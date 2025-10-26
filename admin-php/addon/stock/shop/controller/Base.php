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

namespace addon\stock\shop\controller;

use addon\stock\dict\StockDict;
use addon\stock\model\stock\Allot;
use addon\stock\model\stock\Document;
use addon\stock\model\stock\Export;
use addon\stock\model\stock\Import;
use addon\stock\model\stock\Inventory;
use addon\stock\model\stock\Stock as StockModel;
use addon\stock\model\Store;
use app\dict\goods\GoodsDict;
use app\model\goods\GoodsCategory;
use app\model\goods\GoodsCategory as GoodsCategoryModel;
use app\model\goods\GoodsLabel;
use app\model\upload\Upload as UploadModel;
use app\shop\controller\BaseShop;
use app\model\goods\Goods;
use think\App;

/**
 * 库存管理
 * Class Stock
 * @package addon\stock\shop\controller
 */
class Base extends BaseShop
{
    public function __construct(App $app = null)
    {
        $this->replace = [
            'ADDON_STOCK_CSS' => __ROOT__ . '/addon/stock/shop/view/public/css',
            'ADDON_STOCK_JS' => __ROOT__ . '/addon/stock/shop/view/public/js',
            'ADDON_STOCK_IMG' => __ROOT__ . '/addon/stock/shop/view/public/img',
            'ADDON_STOCK_FILE' => __ROOT__ . '/addon/stock/shop/view/public/file',
        ];
        parent::__construct($app);
    }
}