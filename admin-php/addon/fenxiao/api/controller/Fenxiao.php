<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\api\controller;

use addon\fenxiao\model\Config;
use addon\fenxiao\model\Fenxiao as FenxiaoModel;
use addon\fenxiao\model\FenxiaoLevel;
use addon\fenxiao\model\FenxiaoOrder as FenxiaoOrderModel;
use addon\fenxiao\model\Poster;
use addon\fenxiao\model\PosterTemplate as PosterTemplateModel;
use app\api\controller\BaseApi;
use app\model\member\Member;
use Carbon\Carbon;
use think\facade\Db;

/**
 * 分销相关信息
 */
class Fenxiao extends BaseApi
{
    /**
     * 获取分销商信息
     */
    public function detail()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $condition = [
            [ 'f.member_id', '=', $this->member_id ]
        ];

        $model = new FenxiaoModel();
        $info = $model->getFenxiaoDetailInfo($condition);
        if (empty($info[ 'data' ])) {
            $res = $model->autoBecomeFenxiao($this->member_id, $this->site_id);
            if (isset($res[ 'code' ]) && $res[ 'code' ] >= 0) {
                $info = $model->getFenxiaoDetailInfo($condition);
            }
        } else {
            $member = new Member();
            //$info[ 'data' ][ 'one_child_num' ] = $member->getMemberCount([ [ 'fenxiao_id', '=', $info[ 'data' ][ 'fenxiao_id' ] ], [ 'is_fenxiao', '=', 0 ] ])[ 'data' ];

            $condition_result = $model->geFenxiaoNextLevel($this->member_id, $this->site_id);
            $info[ 'data' ][ 'condition' ] = $condition_result[ 'data' ];
        }

        if (!empty($info[ 'data' ])) {
            $fenxiao_order_model = new FenxiaoOrderModel();

            // 今日收入
            $compare_today = Carbon::today()->timestamp;
            $compare_tomorrow = Carbon::tomorrow()->timestamp;

            $commission = 0;
            $one_commission = $fenxiao_order_model->getFenxiaoOrderInfo([ [ 'one_fenxiao_id', '=', $info[ 'data' ][ 'fenxiao_id' ] ], [ 'create_time', 'between', [ $compare_today, $compare_tomorrow ] ], [ 'is_settlement', '=', 1 ] ], 'sum(one_commission) as commission');
            $two_commission = $fenxiao_order_model->getFenxiaoOrderInfo([ [ 'two_fenxiao_id', '=', $info[ 'data' ][ 'fenxiao_id' ] ], [ 'create_time', 'between', [ $compare_today, $compare_tomorrow ] ], [ 'is_settlement', '=', 1 ] ], 'sum(two_commission) as commission');
            $three_commission = $fenxiao_order_model->getFenxiaoOrderInfo([ [ 'three_fenxiao_id', '=', $info[ 'data' ][ 'fenxiao_id' ] ], [ 'create_time', 'between', [ $compare_today, $compare_tomorrow ] ], [ 'is_settlement', '=', 1 ] ], 'sum(three_commission) as commission');

            if (!empty($one_commission[ 'data' ][ 'commission' ])) $commission += $one_commission[ 'data' ][ 'commission' ];
            if (!empty($two_commission[ 'data' ][ 'commission' ])) $commission += $two_commission[ 'data' ][ 'commission' ];
            if (!empty($three_commission[ 'data' ][ 'commission' ])) $commission += $three_commission[ 'data' ][ 'commission' ];

            $info[ 'data' ][ 'today_commission' ] = $commission;

            // 总销售额
            $fenxiao_order_info = $fenxiao_order_model->getFenxiaoOrderInfoNew([ [ 'fo.one_fenxiao_id|fo.two_fenxiao_id|fo.three_fenxiao_id', '=', $info[ 'data' ][ 'fenxiao_id' ] ], ['', 'exp', Db::raw('fo.is_refund=0 or (o.order_status=10 and fo.is_refund=1) ') ] ], 'sum(fo.real_goods_money) as real_goods_money');

            $fenxiao_order_info = $fenxiao_order_info[ 'data' ];
            if (empty($fenxiao_order_info[ 'real_goods_money' ])) {
                $fenxiao_order_info[ 'real_goods_money' ] = 0;
            }
            $info[ 'data' ][ 'today_order_money' ] = $fenxiao_order_info[ 'real_goods_money' ];

            $info[ 'data' ][ 'in_progress_money' ] = 0;
            $one_in_progress_commission = $fenxiao_order_model->getFenxiaoOrderInfo([ [ 'one_fenxiao_id', '=', $info[ 'data' ][ 'fenxiao_id' ] ], [ 'is_settlement', '=', 0 ], [ 'is_refund', '=', 0 ] ], 'sum(one_commission) as commission');
            $two_in_progress_commission = $fenxiao_order_model->getFenxiaoOrderInfo([ [ 'two_fenxiao_id', '=', $info[ 'data' ][ 'fenxiao_id' ] ], [ 'is_settlement', '=', 0 ], [ 'is_refund', '=', 0 ] ], 'sum(two_commission) as commission');
            $three_in_progress_commission = $fenxiao_order_model->getFenxiaoOrderInfo([ [ 'three_fenxiao_id', '=', $info[ 'data' ][ 'fenxiao_id' ] ], [ 'is_settlement', '=', 0 ], [ 'is_refund', '=', 0 ] ], 'sum(three_commission) as commission');

            if (!empty($one_in_progress_commission[ 'data' ][ 'commission' ])) $info[ 'data' ][ 'in_progress_money' ] += $one_in_progress_commission[ 'data' ][ 'commission' ];
            if (!empty($two_in_progress_commission[ 'data' ][ 'commission' ])) $info[ 'data' ][ 'in_progress_money' ] += $two_in_progress_commission[ 'data' ][ 'commission' ];
            if (!empty($three_in_progress_commission[ 'data' ][ 'commission' ])) $info[ 'data' ][ 'in_progress_money' ] += $three_in_progress_commission[ 'data' ][ 'commission' ];
        }
        return $this->response($info);
    }

    /**
     * 获取推荐人分销商信息
     */
    public function sourceInfo()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $member = new Member();
        $member_info = $member->getMemberInfo([ [ 'member_id', '=', $this->member_id ] ], 'fenxiao_id');
        $fenxiao_id = $member_info[ 'data' ][ 'fenxiao_id' ] ?? 0;

        if (empty($fenxiao_id)) {
            return $this->response($this->error('', 'REQUEST_SOURCE_MEMBER'));
        }
        $condition = [
            [ 'fenxiao_id', '=', $fenxiao_id ]
        ];

        $model = new FenxiaoModel();
        $info = $model->getFenxiaoInfo($condition, 'fenxiao_name');

        return $this->response($info);
    }

    /**
     * 获取模板id
     * @return false|string
     */
    public function posterTemplateIds()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'template_type', '=', 'fenxiao' ],
            [ 'template_status', '=', 1 ],
        ];
        $condition[] = [ 'template_type', '=', 'fenxiao' ];
        $poster_template_model = new PosterTemplateModel();
        $list = $poster_template_model->getPosterTemplateList($condition, 'template_id', 'template_id asc')[ 'data' ];
        $id_arr = array_column($list, 'template_id');
        if (empty($id_arr)) $id_arr = [ 'default' ];

        return $this->response($this->success($id_arr));
    }

    /**
     * 分销海报
     * @return \app\api\controller\false|string
     */
    public function poster()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $qrcode_param = $this->params['qrcode_param'] ?? '';//二维码

        if (empty($qrcode_param)) {
            return $this->response($this->error('', 'REQUEST_QRCODE_PARAM'));
        }

        $qrcode_param = json_decode($qrcode_param, true);
        $qrcode_param[ 'source_member' ] = $this->member_id;

        $poster = new Poster();
        $param = $this->params;
        $param[ 'qrcode_param' ] = $qrcode_param;
        $res = $poster->getFenxiaoPoster($param);

        return $this->response($res);
    }

    /**
     * 分销海报
     * @return \app\api\controller\false|string
     */
    public function posterList()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'template_type', '=', 'fenxiao' ],
            [ 'template_status', '=', 1 ],
        ];
        $condition[] = [ 'template_type', '=', 'fenxiao' ];
        $poster_template_model = new PosterTemplateModel();
        $list = $poster_template_model->getPosterTemplateList($condition, 'template_id', 'template_id asc')[ 'data' ];
        $id_arr = array_column($list, 'template_id');
        $id_arr = $id_arr ?: ['default'];
        $qrcode_param = $this->params['qrcode_param'] ?? '';//二维码
        $qrcode_param = [json_decode($qrcode_param, true)];
        $qrcode_param[ 'source_member' ] = $this->member_id;
        $poster = new Poster();
        $param = $this->params;
        $param[ 'qrcode_param' ] = $qrcode_param;
        $path = [];
        foreach ($id_arr as $k => $v){
            $param['template_id'] = $v;
            $res = $poster->getFenxiaoPoster($param)['data']['path'] ?? '';
            if($res) $path[] = $res;
        }
        return $this->response($this->success($path));
    }

    /**
     * 分销商等级信息
     */
    public function level()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $level = $this->params[ 'level' ] ?? 0;

        $condition = [
            [ 'level_id', '=', $level ]
        ];
        $model = new FenxiaoLevel();
        $info = $model->getLevelInfo($condition);

        return $this->response($info);
    }

    /**
     * 分销商我的团队
     */
    public function team()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $level = $this->params[ 'level' ] ?? 1;
        $is_pay = $this->params[ 'is_pay' ] ?? 0;

        $model = new FenxiaoModel();
        $fenxiao_info = $model->getFenxiaoInfo([ [ 'member_id', '=', $this->member_id ] ], 'fenxiao_id');
        if (empty($fenxiao_info[ 'data' ])) return $this->response($this->error('', 'MEMBER_NOT_IS_FENXIAO'));

        $list = $model->getFenxiaoTeam($level, $fenxiao_info[ 'data' ][ 'fenxiao_id' ], $page, $page_size, $is_pay);

        return $this->response($list);
    }

    /**
     * 查询我的团队的数量
     */
    public function teamNum()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $model = new FenxiaoModel();
        $fenxiao_info = $model->getFenxiaoInfo([ [ 'member_id', '=', $this->member_id ] ], 'fenxiao_id');
        if (empty($fenxiao_info[ 'data' ])) return $this->response($this->error('', 'MEMBER_NOT_IS_FENXIAO'));

        $data = $model->getFenxiaoTeamNum($fenxiao_info[ 'data' ][ 'fenxiao_id' ], $this->site_id);
        return $this->response($data);
    }

    /**
     * 获取下级分销商订单
     * @return false|string
     */
    public function getOrder()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $model = new FenxiaoModel();
        $fenxiao_info = $model->getFenxiaoInfo([ [ 'member_id', '=', $this->member_id ] ], 'fenxiao_id');
        if (empty($fenxiao_info[ 'data' ])) return $this->response($this->error('', 'MEMBER_NOT_IS_FENXIAO'));
        $fenxiao_info = $fenxiao_info[ 'data' ];

        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $fenxiao_id = $this->params[ 'fenxiao_id' ] ?? 0;
        $sub_member_id = $this->params[ 'sub_member_id' ] ?? 0;
        $condition = [];

        if (!empty($fenxiao_id)) {
            $sub_fenxiao_info = $model->getFenxiaoInfo([ [ 'fenxiao_id', '=', $fenxiao_id ] ], 'fenxiao_id,member_id')[ 'data' ];
            if (empty($sub_fenxiao_info)) return $this->response($this->error('', 'MEMBER_NOT_IS_FENXIAO'));

            $condition = [
                [ '', 'exp', Db::raw("( (fo.one_fenxiao_id = {$fenxiao_info['fenxiao_id']} AND fo.two_fenxiao_id = {$fenxiao_id}) OR (fo.two_fenxiao_id = {$fenxiao_info['fenxiao_id']} AND fo.three_fenxiao_id = {$fenxiao_id})) OR fo.member_id = {$sub_fenxiao_info['member_id']}") ]
            ];
        } elseif (!empty($sub_member_id)) {
            $is_sub_member = model('member')->getCount([ [ 'member_id', '=', $sub_member_id ], [ 'fenxiao_id', '=', $fenxiao_info[ 'fenxiao_id' ] ] ]);
            if (!$is_sub_member) return $this->response($this->error('', 'NOT_EXIST_FENXIAO_RELATION'));

            $condition = [
                [ 'fo.one_fenxiao_id', '=', $fenxiao_info[ 'fenxiao_id' ] ],
                [ 'fo.member_id', '=', $sub_member_id ]
            ];
        }

        $order_model = new FenxiaoOrderModel();
        $list = $order_model->getFenxiaoOrderPageList($condition, $page, $page_size, 'fo.fenxiao_order_id desc');
        if (!empty($list[ 'data' ][ 'list' ])) {
            foreach ($list[ 'data' ][ 'list' ] as $k => $item) {
                if ($item[ 'one_fenxiao_id' ] == $fenxiao_info[ 'fenxiao_id' ]) {
                    $list[ 'data' ][ 'list' ][ $k ][ 'commission' ] = $item[ 'one_commission' ];
                    $list[ 'data' ][ 'list' ][ $k ][ 'commission_level' ] = 1;
                } elseif ($item[ 'two_fenxiao_id' ] == $fenxiao_info[ 'fenxiao_id' ]) {
                    $list[ 'data' ][ 'list' ][ $k ][ 'commission' ] = $item[ 'two_commission' ];
                    $list[ 'data' ][ 'list' ][ $k ][ 'commission_level' ] = 2;
                } elseif ($item[ 'three_fenxiao_id' ] == $fenxiao_info[ 'fenxiao_id' ]) {
                    $list[ 'data' ][ 'list' ][ $k ][ 'commission' ] = $item[ 'three_commission' ];
                    $list[ 'data' ][ 'list' ][ $k ][ 'commission_level' ] = 3;
                }
                $list[ 'data' ][ 'list' ][ $k ] = array_diff_key($list[ 'data' ][ 'list' ][ $k ], [ 'one_fenxiao_id' => '', 'one_rate' => '', 'one_commission' => '', 'one_fenxiao_name' => '', 'two_fenxiao_id' => '', 'two_rate' => '', 'two_commission' => '', 'two_fenxiao_name' => '', 'three_fenxiao_id' => '', 'three_rate' => '', 'three_commission' => '', 'three_fenxiao_name' => '' ]);
            }
        }
        return $this->response($list);
    }

    /**
     * 排行榜
     */
    public function rankingList()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $type = $this->params[ 'type' ] ?? 'profit'; // 排行榜 profit：按受益  invited_num：按邀请人数
        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;

        $model = new FenxiaoModel();

        $condition = [
            [ 'f.site_id', '=', $this->site_id ],
            [ 'f.is_delete', '=', 0 ]
        ];

        $order = $type == 'profit' ? 'f.total_commission desc' : Db::raw('(f.one_child_num) desc');
        $field = 'f.total_commission, (f.one_child_num) as child_num, m.nickname,m.headimg';

        $data = $model->getFenxiaoPageLists($condition, $page, $page_size, $order, $field, 'f', [ [ 'member m', 'm.member_id = f.member_id', 'inner' ] ]);

        return $this->response($data);
    }

    /**
     * 获取排名
     * @return false|string
     */
    public function ranking()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $type = $this->params[ 'type' ] ?? 'invited_num'; // 排行榜 profit：按受益  invited_num：按邀请人数

        $model = new FenxiaoModel();
        $fenxiao_info = $model->getFenxiaoInfo([ [ 'member_id', '=', $this->member_id ] ], 'fenxiao_id')[ 'data' ];
        if (empty($fenxiao_info)) return $this->response($this->error('', 'MEMBER_NOT_IS_FENXIAO'));

        $order = $type == 'profit' ? 'total_commission' : '(one_child_num + one_child_fenxiao_num)';

        $data = $model->getFenxiaoRanking($this->site_id, $fenxiao_info[ 'fenxiao_id' ], $order);
        return $this->response($data);
    }

    /**
     * 子级分销商
     */
    public function childFenxiao()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;

        $model = new FenxiaoModel();
        $fenxiao_info = $model->getFenxiaoInfo([ [ 'member_id', '=', $this->member_id ] ], 'fenxiao_id')[ 'data' ];
        if (empty($fenxiao_info)) return $this->response($this->error('', 'MEMBER_NOT_IS_FENXIAO'));

        $parent_fenxiao_id = [ $fenxiao_info[ 'fenxiao_id' ] ]; // 上级分销商id集合

        // 查询分销基础配置
        $config_model = new Config();
        $fenxiao_basic_config = $config_model->getFenxiaoBasicsConfig($this->site_id)[ 'data' ][ 'value' ];
        $level = $fenxiao_basic_config[ 'level' ];

        if ($level == 2) {

            // 二级分销商id集合
            $one_level_fenxiao = model('fenxiao')->getColumn([ [ 'parent', '=', $fenxiao_info[ 'fenxiao_id' ] ] ], 'fenxiao_id');
            if (!empty($one_level_fenxiao)) {
                $parent_fenxiao_id = array_merge($parent_fenxiao_id, $one_level_fenxiao);
            }
        }

        $condition = [
            [ 'f.site_id', '=', $this->site_id ],
            [ 'f.parent', 'in', $parent_fenxiao_id ],
            [ 'm.is_delete', '=', 0 ]
        ];
        $field = 'm.nickname,m.headimg,m.member_id,m.order_num,m.order_money,f.fenxiao_id,f.audit_time,f.level_name,m.is_fenxiao,m.bind_fenxiao_time,f.one_child_num,f.one_child_fenxiao_num';
        $join = [ [ 'member m', 'm.member_id = f.member_id', 'inner' ] ];

        $res = $model->getFenxiaoPageLists($condition, $page, $page_size, 'f.audit_time desc', $field, 'f', $join);
        return $this->response($res);
    }
}