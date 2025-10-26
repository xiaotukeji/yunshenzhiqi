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

namespace addon\cardservice\storeapi\controller;

use addon\cardservice\model\MemberCard as MemberCardModel;
use app\storeapi\controller\BaseStoreApi;

/**
 * 会员管理 控制器
 */
class Membercard extends BaseStoreApi
{
    /**
     * 会员卡项
     */
    public function lists()
    {
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $status = $this->params[ 'status' ] ?? 'all';

        $member_oncecard_model = new MemberCardModel();
        $condition = [
            [ 'mgc.member_id', '=', $member_id ],
            [ 'mgc.site_id', '=', $this->site_id ],
            [ 'g.is_delete', '=', 0 ]
        ];
        if ($status != 'all') $condition[] = [ 'mgc.status', '=', $status ];

        $field = 'mgc.card_id, mgc.create_time, mgc.end_time, mgc.status, mgc.card_code, mgc.card_type, mgc.goods_name, mgc.total_num, mgc.total_use_num';
        $res = $member_oncecard_model->getCardPageList($condition, $field, 'mgc.create_time desc', $page, $page_size, 'mgc', [
            [ 'goods g', 'g.goods_id = mgc.goods_id', 'inner' ]
        ]);

        if (empty($res[ 'data' ])) return $res;

        foreach ($res[ 'data' ][ 'list' ] as $k => &$v) {
            $alias = 'i';
            $join = [
                [ 'goods g', 'i.goods_id = g.goods_id', 'inner' ],
                [ 'goods_sku gs', 'i.sku_id = gs.sku_id', 'inner' ],
                [ 'verify mv', 'i.member_verify_id = mv.id', 'inner' ],
                [ 'store_goods_sku sgs', 'i.sku_id=sgs.sku_id and sgs.store_id=' . $this->store_id, 'inner' ],
                [ 'store s', 's.store_id = sgs.store_id', 'left' ]
            ];
            $field = 'i.*,  gs.sku_name,gs.sku_image, gs.is_virtual, mv.verify_code, IF(g.is_unify_price = 1,gs.price,sgs.price) as price,IF(s.stock_type = "store",sgs.stock, gs.stock) as stock';
            $order = 'i.item_id asc';
            $res[ 'data' ][ 'list' ][ $k ][ 'item_list' ] = $member_oncecard_model->getCartItemList([
                [ 'i.card_id', '=', $v[ 'card_id' ] ],
                [ 'i.site_id', '=', $this->site_id ],
                [ 'i.member_id', '=', $member_id ],
                [ 'sgs.status', '=', 1 ],
                [ 'g.is_delete', '=', 0 ]
            ], $field, $order, $alias, $join)[ 'data' ];
            foreach ($res[ 'data' ][ 'list' ][ $k ][ 'item_list' ] as $ck => $cv) {
                $res[ 'data' ][ 'list' ][ $k ][ 'item_list' ][ $ck ][ 'stock' ] = numberFormat($res[ 'data' ][ 'list' ][ $k ][ 'item_list' ][ $ck ][ 'stock' ]);
            }
        }
        return $this->response($res);
    }

    /**
     * 卡项详情
     */
    public function detail()
    {
        $card_id = $this->params[ 'card_id' ] ?? 0;
        $member_id = $this->params[ 'member_id' ] ?? 0;
        if (empty($card_id)) {
            return $this->response($this->error('', 'REQUEST_ID'));
        }

        $model = new MemberCardModel();
        $condition = [
            [ 'mgc.site_id', '=', $this->site_id ],
            [ 'mgc.member_id', '=', $member_id ],
            [ 'mgc.card_id', '=', $card_id ],
            [ 'g.is_delete', '=', 0 ],
        ];
        $field = 'mgc.*, g.goods_name,g.price,g.goods_image,g.introduction,g.goods_content';
        $join = [
            [ 'goods g', 'mgc.goods_id = g.goods_id', 'inner' ],
        ];
        $info = $model->getCardInfo($condition, $field, 'mgc', $join)[ 'data' ];

        if (empty($info)) return $this->response($this->error('', '未获取到卡项信息'));
        $condition = [
            [ 'mgci.card_id', '=', $info[ 'card_id' ] ],
            [ 'sku.is_delete', '=', 0 ],
        ];
        $info[ 'card_item' ] = $model->getCartItemList($condition, 'mgci.*,sku.sku_name,sku.price,sku.sku_image,sku.sku_images,sku.goods_class_name,sku.stock,
        v.verify_code,v.verify_type,v.verify_type_name,v.verify_content_json,v.verifier_id,v.verifier_name,v.is_verify,v.verify_time,v.expire_time,v.verify_from,v.verify_remark,v.verify_total_count,v.verify_use_num', 'mgci.card_id asc', 'mgci', [
                [ 'goods_sku sku', 'mgci.sku_id = sku.sku_id', 'inner' ],
                [ 'verify v', 'mgci.member_verify_id = v.id', 'left' ],
            ])[ 'data' ] ?? [];
        foreach ($info[ 'card_item' ] as $k => $v) {
            $info[ 'card_item' ][ $k ][ 'stock' ] = numberFormat($info[ 'card_item' ][ $k ][ 'stock' ]);
        }

        return $this->response($this->success($info));
    }

    /**
     * 使用记录
     */
    public function records()
    {
        $card_id = $this->params[ 'card_id' ] ?? 0;
        $item_id = $this->params[ 'item_id' ] ?? 0;
        $member_id = $this->params[ 'member_id' ] ?? 0;
        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        if (empty($member_id) || ( empty($card_id) && empty($item_id) )) {
            return $this->response($this->error([], '请传入必要参数'));
        }

        $condition = [];
        $condition[] = [ 'cr.site_id', '=', $this->site_id ];
        $condition[] = [ 'ci.member_id', '=', $member_id ];
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
        $list = $model->getMemberCardRecordsPageList($condition, $field, 'cr.create_time desc', $page, $page_size, $alias, $join);
        return $this->response($list);
    }
}