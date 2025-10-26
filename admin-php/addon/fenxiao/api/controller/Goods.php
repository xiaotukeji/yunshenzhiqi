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

use addon\fenxiao\model\Config as ConfigModel;
use addon\fenxiao\model\Fenxiao as FenxiaoModel;
use addon\fenxiao\model\FenxiaoGoodsCollect as FenxiaoGoodsCollectModel;
use addon\fenxiao\model\FenxiaoGoodsSku as FenxiaoGoodsSkuModel;
use app\api\controller\BaseApi;

/**
 * 分销商品
 */
class Goods extends BaseApi
{

    /**
     * 分销商品详情
     * @param int $id
     * @return false|string
     */
    public function detail($id = 0)
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $sku_id = $this->params['sku_id'] ?? 0;
        if (!empty($id)) {
            $sku_id = $id;
        }
        if (empty($sku_id)) {
            return $this->response($this->error('', 'REQUEST_SKU_ID'));
        }

        $config = new ConfigModel();

        $words_config = $config->getFenxiaoWordsConfig($this->site_id)[ 'data' ][ 'value' ];

        $data = [
            'words_account' => $words_config[ 'account' ],
            'commission_money' => 0.00
        ];

        $fenxiao_model = new FenxiaoModel();
        $fenxiao_info = $fenxiao_model->getFenxiaoInfo([ [ 'member_id', '=', $this->member_id ] ], 'fenxiao_id,level_id')[ 'data' ];
        if (empty($fenxiao_info)) return $this->response($this->error());

        $fenxiao_goods_sku_model = new FenxiaoGoodsSkuModel();
        $data[ 'commission_money' ] = $fenxiao_goods_sku_model->getSkuFenxiaoCommission($sku_id, $fenxiao_info[ 'level_id' ])[ 'data' ];

        $basics = $config->getFenxiaoBasicsConfig($this->site_id);
        $data[ 'is_commission_money' ] = $basics[ 'data' ][ 'value' ][ 'is_commission_money' ];
        return $this->response($this->success($data));
    }

    /**
     * 分销商品分页列表
     */
    public function page()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $keyword = $this->params['keyword'] ?? '';//关键词
        $category_id = $this->params['category_id'] ?? 0;//分类
        $min_price = $this->params['min_price'] ?? 0;//价格区间，小
        $max_price = $this->params['max_price'] ?? 0;//价格区间，大
        $is_free_shipping = $this->params['is_free_shipping'] ?? 0;//是否免邮
        $order = $this->params['order'] ?? 'create_time';//排序（综合、销量、价格）
        $sort = $this->params['sort'] ?? 'desc';//升序、降序
        $goods_id_arr = $this->params['goods_id_arr'] ?? '';//goods_id数组

        $condition = [
            [ 'g.is_fenxiao', '=', 1 ],
            [ 'g.goods_state', '=', 1 ],
            [ 'g.is_delete', '=', 0 ],
            [ 'g.site_id', '=', $this->site_id ]
        ];

        if (!empty($keyword)) {
            $condition[] = [ 'g.goods_name|g.keywords', 'like', '%' . $keyword . '%' ];
        }

        if (!empty($category_id)) {
            $condition[] = [ 'g.category_id', 'like', '%,' . $category_id . ',%' ];
        }

        if (!empty($goods_id_arr)) {
            $condition[] = [ 'g.goods_id', 'in', $goods_id_arr ];
        }

        if ($min_price != '' && $max_price != '') {
            $condition[] = [ 'g.discount_price', 'between', [ $min_price, $max_price ] ];
        } elseif ($min_price != '') {
            $condition[] = [ 'g.discount_price', '>=', $min_price ];
        } elseif ($max_price != '') {
            $condition[] = [ 'g.discount_price', '<=', $max_price ];
        }

        if (!empty($is_free_shipping)) {
            $condition[] = [ 'g.is_free_shipping', '=', $is_free_shipping ];
        }

        // 非法参数进行过滤
        if ($sort != 'desc' && $sort != 'asc') {
            $sort = '';
        }

        // 非法参数进行过滤
        if ($order != '') {
            if ($order != 'sale_num' && $order != 'discount_price') {
                $order = 'gs.create_time';
            } elseif ($order != 'sale_num') {
                $order = 'gs.' . $order;
            } else {
                $order = 'sale_sort';
            }
            $order_by = $order . ' ' . $sort;
        } else {
            $order_by = 'g.sort desc,g.create_time desc';
        }

        $fenxiao_model = new FenxiaoModel();
        $fenxiao_info = $fenxiao_model->getFenxiaoInfo([ [ 'member_id', '=', $this->member_id ] ], 'fenxiao_id,level_id')[ 'data' ];

//        $fenxiao_level = new FenxiaoLevel();
//        $level_info = $fenxiao_level->getLevelInfo([ [ 'level_id', '=', $fenxiao_info[ 'level_id' ] ] ], 'one_rate')[ 'data' ];

        $fenxiao_goods_sku_model = new FenxiaoGoodsSkuModel();
        $list = $fenxiao_goods_sku_model->getFenxiaoGoodsSkuPageList($condition, $page, $page_size, $order_by);

        $fenxiao_goods_collect_model = new FenxiaoGoodsCollectModel();

        // 计算佣金
        foreach ($list[ 'data' ][ 'list' ] as $k => $v) {
            $collection_info = $fenxiao_goods_collect_model->getCollectInfo([
                [ 'member_id', '=', $this->member_id ],
                [ 'goods_id', '=', $v[ 'goods_id' ] ],
                [ 'fenxiao_id', '=', $fenxiao_info[ 'fenxiao_id' ] ],
            ], 'collect_id');

            // 查询是否关注该分销商品
            $collection_info = $collection_info[ 'data' ];
            if (!empty($collection_info)) {
                $list[ 'data' ][ 'list' ][ $k ][ 'is_collect' ] = 1;
                $list[ 'data' ][ 'list' ][ $k ][ 'collect_id' ] = $collection_info[ 'collect_id' ];
            } else {
                $list[ 'data' ][ 'list' ][ $k ][ 'is_collect' ] = 0;
            }

            $sku_commission_info = $fenxiao_goods_sku_model->getSkuFenxiaoCommission($v[ 'sku_id' ], $fenxiao_info[ 'level_id' ]);
            $list[ 'data' ][ 'list' ][ $k ][ 'commission_money' ] = $sku_commission_info[ 'data' ];
        }

        return $this->response($list);

    }

}