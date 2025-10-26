<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\hongbao\model;

use app\dict\member_account\AccountDict;
use app\model\BaseModel;
use app\model\member\MemberAccount as MemberAccountModel;
use app\model\system\Config as ConfigModel;
use app\model\system\Cron;
use app\model\system\User;
use extend\Poster as PosterExtend;
use app\model\upload\Upload;

/**
 * 裂变红包活动表
 * Class hongbao
 * @package addon\hongbao\model
 */
class Hongbao extends BaseModel
{
    private $status = [
        0 => '未开始',
        1 => '进行中',
        2 => '已结束',
        -1 => '已关闭'
    ];

    /**
     * 获取预售活动状态
     * @return array
     */
    public function getHongbaoStatus()
    {
        return $this->status;
    }

    /**
     * 获取分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getHongbaoPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'create_time desc', $field = '*')
    {
        $list = model('promotion_hongbao')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 获取优惠券活动信息
     * @param array $condition
     * @param string $field
     * @param string $alias
     * @param unknown $join
     * @param unknown $data
     * @return array
     */
    public function getHongbaoInfo($condition = [], $field = true, $alias = 'a', $join = null, $data = null)
    {
        $res = model('promotion_hongbao')->getInfo($condition, $field, $alias, $join, $data);
        return $this->success($res);
    }

    /**
     * 新增瓜分红包活动
     * @param $data
     * @return array
     */
    public function addHongbao($data)
    {
        if ($data[ 'start_time' ] > time()) {
            $data[ 'status' ] = 0;
            $data[ 'status_name' ] = $this->status[ 0 ];
        } else {
            $data[ 'status' ] = 1;
            $data[ 'status_name' ] = $this->status[ 1 ];
        }
        model('promotion_hongbao')->startTrans();
        try {
            $res = model("promotion_hongbao")->add($data);

            $cron = new Cron();
            //增加定时更改活动状态自动事件
            if (!empty($data[ 'start_time' ])) {
                $cron->addCron(1, 0, "裂变红包变更活动状态", "CronChangeHongbaoStatus", $data[ 'start_time' ], $res);
            }
            if (!empty($data[ 'end_time' ])) {
                $cron->addCron(1, 0, "裂变红包变更活动状态", "CronChangeHongbaoStatus", $data[ 'end_time' ], $res);
            }

            model('promotion_hongbao')->commit();
            return $this->success($res);
        } catch (\Exception $e) {
            model('promotion_hongbao')->rollback();
            return $this->error($e->getMessage());
        }
    }

    public function editHongbao($data)
    {
        $data[ 'update_time' ] = time();

        if ($data[ 'start_time' ] > time()) {
            $data[ 'status' ] = 0;
            $data[ 'status_name' ] = $this->status[ 0 ];
        } else {
            $data[ 'status' ] = 1;
            $data[ 'status_name' ] = $this->status[ 1 ];
        }

        $hongbao_id = $data[ 'hongbao_id' ];
        unset($data[ 'hongbao_id' ]);

        #(修改发放量与库存)
        $old_info = model("promotion_hongbao")->getInfo([ [ 'hongbao_id', '=', $hongbao_id ] ], 'inventory,count,image');
        $data[ 'count' ] = $old_info[ 'count' ] + $data[ 'inventory' ] - $old_info[ 'inventory' ];

        if (!empty($old_info[ 'image' ]) && !empty($data[ 'image' ]) && $old_info[ 'image' ] != $data[ 'image' ]) {
            $upload_model = new Upload();
            $upload_model->deletePic($old_info[ 'image' ], $data[ 'site_id' ]);
        }

        $res = model("promotion_hongbao")->update($data, [ [ 'hongbao_id', '=', $hongbao_id ] ]);
        $cron = new Cron();
        $cron->deleteCron([ [ 'event', '=', 'CronChangeHongbaoStatus' ], [ 'relate_id', '=', $hongbao_id ] ]);
        if ($data[ 'status' ] == 0) {
            $cron->addCron(1, 0, "裂变红包变更活动状态", "CronChangeHongbaoStatus", $data[ 'start_time' ], $hongbao_id);
            $cron->addCron(1, 0, "裂变红包变更活动状态", "CronChangeHongbaoStatus", $data[ 'end_time' ], $hongbao_id);
        } else if ($data[ 'status' ] == 1) {
            $cron->addCron(1, 0, "裂变红包变更活动状态", "CronChangeHongbaoStatus", $data[ 'end_time' ], $hongbao_id);
        }
        return $this->success($res);
    }

    /**
     * 更改活动状态
     * @param $hongbao_id
     * @return array
     */
    public function changeHongbaoStatus($hongbao_id)
    {
        $info = model('promotion_hongbao')->getInfo([ [ 'hongbao_id', '=', $hongbao_id ] ]);
        if (empty($info)) $this->success();

        if ($info[ 'end_time' ] <= time()) {
            $status = 2;
            $status_name = $this->status[ 2 ];
            model('promotion_hongbao_group')->update([ 'is_look' => 1 ], [ [ 'hongbao_id', '=', $hongbao_id ], [ 'status', '=', 2 ] ]);
        } else if ($info[ 'start_time' ] <= time() && $info[ 'end_time' ] > time()) {
            $status = 1;
            $status_name = $this->status[ 1 ];
        } else {
            $status = 0;
            $status_name = $this->status[ 0 ];
        }
        $res = model('promotion_hongbao')->update([ 'status' => $status, 'status_name' => $status_name ], [ [ 'hongbao_id', '=', $hongbao_id ] ]);
        return $this->success($res);
    }

    /**
     * 关闭活动
     * @param $data
     * @return array
     */
    public function closeHongbao($data)
    {
        $hongbao_id = $data[ 'hongbao_id' ];
        $site_id = $data[ 'site_id' ];
        $condition = [
            [ 'hongbao_id', '=', $hongbao_id ],
            [ 'site_id', '=', $site_id ],
        ];
        model('promotion_hongbao')->startTrans();
        try {
            $res = model('promotion_hongbao')->update([ 'status' => -1, 'status_name' => '已关闭' ], $condition);
            model('promotion_hongbao_group')->update([ 'status' => 2 ], [ [ 'hongbao_id', '=', $hongbao_id ], [ 'status', '=', 0 ] ]);
            model('promotion_hongbao_group')->update([ 'is_look' => 1 ], [ [ 'hongbao_id', '=', $hongbao_id ], [ 'status', '=', 2 ] ]);
            $cron = new Cron();
            $cron->deleteCron([ [ 'event', '=', 'CronChangeHongbaoStatus' ], [ 'relate_id', '=', $hongbao_id ] ]);
            model('promotion_hongbao')->commit();
            return $this->success($res);
        } catch (\Exception $e) {
            model('promotion_hongbao')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 删除活动
     * @param $data
     * @return array
     */
    public function deleteHongbao($data)
    {
        $hongbao_id = $data[ 'hongbao_id' ];
        $site_id = $data[ 'site_id' ];

        model('promotion_hongbao')->startTrans();
        try {
            $condition = [
                [ 'hongbao_id', '=', $hongbao_id ],
                [ 'site_id', '=', $site_id ],
            ];

            $old_info = model("promotion_hongbao")->getInfo($condition);
            if (!empty($old_info[ 'image' ])) {
                $upload_model = new Upload();
                $upload_model->deletePic($old_info[ 'image' ], $site_id);
            }

            $res = model("promotion_hongbao")->delete($condition);
            #删除活动所建分组
            model('promotion_hongbao_group')->delete([ [ 'hongbao_id', '=', $hongbao_id ] ]);

            $cron = new Cron();
            $cron->deleteCron([ [ 'event', '=', 'CronChangeHongbaoStatus' ], [ 'relate_id', '=', $hongbao_id ] ]);
            model('promotion_hongbao')->commit();
            return $this->success($res);
        } catch (\Exception $e) {
            model('promotion_hongbao')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 发起瓜分
     */
    public function launch($hongbao_id, $member_id, $site_id)
    {
        $hongbao_info = model('promotion_hongbao')->getInfo(
            [
                [ 'hongbao_id', '=', $hongbao_id ],
                [ 'site_id', '=', $site_id ],
                [ 'status', '=', 1 ],
            ]
        );
        if (empty($hongbao_info)) return $this->error('', '未查到瓜分活动信息');
        if ($hongbao_info[ 'inventory' ] <= 0) return $this->error('', '红包已被抢完了，下次再来吧！');
        $launch_info = model('promotion_hongbao_group')->getInfo([ [ 'hongbao_id', '=', $hongbao_info[ 'hongbao_id' ] ], [ 'header_id', '=', $member_id ] ], 'group_id,status');
        if (!empty($launch_info) && $launch_info[ 'status' ] == 0) return $this->error('', '正在瓜分中');
        if (!empty($launch_info) && $launch_info[ 'status' ] == 1) return $this->error('', '已参与过此活动，无法重复发起瓜分');
        $member_info = model('member')->getInfo([ [ 'site_id', '=', $site_id ], [ 'member_id', '=', $member_id ] ], 'nickname,headimg');
        if (empty($member_info)) return $this->error('', '未获取到会员信息');
        model('promotion_hongbao_group')->startTrans();
        $end_time = time() + ( $hongbao_info[ 'divide_time' ] * 3600 );
        try {
            $data = [
                'hongbao_id' => $hongbao_info[ 'hongbao_id' ],
                'start_time' => time(),
                'header_id' => $member_id,
                'status' => 0,
                'num' => $hongbao_info[ 'divide_num' ],
                'site_id' => $site_id,
                'group_member_ids' => $member_id,
            ];
            if ($hongbao_info[ 'end_time' ] < $end_time) {
                $data[ 'end_time' ] = $hongbao_info[ 'end_time' ];
            } else {
                $data[ 'end_time' ] = $end_time;
            }
            $launch_id = model('promotion_hongbao_group')->add($data);
            model('promotion_hongbao_group')->update([ 'is_look' => 1 ], [ [ 'hongbao_id', '=', $hongbao_info[ 'hongbao_id' ] ], [ 'header_id', '=', $member_id ], [ 'status', '=', 2 ] ]);
            model('promotion_hongbao')->setDec([ [ 'hongbao_id', '=', $hongbao_id ], [ 'site_id', '=', $site_id ] ], 'inventory');
            $cron = new Cron();

            #是否模拟好友 1 是 2 否
            if ($hongbao_info[ 'is_simulation' ] == 1) {
                #加个 瓜分时间到期自动补齐
                $cron->addCron(1, 0, '未成团自动模拟好友瓜分', 'HongbaoSimulation', $data[ 'end_time' ], $launch_id);
            } else {
                $cron->addCron(1, 0, '瓜分发起自动关闭', 'HongbaoLaunchClose', $data[ 'end_time' ], $launch_id);
            }

            model('promotion_hongbao_group')->commit();
            return $this->success($launch_id);
        } catch (\Exception $e) {
            model('promotion_hongbao_group')->rollback();
            return $this->error($e->getMessage());
        }

    }

    /**
     * 帮瓜分
     * @param $launch_id
     * @param $member_id
     * @param $site_id
     */
    public function divide($launch_id, $member_id, $site_id)
    {
        $hongbao_group = model('promotion_hongbao_group')->getInfo(
            [
                [ 'group_id', '=', $launch_id ],
                [ 'site_id', '=', $site_id ],
            ]
        );
        if (empty($hongbao_group)) return $this->error('', '未查到瓜分红包参与活动组信息');
        if ($hongbao_group[ 'status' ] == 1) return $this->error('', '已经被瓜分完了');
        if ($hongbao_group[ 'status' ] == 2) return $this->error('', '瓜分过期请重新发起瓜分');
        $member_info = model('member')->getInfo([ [ 'site_id', '=', $site_id ], [ 'member_id', '=', $member_id ] ], 'nickname,headimg');
        if (empty($member_info)) return $this->error('', '未获取到会员信息');
        model('promotion_hongbao_group')->startTrans();
        try {
            $hongbao_info = model('promotion_hongbao')->getInfo(
                [
                    [ 'hongbao_id', '=', $hongbao_group[ 'hongbao_id' ] ],
                    [ 'site_id', '=', $site_id ],
                    [ 'status', '=', 1 ],
                ]
            );
            if (empty($hongbao_info)) return $this->error('', '未查到瓜分活动信息');
            #判断此用户是否是新人
            $hongbao_member_group = model('promotion_hongbao_group')->getList([ [ 'hongbao_id', '=', $hongbao_info[ 'hongbao_id' ] ] ], 'member_ids');
            $is_new = 0;
            if (!empty($hongbao_member_group)) {
                foreach ($hongbao_member_group as $k => $v) {
                    if (in_array($member_id, explode(",", $v[ 'member_ids' ]))) {
                        $is_new = $is_new + 1;
                    }
                }
            }
            #活动限制仅新人可瓜
            if ($hongbao_info[ 'is_new' ] == 1) {
                if ($is_new > 0) return $this->error('', '您已参加过此活动了，此活动只可参与一次');
            }

            $member_arr = [];#帮瓜分用户
            $group_member_arr = [];#瓜分组用户
            if (!empty($hongbao_group[ 'group_member_ids' ])) {
                $group_member_arr = explode(",", $hongbao_group[ 'group_member_ids' ]);
            }
            if (!empty($hongbao_group[ 'member_ids' ])) {
                $member_arr = explode(",", $hongbao_group[ 'member_ids' ]);
            }
            if (in_array($member_id, $member_arr)) {
                return $this->error('', '已经帮助瓜分过啦');
            }
            #插入瓜分的用户组
            $member_arr[] = $member_id;
            $group_member_arr[] = $member_id;
            #达到人数(瓜分成功)
            if (count($group_member_arr) == $hongbao_group[ 'num' ]) {
                model('promotion_hongbao')->setInc([ [ 'hongbao_id', '=', $hongbao_group[ 'hongbao_id' ] ], [ 'site_id', '=', $site_id ] ], 'success_count');
                model('promotion_hongbao_group')->update([ 'status' => 1, 'is_look' => 1, 'member_ids' => implode(',', $member_arr), 'group_member_ids' => implode(',', $group_member_arr) ], [ [ 'group_id', '=', $launch_id ], [ 'site_id', '=', $site_id ] ]);
                $user_model = new User();
                $user_admin_info = $user_model->getUserInfo([ [ 'app_module', '=', 'shop' ], [ 'is_admin', '=', 1 ], [ 'site_id', '=', $site_id ] ])[ 'data' ];
                $balance_data = [
                    'site_id' => $site_id,
                    'uid' => $user_admin_info[ 'uid' ],
                    'username' => $user_admin_info[ 'username' ],
                    'balance_set' => $hongbao_info[ 'balance_set' ]
                ];
                #固定的瓜分金额
                if ($hongbao_info[ 'divide_type' ] == 0) {
                    $discount_coupon_money = round($hongbao_info[ 'money' ] / $hongbao_info[ 'divide_num' ], 2);
                    $balance_data[ 'money' ] = $discount_coupon_money;
                    foreach ($group_member_arr as $k => $v) {
                        $balance_data[ 'member_id' ] = $v;
                        #发放储值余额
                        $this->addBalance($balance_data, $launch_id);
                    }
                } else {
                    #新人组
                    $couple_group = [];
                    #旧人组
                    $old_group = [];
                    #判断参与活动的用户是否是新人
                    if (!empty($hongbao_member_group)) {
                        foreach ($group_member_arr as $key => $value) {
                            $fresh_num = 0;
                            foreach ($hongbao_member_group as $k => $v) {
                                if (in_array($value, explode(",", $v[ 'member_ids' ]))) {
                                    $fresh_num = $fresh_num + 1;
                                }
                            }
                            if ($fresh_num == 0) $couple_group[] = $value;
                        }
                        $old_group = array_diff($group_member_arr, $couple_group);
                    } else {
                        $couple_group = $group_member_arr;
                    }
                    #随机获取比例(整数)
                    $proportion = $this->rand_bouns($hongbao_info[ 'divide_num' ], $hongbao_info[ 'money' ]);
                    #比例从大到小排序
                    arsort($proportion);
                    $proportion = array_values($proportion);
                    #有新人
                    if (!empty($couple_group)) {
                        #打乱新人组排序
                        shuffle($couple_group);
                        #新人组  重置下标
                        $couple_group = array_values($couple_group);

                        foreach ($couple_group as $k => $v) {
                            $balance_data[ 'money' ] = round($proportion[ $k ], 2);
                            $balance_data[ 'member_id' ] = $v;
                            $this->addBalance($balance_data, $launch_id);
                            unset($proportion[ $k ]);
                        }
                        if (!empty($old_group)) {
                            shuffle($old_group);
                            $old_group = array_values($old_group);
                            $proportion = array_values($proportion);
                            foreach ($old_group as $k => $v) {
                                $balance_data[ 'money' ] = round($proportion[ $k ], 2);
                                $balance_data[ 'member_id' ] = $v;
                                $this->addBalance($balance_data, $launch_id);
                            }
                        }
                    } else {
                        #打乱旧人组排序
                        shuffle($old_group);
                        $old_group = array_values($old_group);
                        foreach ($old_group as $k => $v) {
                            $balance_data[ 'money' ] = round($proportion[ $k ], 2);
                            $balance_data[ 'member_id' ] = $v;
                            $this->addBalance($balance_data, $launch_id);
                        }
                    }
                    #修改 人组顺序
                    $new_group_member_ids = array_merge($couple_group, $old_group);
                    model('promotion_hongbao_group')->update([ 'group_member_ids' => implode(',', $new_group_member_ids) ], [ [ 'group_id', '=', $launch_id ], [ 'site_id', '=', $site_id ] ]);
                }
            } else {
                #增加参与人
                model('promotion_hongbao_group')->update([ 'member_ids' => implode(',', $member_arr), 'group_member_ids' => implode(',', $group_member_arr) ], [ [ 'group_id', '=', $launch_id ], [ 'site_id', '=', $site_id ] ]);
            }
            model('promotion_hongbao_group')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('promotion_hongbao_group')->rollback();
            return $this->error($e->getMessage());
        }
    }

    #发放红包（余额）
    public function addBalance($data, $launch_id)
    {
        model('promotion_hongbao_group')->startTrans();
        try {
            $member_account_model = new MemberAccountModel();
            $user_model = new User();
            if ($data[ 'balance_set' ] == 1) {
                #奖励储值余额
                $member_account_model->addMemberAccount($data[ 'site_id' ], $data[ 'member_id' ], AccountDict::balance, $data[ 'money' ], 'hongbao', 0, '活动奖励发放', $launch_id);
            } else {
                #奖励现金余额
                $member_account_model->addMemberAccount($data[ 'site_id' ], $data[ 'member_id' ], 'balance_money', $data[ 'money' ], 'hongbao', 0, '活动奖励发放', $launch_id);
            }
            $user_model->addUserLog($data[ 'uid' ], $data[ 'username' ], $data[ 'site_id' ], "裂变红包：会员余额调整id:" . $data[ 'member_id' ] . "金额" . $data[ 'money' ]);

            $hongbao_group = model('promotion_hongbao_group')->getInfo(
                [
                    [ 'group_id', '=', $launch_id ],
                ]
            );
            if (empty($hongbao_group)) return $this->error('', '未查到瓜分红包参与活动组信息');
            $balance_data = [];
            if (!empty($hongbao_group[ 'balance_data' ])) {
                $balance_data = explode(",", $hongbao_group[ 'balance_data' ]);
            }
            $balance_data[] = $data['money'];

            $res = model('promotion_hongbao_group')->update([ 'balance_data' => implode(',', $balance_data) ], [ [ 'group_id', '=', $launch_id ] ]);

            $cron = new Cron();
            #删除瓜分组定时结束
            $cron->deleteCron([ [ 'event', '=', 'HongbaoLaunchClose' ], [ 'relate_id', '=', $launch_id ] ]);
            model('promotion_hongbao_group')->commit();
            return $res;
        } catch (\Exception $e) {
            model('promotion_hongbao_group')->rollback();
            return $this->error($e->getMessage());
        }

    }

    /**
     * @param $person  人数
     * @param $percent  金额
     * @return array
     */
    public static function rand_bouns($person, $percent)
    { //百分比
        $now_person = $person;
        $bouns = array ();
        for ($i = 0; $i <= $person - 1; $i++) {
            $bouns[ $i ] = self::get_bouns($now_person, $percent);
            $percent = $percent - $bouns[ $i ];
            $now_person = $now_person - 1;
        }
        return $bouns;
    }

    public static function get_bouns($person, $percent)
    {
        if ($person == 1) return $percent;
        $max = 30;
        if ($percent < $max) $max = $percent;
        $min = $percent - $max * ( $person - 1 ) <= 0 ? 1 : $percent - $max * ( $person - 1 );
        $max = $max - ( $person ) <= 0 ? 1 : $max - ( $person );
        return rand($min, $max);
    }

    /**
     * 海报
     */
    public function poster($arr, $app_type, $site_id, $member_id)
    {
        try {
            $qrcode_info = $this->getQrcode($arr, $app_type, $site_id);
            if ($qrcode_info[ 'code' ] < 0) return $qrcode_info;

            $member_info = $this->getMemberInfo($member_id);
            if (empty($member_info)) return $this->error('未获取到会员信息');

            $poster = new PosterExtend(740, 1250);
            $option = [
                [
                    'action' => 'imageCopy', // 背景图
                    'data' => [
                        'public/uniapp/hongbao/poster.png',
                        0,
                        0,
                        740,
                        1250,
                        'square',
                        0,
                        1
                    ]
                ],
                [
                    'action' => 'imageCopy', // 二维码
                    'data' => [
                        $qrcode_info[ 'data' ][ 'path' ],
                        505,
                        980,
                        205,
                        205,
                        'square',
                        0,
                        1
                    ]
                ],
                [
                    'action' => 'imageCircularCopy', // 写入用户头像
                    'data' => [
                        !empty($member_info[ 'headimg' ]) ? $member_info[ 'headimg' ] : 'public/static/img/default_img/head.png',
                        82,
                        852,
                        112,
                        112
                    ]
                ],
                [
                    'action' => 'imageText', // 写入分享人昵称
                    'data' => [
                        $member_info[ 'nickname' ],
                        22,
                        [ 255, 129, 61 ],
                        40,
                        1030,
                        440,
                        1,
                        true,
                        1
                    ]
                ]

            ];

            $option_res = $poster->create($option);
            if (is_array($option_res)) return $option_res;

            $res = $option_res->jpeg('upload/poster/hongbao', 'hongbao_id_' . $arr[ 'hid' ] . 'group_id_' . $arr[ 'gid' ] . '_' . $app_type);
            return $res;
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取用户信息
     * @param unknown $member_id
     */
    private function getMemberInfo($member_id)
    {
        $info = model('member')->getInfo([ 'member_id' => $member_id ], 'nickname,headimg');
        return $info;
    }

    /**
     * 生成优惠券二维码
     * @param $hongbao_id
     * @param string $app_type all为全部
     * @param string $type 类型 create创建 get获取
     * @return mixed|array
     */
    public function getQrcode($arr, $app_type, $site_id, $type = 'create')
    {
        $res = event('Qrcode', [
            'site_id' => $site_id,
            'app_type' => $app_type,
            'type' => $type,
            'data' => $arr,
            'page' => '/pages_tool/hongbao/index',
            'qrcode_path' => 'upload/qrcode/hongbao',
            'qrcode_name' => 'hongbao_id_' . $arr[ 'hid' ] . 'inviter_id_' . $arr[ 'id' ] . 'group_id_' . $arr[ 'gid' ] . '_' . $site_id,
        ], true);
        return $res;
    }

    /**
     * 到时模拟瓜分
     */
    public function cronHongbaoSimulation($launch_id)
    {
        $launch_info = model('promotion_hongbao_group')->getInfo([ [ 'group_id', '=', $launch_id ] ]);
        $hongbao_info = model('promotion_hongbao')->getInfo([ [ 'hongbao_id', '=', $launch_info[ 'hongbao_id' ] ] ]);

        model('promotion_hongbao')->setInc([ [ 'hongbao_id', '=', $launch_info[ 'hongbao_id' ] ] ], 'success_count');
        model('promotion_hongbao_group')->update([ 'status' => 1 ], [ [ 'group_id', '=', $launch_id ] ]);

        $group_member_arr = [];#瓜分组用户
        if (!empty($launch_info[ 'group_member_ids' ])) {
            $group_member_arr = explode(",", $launch_info[ 'group_member_ids' ]);
        }
        $user_model = new User();
        $user_admin_info = $user_model->getUserInfo([ [ 'app_module', '=', 'shop' ], [ 'is_admin', '=', 1 ], [ 'site_id', '=', $launch_info[ 'site_id' ] ] ])[ 'data' ];
        $balance_data = [
            'site_id' => $launch_info[ 'site_id' ],
            'uid' => $user_admin_info[ 'uid' ],
            'username' => $user_admin_info[ 'username' ],
            'balance_set' => $hongbao_info[ 'balance_set' ]
        ];
        #固定的瓜分金额
        if ($hongbao_info[ 'divide_type' ] == 0) {
            $discount_coupon_money = round($hongbao_info[ 'money' ] / $hongbao_info[ 'divide_num' ], 2);
            $balance_data[ 'money' ] = $discount_coupon_money;
            foreach ($group_member_arr as $k => $v) {
                $balance_data[ 'member_id' ] = $v;
                $this->addBalance($balance_data, $launch_id);
            }
        } else {
            #新人组
            $couple_group = [];
            #旧人组
            $old_group = [];
            #判断参与活动的用户是否是新人
            if (!empty($hongbao_member_group)) {
                foreach ($group_member_arr as $key => $value) {
                    $fresh_num = 0;
                    foreach ($hongbao_member_group as $k => $v) {
                        if (in_array($value, explode(",", $v[ 'member_ids' ]))) {
                            $fresh_num = $fresh_num + 1;
                        }
                    }
                    if ($fresh_num == 0) $couple_group[] = $value;
                }
                $old_group = array_diff($group_member_arr, $couple_group);
            } else {
                $couple_group = $group_member_arr;
            }
            #随机获取比例(整数)
            $proportion = $this->rand_bouns($hongbao_info[ 'divide_num' ], $hongbao_info[ 'money' ]);
            #比例从大到小排序
            arsort($proportion);

            $proportion = array_values($proportion);
            #有新人
            if (!empty($couple_group)) {
                #打乱新人组排序
                shuffle($couple_group);
                #新人组  重置下标
                $couple_group = array_values($couple_group);

                foreach ($couple_group as $k => $v) {
                    $balance_data[ 'money' ] = round($proportion[ $k ], 2);
                    $balance_data[ 'member_id' ] = $v;
                    $this->addBalance($balance_data, $launch_id);
                    unset($proportion[ $k ]);
                }
                if (!empty($old_group)) {
                    shuffle($old_group);
                    $old_group = array_values($old_group);
                    $proportion = array_values($proportion);
                    foreach ($old_group as $k => $v) {
                        $balance_data[ 'money' ] = round($proportion[ $k ], 2);
                        $balance_data[ 'member_id' ] = $v;
                        $this->addBalance($balance_data, $launch_id);
                    }
                }
            } else {
                #打乱旧人组排序
                shuffle($old_group);
                $old_group = array_values($old_group);
                foreach ($old_group as $k => $v) {
                    $balance_data[ 'money' ] = round($proportion[ $k ], 2);
                    $balance_data[ 'member_id' ] = $v;
                    $this->addBalance($balance_data, $launch_id);
                }
            }
            #修改 人组顺序
            $new_group_member_ids = array_merge($couple_group, $old_group);
            model('promotion_hongbao_group')->update([ 'group_member_ids' => implode(',', $new_group_member_ids) ], [ [ 'group_id', '=', $launch_id ], [ 'site_id', '=', $launch_info[ 'site_id' ] ] ]);
        }

    }

    /**
     * @param $hongbao_id
     * @param $name
     * @param $site_id
     * @param string $type
     * @return array
     * shop端推广
     */
    public function spread($hongbao_id, $name, $site_id, $type = "create")
    {
        $data = [
            'site_id' => $site_id,
            'app_type' => "all", // all为全部
            'type' => $type, // 类型 create创建 get获取
            'data' => [
                "hongbao_id" => $hongbao_id
            ],
            'page' => '/pages_tool/hongbao/index',
            'qrcode_path' => 'upload/qrcode/hongbao',
            'qrcode_name' => 'hongbao_id_' . $hongbao_id,
        ];

        event('Qrcode', $data, true);
        $app_type_list = config('app_type');
        $path = [];
        foreach ($app_type_list as $k => $v) {
            switch ( $k ) {
                case 'h5':
                    $wap_domain = getH5Domain();
                    $path[ $k ][ 'status' ] = 1;
                    $path[ $k ][ 'url' ] = $wap_domain . $data[ 'page' ] . '?hongbao_id=' . $hongbao_id;
                    $path[ $k ][ 'img' ] = "upload/qrcode/hongbao/hongbao_id_" . $hongbao_id . "_" . $k . ".png";
                    break;
                case 'weapp' :
                    $config = new ConfigModel();
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WEAPP_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if (empty($res[ 'data' ][ 'value' ][ 'qrcode' ])) {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = '未配置微信小程序';
                        } else {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $res[ 'data' ][ 'value' ][ 'qrcode' ];
                        }

                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信小程序';
                    }
                    break;
                case 'wechat' :
                    $config = new ConfigModel();
                    $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'WECHAT_CONFIG' ] ]);
                    if (!empty($res[ 'data' ])) {
                        if (empty($res[ 'data' ][ 'value' ][ 'qrcode' ])) {
                            $path[ $k ][ 'status' ] = 2;
                            $path[ $k ][ 'message' ] = '未配置微信公众号';
                        } else {
                            $path[ $k ][ 'status' ] = 1;
                            $path[ $k ][ 'img' ] = $res[ 'data' ][ 'value' ][ 'qrcode' ];
                        }
                    } else {
                        $path[ $k ][ 'status' ] = 2;
                        $path[ $k ][ 'message' ] = '未配置微信公众号';
                    }
                    break;
            }

        }

        $return = [
            'path' => $path,
            'name' => $name,
        ];

        return $this->success($return);
    }

    public function hongbaoQrcode($page, $qrcode_param, $promotion_type, $app_type, $site_id)
    {
        $params = [
            'site_id' => $site_id,
            'data' => $qrcode_param,
            'page' => $page,
            'promotion_type' => $promotion_type,
            'app_type' => $app_type,
            'h5_path' => $page . '?hid=' . $qrcode_param[ 'hid' ],
            'qrcode_path' => 'upload/qrcode/hongbao',
            'qrcode_name' => 'hongbao_id_' . $promotion_type . '_' . $qrcode_param[ 'hid' ] . '_' . $site_id,
        ];
        $solitaire = event('PromotionQrcode', $params, true);
        return $this->success($solitaire);
    }
}