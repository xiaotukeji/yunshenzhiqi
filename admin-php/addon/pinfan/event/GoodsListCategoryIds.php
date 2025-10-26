<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */


namespace addon\pinfan\event;

use addon\pinfan\model\Pinfan;

/**
 * 商品分类
 */
class GoodsListCategoryIds
{
	public function handle($param)
	{
		if (empty($param[ 'promotion' ]) || $param[ 'promotion' ] != 'pinfan') return [];

		$condition[] = [
			['pp.site_id', '=', $param[ 'site_id' ]],
			['pp.status', '=', 1],
            ['g.is_delete','=',0],
            ['g.goods_state','=',1],
		];

		$model = new Pinfan();
		$list = $model->getGoodsCategoryIds($condition);
		return $list;
	}
}