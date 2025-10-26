<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace addon\cardservice\model;

use app\model\BaseModel;
use app\model\system\Cron;
use app\model\verify\Verify;
use think\facade\Db;
use think\facade\Log;

class MemberCard extends BaseModel
{
    //卡包状态
    const STATUS_NORMAL = 1;
    const STATUS_INVALID = 0;

    /**
     * 创建会员卡项
     * @param $param
     * @return array
     */
    public function create($param)
    {
        $store_id = $param['store_id'] ?? 0;
        $goods_info = model('goods')->getInfo([
            ['goods_id', '=', $param['goods_id']],
            ['goods_state', '=', 1],
            ['is_delete', '=', 0],
        ], 'goods_name,goods_class,sku_id');
        if (empty($goods_info)) {
            return $this->error('', '未获取到商品信息');
        }

        $verify_model = new Verify();
        $end_time = 0;
        $card_info = model('goods_card')->getInfo([['goods_id', '=', $param['goods_id']]], 'site_id, goods_id, card_id, card_type, card_type_name, renew_price, recharge_money, common_num, discount_goods_type, discount, validity_type, validity_day, validity_time');
        if (empty($card_info)) {
            return $this->error('', '未获取到卡项信息');
        }
        $card_item = model('goods_card_item')->getList([['ngci.card_goods_id', '=', $param['goods_id']]], 'ngci.goods_id, ngci.sku_id, ngci.num, ngci.discount, ngci.card_goods_id, ngci.id, ngs.goods_class, ngs.sku_name, ngs.sku_image, ngs.price, ngs.is_virtual', '', 'ngci', [['goods_sku ngs', 'ngci.sku_id = ngs.sku_id', 'inner']]);
        switch ($card_info['validity_type']) {
            case 1:
                $end_time = strtotime('+' . $card_info['validity_day'] . ' day');
                break;
            case 2:
                $end_time = $card_info['validity_time'];
                if ($end_time < time()) {
                    return $this->error('', '卡项已超出有效期');
                }
                break;
        }

        model('member_goods_card')->startTrans();
        try {
            $delivery_method = 'verify';
            $create_num = $param['num'] ?? 1;
            for ($i = 1; $i <= $create_num; $i++) {
                $card_id = model('member_goods_card')->add([
                    'site_id' => $param['site_id'] ?? 0,
                    'card_code' => $this->createNo(),
                    'member_id' => $param['member_id'],
                    'goods_id' => $param['goods_id'],
                    'create_time' => time(),
                    'end_time' => $end_time,
                    'order_id' => $param['order_id'] ?? 0,
                    'card_type' => $card_info['card_type'],
                    'total_num' => $card_info['common_num'],
                    'delivery_method' => $delivery_method,
                    'store_id' => $store_id,
                    'goods_name' => $goods_info['goods_name']
                ]);

                $total_num = 0;
                foreach ($card_item as $item) {
                    if (isset($card_info) && $card_info['card_type'] == 'commoncard') {
                        $item['num'] = $card_info['common_num'];
                        $total_num = $item['num'];
                    } else {
                        $total_num += $item['num'];
                    }
                    $item_data = [
                        'site_id' => $param['site_id'] ?? 0,
                        'card_id' => $card_id,
                        'member_id' => $param['member_id'],
                        'goods_id' => $item['goods_id'],
                        'sku_id' => $item['sku_id'],
                        'num' => $item['num'],
                        'end_time' => $end_time,
                        'card_type' => isset($card_info) ? $card_info['card_type'] : '',
                        'goods_class' => $item['goods_class'],
                        'member_verify_id' => 0,
                        'store_id' => $store_id
                    ];

                    $item_array = [
                        [
                            'img' => $item['sku_image'],
                            'name' => $item['sku_name'],
                            'price' => $item['price'],
                            'num' => $item['num'],
                            'remark_array' => []
                        ]
                    ];
                    $verify_content_json = $verify_model->getVerifyJson($item_array, []);
                    //创建核销码
                    $verify_res = $verify_model->addVerify("cardgoods", $item_data['site_id'], "", $verify_content_json, $end_time, $item_data['num'], 0, $param['member_id']);
                    if ($verify_res['code'] != 0) {
                        model('member_goods_card')->rollback();
                        return $this->error([], '核销码创建失败');
                    }
                    $item_data['member_verify_id'] = $verify_res['data']['verify_id'];
                    model('member_goods_card_item')->add($item_data);
                }
                model('member_goods_card')->update(['total_num' => $total_num, 'delivery_method' => $delivery_method], [['card_id', '=', $card_id]]);

                if ($end_time > 0) (new Cron())->addCron(1, 0, "会员卡项过期失效", "CronMemberCardExpire", $end_time, $card_id);

            }

            model('member_goods_card')->commit();
            return $this->success($card_id);
        } catch (\Exception $e) {
            model('member_goods_card')->rollback();
            return $this->error([], '会员卡项创建失败' . $e->getMessage());
        }
    }

    /**
     * 生成订单编号
     * @param array $site_id
     */
    public function createNo()
    {
        $time_str = date('YmdHi');
        $card_no = $time_str . (string)rand(11111, 99999);
        return $card_no;
    }

    /**
     * 获取会员卡项信息
     * @param array $condition
     * @param string $field
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getCardInfo($condition = [], $field = '*', $alias = '', $join = [])
    {
        $data = model('member_goods_card')->getInfo($condition, $field, $alias, $join);
        return $this->success($data);
    }

    /**
     * 获取会员卡项数量
     * @param array $condition
     * @param string $field
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getCardCount($condition = [], $field = '*', $alias = 'a', $join = null)
    {
        $data = model('member_goods_card')->getCount($condition, $field, $alias, $join);
        return $this->success($data);
    }

    /**
     * 获取会员卡项分页列表
     * @param array $condition
     * @param bool $field
     * @param string $order
     * @param int $page
     * @param int $list_rows
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getCardPageList($condition = [], $field = true, $order = '', $page = 1, $list_rows = PAGE_LIST_ROWS, $alias = 'a', $join = [])
    {
        $data = model('member_goods_card')->pageList($condition, $field, $order, $page, $list_rows, $alias, $join);
        return $this->success($data);
    }

    /**
     * 获取会员卡项项列表
     * @param array $condition
     * @param bool $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getCartItemList($condition = [], $field = true, $order = '', $alias = 'a', $join = [])
    {
        $data = model('member_goods_card_item')->getList($condition, $field, $order, $alias, $join);
        return $this->success($data);
    }

    /**
     * 获取会员卡项项分页列表
     * @param array $condition
     * @param bool $field
     * @param string $order
     * @param int $page
     * @param int $list_rows
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getCartItemPageList($condition = [], $field = true, $order = '', $page = 1, $list_rows = PAGE_LIST_ROWS, $alias = 'a', $join = [])
    {
        $data = model('member_goods_card_item')->pageList($condition, $field, $order, $page, $list_rows, $alias, $join);
        return $this->success($data);
    }

    /**
     * 获取会员卡项项详情
     */
    public function getCartItemInfo($condition = [], $field = '*', $alias = '', $join = [])
    {
        $data = model('member_goods_card_item')->getInfo($condition, $field, $alias, $join);
        return $this->success($data);
    }

    /**
     * 卡项核销
     * @param $param
     */
    public function verify($param)
    {
        $card_item_info = model('member_goods_card_item')->getInfo([['member_verify_id', '=', $param['verify_id']]], 'item_id');
        if (empty($card_item_info)) return $this->error('', '未获取到卡项信息');

        return $this->cardUse([
            'item_id' => $card_item_info['item_id'],
            'num' => 1,
            'type' => 'verify',
            'relation_id' => $param['verify_id'],
            'store_id' => $param['store_id'] ?? 0
        ]);
    }

    /**
     * 卡项使用
     * array 两种参数形式 @param
     * array('item_id' => $card_item_id,'num' => $num,'type' => $type,'relation_id' => $order_goods_id);
     * array(('item_id' => $card_item_id,'num' => $num,'type' => $type,'relation_id' => $order_goods_id));
     */
    public function cardUse($param)
    {
        $temp_item_id = $param['item_id'] ?? 0;
        if ($temp_item_id > 0) {
            $item_list = [$param];
        } else {
            $item_list = $param;
        }
        model('member_goods_card_item')->startTrans();
        try {
            $out_relation_ids = [];
            foreach ($item_list as $item_v) {
                $item_id = $item_v['item_id'];
                $item_num = $item_v['num'] ?? 1;
                $item_type = $item_v['type'];
                $item_relation_id = $item_v['relation_id'];
                $item_store_id = $item_v['store_id'] ?? 0;
                $card_item_info = model('member_goods_card_item')->getInfo([['mci.item_id', '=', $item_id]],
                    'mci.site_id,mci.card_id,mci.num,mci.use_num,mci.item_id,mci.goods_id,mci.sku_id,mci.end_time,mci.member_verify_id,mc.card_type,mc.total_num,mc.total_use_num,mc.delivery_method', 'mci',
                    [
                        ['member_goods_card mc', 'mc.card_id = mci.card_id', 'left']
                    ]);

                if (empty($card_item_info)) {
                    model('member_goods_card_item')->rollback();
                    return $this->error('', '未获取到卡项信息');
                }
                if ($card_item_info['end_time'] > 0 && $card_item_info['end_time'] < time()) {
                    model('member_goods_card_item')->rollback();
                    return $this->error('', '已超出有效期');
                }
                if ($card_item_info['card_type'] != 'timecard' && ($card_item_info['num'] - $card_item_info['use_num'] - $item_num) < 0) {
                    model('member_goods_card_item')->rollback();
                    return $this->error('', '卡项可用次数不足');
                }
                // 如果是通用卡项
                if ($card_item_info['card_type'] == 'commoncard') {
                    model('member_goods_card_item')->setInc([['card_id', '=', $card_item_info['card_id']]], 'use_num', $item_num);
                    // 同步核销码使用次数
                    if ($item_type == 'order') {
                        $verify_ids = model('member_goods_card_item')->getColumn([['card_id', '=', $card_item_info['card_id']]], 'member_verify_id');
                    } else {
                        $verify_ids = model('member_goods_card_item')->getColumn([['card_id', '=', $card_item_info['card_id']], ['item_id', '<>', $card_item_info['item_id']]], 'member_verify_id');
                    }
                    if (!empty($verify_ids)) {
                        model('verify')->setInc([['id', 'in', $verify_ids]], 'verify_use_num', $item_num);
                        model('verify')->update(['is_verify' => Verify::STATUS_IS_VERIFY, 'verify_time' => time()], [
                            ['id', 'in', $verify_ids],
                            ['verify_total_count', '>', 0],
                            ['', 'exp', Db::raw('verify_use_num >= verify_total_count')]
                        ]);
                    }
                } else {
                    model('member_goods_card_item')->setInc([['item_id', '=', $card_item_info['item_id']]], 'use_num', $item_num);
                    // 同步核销码使用次数
                    if ($item_type == 'order') {
                        model('verify')->setInc([['id', '=', $card_item_info['member_verify_id']]], 'verify_use_num', $item_num);
                        model('verify')->update(['is_verify' => Verify::STATUS_IS_VERIFY, 'verify_time' => time()], [
                            ['id', '=', $card_item_info['member_verify_id']],
                            ['verify_total_count', '>', 0],
                            ['', 'exp', Db::raw('verify_use_num >= verify_total_count')]
                        ]);
                    }
                }
                model('member_goods_card')->setInc([['card_id', '=', $card_item_info['card_id']]], 'total_use_num', $item_num);

                // 如果卡项次数已使用完
                if ($card_item_info['card_type'] != 'timecard' && ($card_item_info['total_num'] - $card_item_info['total_use_num'] - $item_num) == 0) {
                    model('member_goods_card')->update(['status' => 0], [['card_id', '=', $card_item_info['card_id']]]);
                    (new Cron())->deleteCron([['event', '=', 'CronMemberCardExpire'], ['relate_id', '=', $card_item_info['card_id']]]);
                }
                // 添加使用记录
                model('member_goods_card_records')->add([
                    'card_id' => $card_item_info['card_id'],
                    'site_id' => $card_item_info['site_id'],
                    'card_item_id' => $card_item_info['item_id'],
                    'type' => $item_type,
                    'relation_id' => $item_relation_id,
                    'create_time' => time(),
                    'store_id' => $item_store_id,
                    'num' => $item_num
                ]);
            }
            model('member_goods_card_item')->commit();
            return $this->success(['out_relation_ids' => $out_relation_ids]);
        } catch (\Exception $e) {
            model('member_goods_card_item')->rollback();
            Log::write('卡项使用错误，错误原因:' . $e->getMessage() . $e->getFile() . $e->getLine() . '请求参数:' . json_encode($param));
            return $this->error('', '卡项使用失败');
        }
    }

    /**
     * 获取会员卡项使用记录列表
     * @param array $condition
     * @param bool $field
     * @param string $order
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getMemberCardRecordsList($condition = [], $field = true, $order = '', $alias = 'a', $join = [])
    {
        $data = model('member_goods_card_records')->getList($condition, $field, $order, $alias, $join);
        return $this->success($data);
    }

    public function getMemberCardRecordsPageList($condition = [], $field = true, $order = '', $page = 1, $list_rows = PAGE_LIST_ROWS, $alias = 'a', $join = [])
    {
        $data = model('member_goods_card_records')->pageList($condition, $field, $order, $page, $list_rows, $alias, $join);
        return $this->success($data);
    }

    /**
     * 查询当前会员是否可以使用卡项
     * @param $params
     * @param bool $is_buy
     * @return array
     */
    public function getMemberCardUse($params)
    {
        if (!empty($params['card_item_id'])) {
            $card_condition = [
                ['moi.site_id', '=', $params['site_id']],
                ['moi.member_id', '=', $params['member_id']],
                ['', 'exp', Db::raw('(mo.end_time = 0 or mo.end_time > ' . time() . ') and mo.status = ' . self::STATUS_NORMAL)],
                ['moi.item_id', '=', $params['card_item_id']]
            ];

            $card_field = 'moi.end_time, moi.card_id, moi.item_id';
            $card_join = [
                ['member_goods_card mo', 'mo.card_id = moi.card_id', 'left'],
                ['goods_card gc', 'gc.goods_id = mo.goods_id', 'left']
            ];
            $card_item_info = $this->getCartItemInfo($card_condition, $card_field, 'moi', $card_join)['data'];
            return $this->success($card_item_info);
        }
        return $this->error();
    }

    /**
     * 查询会员可用的卡项(关联商品查询)
     * @param $params
     */
    public function getMemberUseCardList($params)
    {
        $site_id = $params['site_id'] ?? 0;
        $member_id = $params['member_id'] ?? 0;
        $goods_id = $params['goods_id'] ?? 0;
        $sku_id = $params['sku_id'];
        $alias = 'moi';
        $condition = array(
            [$alias . '.member_id', '=', $member_id],
            ['mo.status', '=', 1],
        );
        if ($goods_id > 0) {
            $condition[] = [$alias . '.goods_id', '=', $goods_id];
        }
        if ($sku_id > 0) {
            $condition[] = [$alias . '.sku_id', '=', $sku_id];
        }
        $field = 'moi.*,mo.status';
        $join = [
            ['member_goods_card mo', 'mo.card_id = moi.card_id', 'left'],
            ['goods_card gc', 'gc.goods_id = mo.goods_id', 'left']
        ];
        $list = model('member_goods_card_item')->getList($condition, $field, '', $alias, $join);
        return $this->success($list);
    }

    /**
     * 检验商品是否可被卡项抵扣
     * @param $params
     */
    public function getUseCardNum($params)
    {
        $member_id = $params['member_id'];
        $sku_id = $params['sku_id'];
        $item_id = $params['item_id'];
        $card_item_condition = array(
            ['member_id', '=', $member_id],
            ['sku_id', '=', $sku_id],
            ['item_id', '=', $item_id]
        );
        $card_item_info = model('member_goods_card_item')->getInfo($card_item_condition);
        if (empty($card_item_info))
            return $this->error([], '没有可用的卡项');

        $card_type = $card_item_info['card_type'];
        $card_id = $card_item_info['card_id'];
        $card_condition = array(
            ['card_id', '=', $card_id]
        );
        $card_info = model('member_goods_card')->getInfo($card_condition);
        if (empty($card_item_info))
            return $this->error([], '没有可用的卡项');

        $return_params = array(
            'card_item_info' => $card_item_info,
            'card_info' => $card_info
        );
        $status = $card_info['status'];
        if ($status != self::STATUS_NORMAL)
            return $this->error($return_params, '卡包已失效');

        $total_num = $card_info['total_num'];
        $total_use_num = $card_info['total_use_num'];

        switch ($card_type) {
            case 'oncecard'://限次
                $item_num = $card_item_info['num'];
                $item_use_num = $card_item_info['use_num'];
                $surplus_num = $item_num - $item_use_num;
                break;
            case 'timecard'://限时
                $surplus_num = 0;
                break;
            case 'commoncard'://通用共享次数
                $surplus_num = $total_num - $total_use_num;
                break;
            case ''://通用共享次数
                $item_num = $card_item_info['num'];
                $item_use_num = $card_item_info['use_num'];
                $surplus_num = $item_num - $item_use_num;
                break;
        }
        $return_params['card_num'] = $surplus_num;
        return $this->success($return_params);

    }

    /**
     * 关闭会员卡项
     * @param $card_id
     */
    public function memberOncecardClose($card_ids)
    {
        model('member_goods_card')->startTrans();
        try {
            // 关闭会员卡项
            model('member_goods_card')->update(['status' => 0], [['card_id', 'in', $card_ids]]);
            (new Cron())->deleteCron([['event', '=', 'CronMemberCardExpire'], ['relate_id', 'in', $card_ids]]);

            // 关闭核销码
            $verify_ids = model('member_goods_card_item')->getColumn([['card_id', 'in', $card_ids]], 'member_verify_id');
            model('member_verify')->update(['state' => -1], [['id', 'in', $verify_ids]]);
            (new Cron())->deleteCron([['event', 'in', ['CronMemberVerifyClose', 'CronVerifyClosePreRemind']], ['relate_id', '=', $verify_ids]]);

            model('member_goods_card')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('member_goods_card')->rollback();
            return $this->error();
        }
    }

    /**
     * 卡包失效
     * @param $condition
     * @param $invalid_type
     * @return array
     */
    public function memberOncecardInvalid($condition, $invalid_type)
    {
        $config = [
            'expired' => ['reason' => '已过期', 'verify_status' => Verify::STATUS_EXPIRED],
            'refunded' => ['reason' => '已退款', 'verify_status' => Verify::STATUS_REFUNDED],
        ];
        if (!in_array($invalid_type, ['expired', 'refunded'])) return $this->error(null, '非法的失效类型');
        $member_card_list = model('member_goods_card')->getList($condition);
        if (!empty($member_card_list)) {
            foreach ($member_card_list as $member_card_info) {
                model('member_goods_card')->update([
                    'status' => self::STATUS_INVALID,
                    'invalid_reason' => $config[$invalid_type]['reason'],
                ], [['card_id', '=', $member_card_info['card_id']]]);
                $invalid_verify_ids = model('member_goods_card_item')->getColumn([
                    ['card_id', '=', $member_card_info['card_id']],
                    ['num', '<>', Db::raw('use_num')],
                ], 'member_verify_id');
                if (!empty($invalid_verify_ids)) {
                    $verify_model = new Verify();
                    $verify_model->editVerify(['is_verify' => $config[$invalid_type]['verify_status']], [['id', 'in', $invalid_verify_ids]]);
                }
            }
        }

        return $this->success();
    }

    /**
     * 卡项使用退还
     * @param $item_list [ ['type' => '', 'relation_id' => ''] ]
     */
    public function memberOncecardItemRefund($item_list)
    {
        model('member_goods_card_item')->startTrans();
        try {
            $records_ids = [];
            foreach ($item_list as $item) {
                $item_info = model('member_goods_card_records')->getInfo([
                    ['mgcr.type', '=', $item['type']],
                    ['mgcr.relation_id', '=', $item['relation_id']],
                ], 'mgcr.id,mgcr.card_id,mgcr.num,mgci.item_id,mgci.card_type,mgci.member_verify_id,mgc.status,mgc.end_time', 'mgcr', [
                    ['member_goods_card_item mgci', 'mgci.item_id = mgcr.card_item_id', 'inner'],
                    ['member_goods_card mgc', 'mgci.card_id = mgc.card_id', 'inner']
                ]);
                if (!empty($item_info)) {
                    if ($item_info['card_type'] == 'commoncard') {
                        model('member_goods_card_item')->setDec([['card_id', '=', $item_info['card_id']]], 'use_num', $item_info['num']);
                        $verify_ids = model('member_goods_card_item')->getColumn([['card_id', '=', $item_info['card_id']]], 'member_verify_id');
                        if (!empty($verify_ids)) {
                            model('verify')->setDec([['id', 'in', $verify_ids]], 'verify_use_num', $item_info['num']);
                            if ($item_info['status'] == 0 && ($item_info['end_time'] == 0 || $item_info['end_time'] > time())) {
                                model('verify')->update(['is_verify' => Verify::STATUS_NOT_VERIFY, 'verify_time' => 0], [['id', 'in', $verify_ids]]);
                            }
                        }
                    } else {
                        model('member_goods_card_item')->setDec([['item_id', '=', $item_info['item_id']]], 'use_num', $item_info['num']);
                        model('verify')->setDec([['id', '=', $item_info['member_verify_id']]], 'verify_use_num', $item_info['num']);
                        // 恢复核销码状态
                        if ($item_info['status'] == 0 && ($item_info['end_time'] == 0 || $item_info['end_time'] > time())) {
                            model('verify')->update(['is_verify' => Verify::STATUS_NOT_VERIFY, 'verify_time' => 0], [['id', '=', $item_info['member_verify_id']]]);
                        }
                    }
                    model('member_goods_card')->setDec([['card_id', '=', $item_info['card_id']]], 'total_use_num', $item_info['num']);
                    $records_ids[] = $item_info['id'];

                    // 判断卡是否为不可用状态
                    if ($item_info['status'] == 0 && ($item_info['end_time'] == 0 || $item_info['end_time'] > time())) {
                        model('member_goods_card')->update([
                            'status' => 1
                        ], [['card_id', '=', $item_info['card_id']]]);
                    }
                }
            }
            // 删除使用记录
            if (!empty($records_ids)) model('member_goods_card_records')->delete([['id', 'in', $records_ids]]);

            model('member_goods_card_item')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('member_goods_card_item')->rollback();
            Log::write('卡项退还错误，错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            return $this->error('', '卡项退还失败');
        }
    }

    /**
     * 查询卡项活动信息
     * @param $condition
     */
    public function getCardSelect($condition)
    {
        $info = model('goods_card')->getInfo($condition);
        return $this->success($info);
    }
}