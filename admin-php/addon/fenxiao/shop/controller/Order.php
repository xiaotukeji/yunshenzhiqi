<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\shop\controller;

use addon\fenxiao\model\FenxiaoOrder;
use app\shop\controller\BaseShop;
use think\App;

/**
 *  分销订单
 */
class Order extends BaseShop
{
    public function __construct(App $app = null)
    {
        $this->replace = [
            'FENXIAO_JS' => __ROOT__ . '/addon/fenxiao/shop/view/public/js',
            'FENXIAO_CSS' => __ROOT__ . '/addon/fenxiao/shop/view/public/css'
        ];
        parent::__construct($app);
    }

    /**
     * 分销订单列表
     */
    public function lists()
    {
        if (request()->isJson()) {
            $model = new FenxiaoOrder();
            $page_index = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $status = input('status', 0);
            $condition = [ [ 'fo.site_id', '=', $this->site_id ] ];
            if ($status == 3) {
                $condition[] = [ 'fo.is_refund', '=', 1 ];
            }
            if (in_array($status, [ 1, 2 ])) {
                $condition[] = [ 'fo.is_settlement', '=', $status - 1 ];
            }
            $search_text_type = input('search_text_type', "sku_name");//商品名称/订单编号
            $search_text = input('search_text', "");
            if (!empty($search_text)) {
                $condition[] = [ 'fo.' . $search_text_type, 'like', '%' . $search_text . '%' ];
            }
            //下单时间
            $start_time = input('start_time', '');
            $end_time = input('end_time', '');
            if (!empty($start_time) && empty($end_time)) {
                $condition[] = [ 'fo.create_time', '>=', date_to_time($start_time) ];
            } elseif (empty($start_time) && !empty($end_time)) {
                $condition[] = [ 'fo.create_time', '<=', date_to_time($end_time) ];
            } elseif (!empty($start_time) && !empty(date_to_time($end_time))) {
                $condition[] = [ 'fo.create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
            }

            $list = $model->getFenxiaoOrderPage($condition, $page_index, $page_size);

            return $list;
        } else {

            //订单状态
            return $this->fetch('order/lists');
        }
    }

    public function detail()
    {
        $fenxiao_order_model = new FenxiaoOrder();
        $order_id = input('order_id', '');
        $order_info = $fenxiao_order_model->getFenxiaoOrderDetail([ [ 'order_id', '=', $order_id ] ]);
        if (empty($order_info[ 'data' ])) $this->error('未获取到订单数据', href_url('fenxiao://shop/order/lists'));
        $this->assign('order_detail', $order_info[ 'data' ]);
        return $this->fetch('order/detail');
    }

    /**
     * 订单导出
     */
    public function exportorder()
    {
        $model = new FenxiaoOrder();
        $status = input('status', 0);
        $fenxiao_order_id = input('order_ids', "");

        $condition = [ [ 'fo.site_id', '=', $this->site_id ] ];
        if ($status == 3) {
            $condition[] = [ 'fo.is_refund', '=', 1 ];
        }
        if (in_array($status, [ 1, 2 ])) {
            $condition[] = [ 'fo.is_settlement', '=', $status - 1 ];
        }
        $search_text_type = input('search_text_type', "goods_name");//商品名称/订单编号
        $search_text = input('search_text', "");
        if (!empty($search_text)) {
            $condition[] = [ 'fo.' . $search_text_type, 'like', '%' . $search_text . '%' ];
        }
        //下单时间
        $start_time = input('start_time', '');
        $end_time = input('end_time', '');
        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ 'fo.create_time', '>=', date_to_time($start_time) ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'fo.create_time', '<=', date_to_time($end_time) ];
        } elseif (!empty($start_time) && !empty(date_to_time($end_time))) {
            $condition[] = [ 'fo.create_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
        }
        if ($fenxiao_order_id) {
            $condition = [];
            $condition[] = [ "fo.fenxiao_order_id", "in", $fenxiao_order_id ];
        }
        $model->orderExport($condition);
        return;
    }
}