<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\store\model;

use app\model\BaseModel;
use app\model\system\Export as ExportModel;
use think\facade\Db;

class StoreAccount extends BaseModel
{
    public $period_types = [ 1, 2, 3 ];//转账周期类型1.天  2. 周  3. 月

    public $from_type = [
        'order' => [
            'type_name' => '订单结算',
            'type_url' => '',
        ],
        'refund' => [
            'type_name' => '订单退款',
            'type_url' => '',
        ],
        'withdraw' => [
            'type_name' => '门店提现',
            'type_url' => '',
        ],
    ];

    /**
     * 获取门店转账设置
     */
    public function getStoreWithdrawConfig($site_id)
    {
        $config = new Config();
        $res = $config->getStoreWithdrawConfig($site_id);
        return $res;
    }

    /**
     * 获取门店待结算订单金额
     */
    public function getWaitSettlementInfo($store_id)
    {
        $money_info = model('order')->getInfo([
            [ 'store_id', '=', $store_id ],
            [ 'order_status', '=', 10 ],
            [ 'store_settlement_id', '=', 0 ]
        ], 'sum(order_money) as order_money, sum(refund_money) as refund_money, sum(shop_money) as shop_money, sum(platform_money) as platform_money, sum(refund_shop_money) as refund_shop_money, sum(refund_platform_money) as refund_platform_money, sum(commission) as commission');
        if (empty($money_info) || $money_info == null) {

            $money_info = [
                'order_money' => 0,
                'refund_money' => 0,
                'shop_money' => 0,
                'platform_money' => 0,
                'refund_shop_money' => 0,
                'refund_platform_money' => 0,
                'commission' => 0
            ];

        }
        return $money_info;
    }

    /**
     * 门店账户记录操作
     * @param $params
     */
    public function addStoreAccount($params)
    {
        $site_id = $params[ 'site_id' ];
        $store_id = $params[ 'store_id' ];
        $account_data = $params[ 'account_data' ];
        $remark = $params[ 'remark' ];
        $from_type = $params[ 'from_type' ];
        $related_id = $params[ 'related_id' ];
        $is_limit = $params[ 'is_limit' ] ?? 1;//是否限制不能小于0
        model('store_account')->startTrans();
        try {
            //账户检测
            $store_account = Db::name('store')->where([
                [ 'store_id', '=', $store_id ],
                [ 'site_id', '=', $site_id ]
            ])->field('account')->lock(true)->find();

            $account_new_data = round((float) $store_account[ 'account' ] + (float) $account_data, 2);

            if ($is_limit == 1 && (float) $account_new_data < 0) {
                model('store_account')->rollback();
                $msg = '账户余额不足';
                return $this->error('', $msg);
            }

            //添加记录
            $type_info = $this->from_type[ $from_type ];
            $data = array (
                'site_id' => $site_id,
                'store_id' => $store_id,
                'account_data' => $account_data,
                'from_type' => $from_type,
                'type_name' => $type_info[ 'type_name' ],
                'create_time' => time(),
                'remark' => $remark,
                'related_id' => $related_id,
            );

            model('store_account')->add($data);
            //账户更新
            model('store')->update([
                'account' => $account_new_data
            ], [
                [ 'store_id', '=', $store_id ]
            ]);
            model('store_account')->commit();
            return $this->success();
        } catch (\Exception $e) {
            model('store_account')->rollback();
            return $this->error('', $e->getMessage());
        }
    }

    public function getStoreAccountInfo($condition, $field = '*', $alias = 'a', $join = [])
    {
        $info = model('store_account')->getInfo($condition, $field, $alias, $join);
        return $this->success($info);
    }

    /**
     * 获取账户分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array|\multitype
     */
    public function getStoreAccountPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'create_time desc,id desc', $field = '*', $alias = 'a', $join = [])
    {
        $list = model('store_account')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取账户列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param null $limit
     * @return array|\multitype
     */
    public function getStoreAccountList($condition = [], $field = '*', $order = '', $limit = null)
    {
        $list = model('store_account')->getList($condition, $field, $order, '', '', '', $limit);
        return $this->success($list);
    }

    /**
     * 添加门店账户导出
     * @param $input
     * @return array
     */
    public function addStoreAccountExport($input, $export_store_id = 0)
    {
        $remark = $input['remark'] ?? '';
        $start_date = $input['start_date'] ?? '';
        $end_date = $input['end_date'] ?? '';
        $from_type = $input['from_type'] ?? '';
        $store_id = $input['store_id'] ?? 0;
        $site_id = $input['site_id'];

        $account_model = new StoreAccount();
        $store_model = new \app\model\store\Store();

        //组装条件和条件说明
        $condition = [ [ 'sa.site_id', '=', $site_id ] ];
        $condition_desc = [];
        if (!empty($remark)) {
            $condition[] = [ 'sa.remark', 'like', '%' . $remark . '%' ];
            $condition_desc[] = ['name' => '备注信息', 'value' => $remark];
        }
        if (!empty($from_type)) {
            $condition[] = [ 'sa.from_type', '=', $from_type ];
            $from_type_list = $account_model->from_type;
            $condition_desc[] = ['name' => '来源类型', 'value' => $from_type_list[$from_type]['type_name']];
        }
        if ($store_id > 0) {
            $condition[] = [ 'sa.store_id', '=', $store_id ];
            $store_info = $store_model->getStoreInfo([['store_id', '=', $store_id]], 'store_name')['data'];
            $condition_desc[] = ['name' => '门店名称', 'value' => $store_info['store_name']];
        }
        $time_name = '';
        if (!empty($start_date) && empty($end_date)) {
            $condition[] = [ 'sa.create_time', '>=', date_to_time($start_date) ];
            $time_name = $start_date . '起';
        } elseif (empty($start_date) && !empty($end_date)) {
            $condition[] = [ 'sa.create_time', '<=', date_to_time($end_date) ];
            $time_name = '至' . $end_date;
        } elseif (!empty($start_date) && !empty($end_date)) {
            $condition[] = [ 'sa.create_time', 'between', [ date_to_time($start_date), date_to_time($end_date) ] ];
            $time_name = $start_date . ' 至 ' . $end_date;
        }
        if($time_name) $condition_desc[] = [ 'name' => '发生时间', 'value' => $time_name ];

        $param = [
            'site_id' => $site_id,
            'store_id' => $export_store_id,
            'from_type' => 'store_account',
            'from_type_name' => '门店账户',
            'condition_desc' => $condition_desc,
            'query' => [
                'table' => 'store_account',
                'alias' => 'sa',
                'join' => [
                    ['store s', 'sa.store_id = s.store_id', 'inner'],
                ],
                'condition' => $condition,
                'field' => 'sa.*,s.store_name',
                'chunk_field' => 'sa.id',
                'chunk_order' => 'asc',
            ],
            'export_field' => [
                ['field' => 'store_name', 'name' => '门店名称'],
                ['field' => 'type_name', 'name' => '来源方式'],
                ['field' => 'account_data', 'name' => '记录金额'],
                ['field' => 'create_time', 'name' => '发生时间'],
                ['field' => 'remark', 'name' => '备注'],
                ['field' => 'related_no', 'name' => '关联编号'],
            ],
            'handle' => function($item_list){
                foreach($item_list as &$item){
                    $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                }
                return $this->getRelatedInfo($item_list);
            },
        ];
        $export_model = new ExportModel();
        return $export_model->export($param);
    }

    /**
     * 获取关联单号
     * @param $item_list
     * @return mixed
     */
    public function getRelatedInfo($item_list)
    {
        $order_ids = $refund_ids = $withdraw_ids = [];
        $order_list = $refund_list = $withdraw_list = [];
        foreach($item_list as $item){
            switch($item['from_type']){
                case 'order':
                    $order_ids[] = $item['related_id'];
                    break;
                case 'refund':
                    $refund_ids[] = $item['related_id'];
                    break;
                case 'withdraw':
                    $withdraw_ids[] = $item['related_id'];
                    break;
            }
        }
        if(!empty($order_ids)){
            $order_list = model('order')->getList([['order_id', 'in', $order_ids]], 'order_id,order_no,order_from');
            $order_list = array_column($order_list, null, 'order_id');
        }
        if(!empty($refund_ids)){
            $alias = 'og';
            $join = [
                ['order o', 'o.order_id = og.order_id', 'inner'],
            ];
            $field = 'og.order_goods_id,og.refund_no,o.order_id,o.order_no,o.order_from';
            $refund_list = model('order_goods')->getList([['og.order_goods_id', 'in', $refund_ids]], $field, '', $alias, $join);
            $refund_list = array_column($refund_list, null, 'order_goods_id');
        }
        if(!empty($withdraw_ids)){
            $withdraw_list = model('store_withdraw')->getList([['withdraw_id', 'in', $withdraw_ids]], 'withdraw_id,withdraw_no');
            $withdraw_list = array_column($withdraw_list, null, 'withdraw_id');
        }
        foreach($item_list as &$item){
            switch($item['from_type']){
                case 'order':
                    $item['related_no'] = $order_list[$item['related_id']]['order_no'] ?? '';
                    $item['related_info'] = $order_list[$item['related_id']] ?? null;
                    break;
                case 'refund':
                    $item['related_no'] = $refund_list[$item['related_id']]['refund_no'] ?? '';
                    $item['related_info'] = $refund_list[$item['related_id']] ?? null;
                    break;
                case 'withdraw':
                    $item['related_no'] = $withdraw_list[$item['related_id']]['withdraw_no'] ?? '';
                    $item['related_info'] = $withdraw_list[$item['related_id']] ?? null;
                    break;
            }
        }
        return $item_list;
    }
}