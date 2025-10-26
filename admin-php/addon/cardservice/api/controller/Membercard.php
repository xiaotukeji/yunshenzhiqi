<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cardservice\api\controller;

use addon\cardservice\model\MemberCard as MemberCardModel;
use app\api\controller\BaseApi;
use app\model\verify\Verify;

/**
 * 会员卡项
 */
class Membercard extends BaseApi
{

    public function detail()
    {
        $this->initStoreData();

        $card_id = $this->params['card_id'] ?? 0;
        if (empty($card_id)) {
            return $this->response($this->error('', 'REQUEST_ID'));
        }
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $model = new MemberCardModel();
        $condition = [
            [ 'mgc.site_id', '=', $this->site_id ],
            [ 'mgc.member_id', '=', $this->member_id ],
            [ 'mgc.card_id', '=', $card_id ],
            [ 'g.is_delete', '=', 0 ],
        ];
        $field = 'mgc.*, g.goods_name,g.price,g.goods_image,g.introduction,g.goods_content';
        $join = [
            [ 'goods g', 'mgc.goods_id = g.goods_id', 'inner' ],
        ];
        $info = $model->getCardInfo($condition, $field, 'mgc', $join)[ 'data' ];

        $condition = [
            [ 'mgci.card_id', '=', $info[ 'card_id' ] ],
        ];
        $stock_field = 'sku.stock';
        $join = [
            [ 'goods_sku sku', 'mgci.sku_id = sku.sku_id', 'inner' ],
            [ 'verify v', 'mgci.member_verify_id = v.id', 'left' ],
        ];
        if ($this->store_data[ 'config' ][ 'store_business' ] == 'store') {
            $stock_field = 'sgs.stock';
            $join[] = [ 'store_goods_sku sgs', 'sku.sku_id = sgs.sku_id and sgs.store_id = '.$this->store_id, 'left'];
        }

        $info[ 'card_item' ] = $model->getCartItemList($condition, 'mgci.*,sku.sku_name,sku.price,sku.sku_image,sku.sku_images,sku.goods_class_name,'.$stock_field.',
        v.verify_code,v.verify_type,v.verify_type_name,v.verify_content_json,v.verifier_id,v.verifier_name,v.is_verify,v.verify_time,v.expire_time,v.verify_from,v.verify_remark,v.verify_total_count,v.verify_use_num', 'mgci.card_id asc', 'mgci', $join)[ 'data' ] ?? [];

        $verify = new Verify();
        foreach ($info[ 'card_item' ] as $k => $v) {
            if ($v[ 'member_verify_id' ] > 0) {
                $info[ 'card_item' ][ $k ][ 'verify_code_data' ] = $verify->qrcode($v[ 'verify_code' ], 'h5', 'pickup', $this->site_id, 'create')[ 'data' ] ?? [];
                $info[ 'card_item' ][ $k ][ 'barcode' ] = getBarcode($v[ 'verify_code' ], 'upload/qrcode/pickup');
                $info[ 'card_item' ][ $k ][ 'stock' ] = numberFormat($info[ 'card_item' ][ $k ][ 'stock' ]);
            }
        }

        return $this->response($this->success($info));
    }

    /**
     * 列表信息
     */
    public function page()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $status = $this->params['status'] ?? 'all';

        $condition = [];
        $condition[] = [ 'mgc.site_id', '=', $this->site_id ];
        $condition[] = [ 'mgc.member_id', '=', $this->member_id ];
        if ($status !== 'all') {
            $condition[] = [ 'mgc.status', '=', $status ];
        }
        $condition[] = [ 'g.is_delete', '=', 0 ];
        $alias = 'mgc';

        $field = 'mgc.*, g.goods_name,g.price,g.goods_image,g.introduction';

        $join = [
            [ 'goods g', 'mgc.goods_id = g.goods_id', 'inner' ],
        ];

        $model = new MemberCardModel();
        $list = $model->getCardPageList($condition, $field, 'mgc.create_time desc', $page, $page_size, $alias, $join);
        return $this->response($list);
    }

    /**
     * 使用记录
     */
    public function records()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $card_id = $this->params['card_id'] ?? 0;
        $item_id = $this->params['item_id'] ?? 0;
        if (empty($card_id) && empty($item_id)) {
            return $this->response($this->error([], '请传入必要参数'));
        }

        $condition = [];
        $condition[] = [ 'cr.site_id', '=', $this->site_id ];
        $condition[] = [ 'ci.member_id', '=', $this->member_id ];
        if (!empty($item_id)) {
            $condition[] = [ 'cr.card_item_id', '=', $item_id ];
        }
        if (!empty($card_id)) {
            $condition[] = [ 'cr.card_id', '=', $card_id ];
        }
        $alias = 'cr';
        $prefix = config('database.connections.mysql.prefix');
        $field = 'cr.*, sku.sku_name,sku.sku_image,sku.sku_images,sku.price,ci.num as item_num,
        IF(cr.type = \'order\', (select order_id from `' . $prefix . 'order_goods` og where og.order_goods_id = cr.relation_id), 0) as order_id';

        $join = [
            [ 'member_goods_card_item ci', 'ci.item_id = cr.card_item_id', 'left' ],
            [ 'goods_sku sku', 'ci.sku_id = sku.sku_id', 'left' ],
        ];

        $model = new MemberCardModel();
        $list = $model->getMemberCardRecordsList($condition, $field, 'cr.create_time desc', $alias, $join);
        return $this->response($list);
    }

}