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

use app\api\controller\BaseApi;
use addon\fenxiao\model\Config as ConfigModel;
use app\model\goods\Goods;
use app\model\system\Document;

/**
 * 分销相关配置
 */
class Config extends BaseApi
{

    /**
     * 提现配置
     */
    public function withdraw()
    {
        $config = new ConfigModel();
        $res = $config->getFenxiaoWithdrawConfig($this->site_id);
        return $this->response($this->success($res[ 'data' ][ 'value' ]));
    }

    /**
     * 文字设置
     * @return false|string
     */
    public function words()
    {
        $config = new ConfigModel();
        $res = $config->getFenxiaoWordsConfig($this->site_id);
        return $this->response($this->success($res[ 'data' ][ 'value' ]));
    }

    /**
     * 申请协议
     * @return false|string
     */
    public function agreement()
    {
        $config = new ConfigModel();
        $agreement = $config->getFenxiaoAgreementConfig($this->site_id);
        $res = [];
        $res[ 'agreement' ] = $agreement[ 'data' ][ 'value' ];
        if ($agreement[ 'data' ][ 'value' ][ 'is_agreement' ] == 1) {
            $document_model = new Document();
            $document = $document_model->getDocument([ [ 'site_id', '=', $this->site_id ], [ 'app_module', '=', 'shop' ], [ 'document_key', '=', 'FENXIAO_AGREEMENT'] ]);
            $res[ 'document' ] = $document[ 'data' ];
        }

        return $this->response($this->success($res));
    }

    /**
     * 分销基本设置
     * @return false|string
     */
    public function basics()
    {
        $config = new ConfigModel();
        $res = $config->getFenxiaoBasicsConfig($this->site_id);
        return $this->response($this->success($res[ 'data' ][ 'value' ]));
    }

    /**
     * 分销商资格设置
     * @return false|string
     */
    public function fenxiao()
    {
        $config = new ConfigModel();
        $res = $config->getFenxiaoConfig($this->site_id);
        $res[ 'data' ][ 'value' ][ 'goods_list' ] = [];
        // 购买指定商品
        if ($res[ 'data' ][ 'value' ][ 'fenxiao_condition' ] == 4) {
            $page = $this->params[ 'page' ] ?? 1;
            $page_size = $this->params[ 'page_size' ] ?? 10;
            $condition[] = [ 'gs.goods_state', '=', 1 ];
            $condition[] = [ 'gs.is_delete', '=', 0 ];
            $condition[] = [ 'gs.site_id', '=', $this->site_id ];
            $condition[] = [ 'gs.goods_id', 'in', $res[ 'data' ][ 'value' ][ 'goods_ids' ] ];

            $field = 'gs.goods_id,gs.sku_id,gs.sku_name,gs.price,gs.market_price,gs.discount_price,gs.stock,(g.sale_num + g.virtual_sale) as sale_num,gs.sku_image,gs.goods_name,gs.site_id,gs.is_free_shipping,gs.introduction,gs.promotion_type,g.goods_image,gs.unit';
            $alias = 'gs';
            $join = [
                [ 'goods g', 'gs.sku_id = g.sku_id', 'inner' ]
            ];
            $goods = new Goods();
            $list = $goods->getGoodsSkuPageList($condition, $page, $page_size, '', $field, $alias, $join);
            $res[ 'data' ][ 'value' ][ 'goods_list' ] = $list[ 'data' ][ 'list' ];
        }

        return $this->response($this->success($res[ 'data' ][ 'value' ]));
    }

    /**
     * 获取上下级关系设置
     * @return false|string
     */
    public function relation()
    {
        $config = new ConfigModel();
        $res = $config->getFenxiaoRelationConfig($this->site_id);
        return $this->response($this->success($res[ 'data' ][ 'value' ]));
    }

    /**
     * 推广规则
     * @return false|string
     */
    public function promoteRule()
    {
        $document_model = new Document();
        $document = $document_model->getDocument([ [ 'site_id', '=', $this->site_id ], [ 'app_module', '=', 'shop' ], [ 'document_key', '=', 'FENXIAO_PROMOTE_RULE'] ]);
        return $this->response($document);
    }
}