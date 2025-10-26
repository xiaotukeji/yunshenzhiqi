<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace addon\cardservice\shopapi\controller;

use addon\cardservice\model\CardGoods;
use addon\cardservice\model\MemberCard as MemberCardModel;
use app\shopapi\controller\BaseApi;

/**
 *
 * Class Goods
 */
class Goods extends BaseApi
{
    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();

        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            echo $this->response($token);
            exit;
        }
    }

    /**
     * 卡项购买记录
     */
    public function cardList(){
        $model = new MemberCardModel();

        $page_index = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $goods_id = $this->params['goods_id'] ?? 0;
        $search_text = $this->params['search_text'] ?? '';

        $condition = [
            [ 'mgc.site_id', '=', $this->site_id ],
            [ 'mgc.goods_id', '=', $goods_id ],
        ];
        if (!empty($search_text)) {
            $condition[] = [ 'm.nickname', 'like', '%' . $search_text . '%' ];
        }

        $field = 'mgc.*, g.goods_name,g.price,g.goods_image,m.username,m.nickname,m.headimg';
        $join = [
            [ 'goods g', 'mgc.goods_id = g.goods_id', 'inner' ],
            [ 'member m', 'mgc.member_id = m.member_id', 'left' ],
        ];
        $list = $model->getCardPageList($condition, $field, 'mgc.create_time desc', $page_index, $page_size, 'mgc', $join);
        return $this->response($list);
    }

    /**
     * 卡项购买记录详情
     */
    public function cardDetail(){
        $card_id = $this->params['card_id'] ?? 0;

        $model = new MemberCardModel();
        $card_goods = new CardGoods();
        $condition = [
            [ 'mgc.card_id', '=', $card_id ],
            [ 'mgc.site_id', '=', $this->site_id ],
        ];
        $field = 'mgc.*, g.goods_name,g.price,g.goods_image,m.username,m.nickname,m.headimg';
        $join = [
            [ 'goods g', 'mgc.goods_id = g.goods_id', 'inner' ],
            [ 'member m', 'mgc.member_id = m.member_id', 'left' ],
        ];
        $detail = $model->getCardInfo($condition, $field, 'mgc', $join)[ 'data' ] ?? [];
        $detail[ 'card_type_name' ] = $card_goods->getCardType($detail[ 'card_type' ])[ 'title' ];

        $condition = [
            [ 'mgc.card_id', '=', $card_id ],
            [ 'g.goods_state', '=', 1 ],
            [ 'g.is_delete', '=', 0 ]
        ];
        $join = [
            [ 'goods_sku g', 'mgc.sku_id = g.sku_id', 'left' ],
        ];
        $item_list = $model->getCartItemList($condition, 'mgc.*, g.sku_name', 'mgc.item_id asc', 'mgc', $join)[ 'data' ] ?? [];
        $detail['item_list'] = $item_list;

        return $this->response($this->success($detail));
    }

    /**
     * 卡项使用记录
     * @return false|string
     */
    public function cardUseRecord()
    {
        $page_index = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $card_id = $this->params['card_id'] ?? 0;
        $item_id = $this->params['item_id'] ?? 0;

        $condition = [];
        $condition[] = [ 'cr.site_id', '=', $this->site_id ];
        if (!empty($item_id)) {
            $condition[] = [ 'cr.card_item_id', '=', $item_id ];
        }
        if (!empty($card_id)) {
            $condition[] = [ 'cr.card_id', '=', $card_id ];
        }
        $alias = 'cr';
        $prefix = config('database.connections.mysql.prefix');
        $field = 'cr.*, sku.sku_name,sku.sku_image,sku.sku_images,sku.price,ci.num as item_num,
        IF(cr.type = \'order\', (select order_id from `' . $prefix . 'order_goods` og where og.order_goods_id = cr.relation_id), 0) as order_id, s.store_name';

        $join = [
            [ 'member_goods_card_item ci', 'ci.item_id = cr.card_item_id', 'left' ],
            [ 'goods_sku sku', 'ci.sku_id = sku.sku_id', 'left' ],
            [ 'store s', 'cr.store_id = s.store_id', 'left' ],
        ];

        $model = new MemberCardModel();
        $list = $model->getMemberCardRecordsPageList($condition, $field, 'cr.create_time desc', $page_index, $page_size, $alias, $join);
        return $this->response($list);
    }
}