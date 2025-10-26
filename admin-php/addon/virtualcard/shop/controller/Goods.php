<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\virtualcard\shop\controller;

use addon\form\model\Form;
use addon\supply\model\Supplier as SupplierModel;
use addon\virtualcard\model\VirtualGoods as VirtualGoodsModel;
use app\model\goods\Goods as GoodsModel;
use app\model\goods\GoodsAttribute as GoodsAttributeModel;
use app\model\goods\GoodsBrand as GoodsBrandModel;
use app\model\goods\GoodsCategory as GoodsCategoryModel;
use app\model\goods\GoodsLabel as GoodsLabelModel;
use app\model\goods\GoodsService as GoodsServiceModel;
use app\model\store\Store as StoreModel;
use app\model\web\Config as ConfigModel;
use app\shop\controller\BaseShop;
use think\App;


/**
 * 虚拟商品
 * Class Virtualgoods
 * @package app\shop\controller
 */
class Goods extends BaseShop
{

    public function __construct(App $app = null)
    {
        $this->replace = [
            'ADDON_VIRTUALCARD_CSS' => __ROOT__ . '/addon/virtualcard/shop/view/public/css',
            'ADDON_VIRTUALCARD_JS' => __ROOT__ . '/addon/virtualcard/shop/view/public/js',
            'ADDON_VIRTUALCARD_IMG' => __ROOT__ . '/addon/virtualcard/shop/view/public/img',
        ];
        parent::__construct($app);
    }

    /**
     * 添加商品
     * @return mixed
     */
    public function addGoods()
    {
        if (request()->isJson()) {

            $category_id = input('category_id', 0);// 分类id
            $category_json = json_encode($category_id);//分类字符串
            $category_id = ',' . implode(',', $category_id) . ',';

            $data = [
                'goods_name' => input('goods_name', ''),// 商品名称,
                'goods_attr_class' => input('goods_attr_class', ''),// 商品类型id,
                'goods_attr_name' => input('goods_attr_name', ''),// 商品类型名称,
                'is_limit' => input('is_limit', '0'),// 商品是否开启限购,
                'limit_type' => input('limit_type', '1'),// 限购类型,
                'site_id' => $this->site_id,
                'category_id' => $category_id,
                'category_json' => $category_json,
                'goods_image' => input('goods_image', ''),// 商品主图路径
                'goods_content' => input('goods_content', ''),// 商品详情
                'goods_state' => input('goods_state', ''),// 商品状态（1.正常0下架）
                'price' => input('price', 0),// 商品价格（取第一个sku）
                'market_price' => input('market_price', 0),// 市场价格（取第一个sku）
                'cost_price' => input('cost_price', 0),// 成本价（取第一个sku）
                'sku_no' => input('sku_no', ''),// 商品sku编码
                'weight' => input('weight', ''),// 重量
                'volume' => input('volume', ''),// 体积
                'goods_stock' => input('goods_stock', 0),// 商品库存（总和）
                'goods_stock_alarm' => input('goods_stock_alarm', 0),// 库存预警
                'goods_spec_format' => input('goods_spec_format', ''),// 商品规格格式
                'goods_attr_format' => input('goods_attr_format', ''),// 商品参数格式
                'introduction' => input('introduction', ''),// 促销语
                'keywords' => input('keywords', ''),// 关键词
                'brand_id' => input('brand_id', 0),//品牌id
                'unit' => input('unit', ''),// 单位
                'sort' => input('sort', 0),// 排序,
                'video_url' => input('video_url', ''),// 视频
                'goods_sku_data' => input('goods_sku_data', ''),// SKU商品数据
                'goods_service_ids' => input('goods_service_ids', ''),// 商品服务id集合
                'label_id' => input('label_id', ''),// 商品分组id
                'virtual_sale' => input('virtual_sale', 0),// 虚拟销量
                'max_buy' => input('max_buy', 0),// 限购
                'min_buy' => input('min_buy', 0),// 起售
                'recommend_way' => input('recommend_way', 0), // 推荐方式，1：新品，2：精品，3；推荐
                'timer_on' => strtotime(input('timer_on', 0)),//定时上架
                'timer_off' => strtotime(input('timer_off', 0)),//定时下架
                'is_consume_discount' => input('is_consume_discount', 0),//是否参与会员折扣
                'qr_id' => input('qr_id', 0),//社群二维码id
                'template_id' => input('template_id', 0),//商品海报id
                'sale_show' => input('sale_show', 0),//
                'stock_show' => input('stock_show', 0),//
                'market_price_show' => input('market_price_show', 0),//
                'barrage_show' => input('barrage_show', 0),//
                'form_id' => input('form_id', 0),
                'sale_channel' => input('sale_channel', 'all'),
                'sale_store' => input('sale_store', 'all'),
                'supplier_id' => input('supplier_id', 0)
            ];

            $virtual_goods_model = new VirtualGoodsModel();
            $res = $virtual_goods_model->addGoods($data);
            return $res;
        } else {

            //获取一级商品分类
            $goods_category_model = new GoodsCategoryModel();
            $condition = [
                [ 'pid', '=', 0 ],
                [ 'site_id', '=', $this->site_id ]
            ];

            $goods_category_list = $goods_category_model->getCategoryList($condition, 'category_id,category_name,level,commission_rate');
            $goods_category_list = $goods_category_list[ 'data' ];
            $this->assign('goods_category_list', $goods_category_list);

            //获取商品类型
            $goods_attr_model = new GoodsAttributeModel();
            $attr_class_list = $goods_attr_model->getAttrClassList([ [ 'site_id', '=', $this->site_id ] ], 'class_id,class_name')[ 'data' ];
            $this->assign('attr_class_list', $attr_class_list);

            // 商品服务
            $goods_service_model = new GoodsServiceModel();
            $service_list = $goods_service_model->getServiceList([ [ 'site_id', '=', $this->site_id ] ], 'id,service_name,icon')[ 'data' ];
            $this->assign('service_list', $service_list);

            // 商品标签
            $goods_label_model = new GoodsLabelModel();
            $label_list = $goods_label_model->getLabelList([ [ 'site_id', '=', $this->site_id ] ], 'id,label_name', 'sort ASC')[ 'data' ];
            $this->assign('label_list', $label_list);
            // 商品品牌
            $goods_brand_model = new GoodsBrandModel();
            $brand_list = $goods_brand_model->getBrandList([ [ 'site_id', '=', $this->site_id ] ], 'brand_id,brand_name', 'sort asc')[ 'data' ];
            $this->assign('brand_list', $brand_list);
            //商品默认排序值
            $config_model = new ConfigModel();
            $sort_config = $config_model->getGoodsSort($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('sort_config', $sort_config);

            //获取商品海报
            $poster_list = event('PosterTemplate', [ 'site_id' => $this->site_id ], true);
            if (!empty($poster_list)) {
                $poster_list = $poster_list[ 'data' ];
            }
            $this->assign('poster_list', $poster_list);

            $form_is_exit = addon_is_exit('form', $this->site_id);
            if ($form_is_exit) {
                $form_list = ( new Form() )->getFormList([ [ 'site_id', '=', $this->site_id ], [ 'form_type', '=', 'goods' ], [ 'is_use', '=', 1 ] ], 'id desc', 'id, form_name')[ 'data' ];
                $this->assign('form_list', $form_list);
            }
            $this->assign('form_is_exit', $form_is_exit);

            $this->assign('all_goodsclass', event('GoodsClass'));
            $this->assign('goods_class', ( new VirtualGoodsModel() )->getGoodsClass());

            $this->assign('store_is_exit', addon_is_exit('store', $this->site_id));

            $is_install_supply = addon_is_exit('supply');
            if ($is_install_supply) {
                $supplier_model = new SupplierModel();
                $supplier_list = $supplier_model->getSupplyList([ [ 'supplier_site_id', '=', $this->site_id ] ], 'supplier_id,title', 'supplier_id desc')['data'];
                $this->assign('supplier_list', $supplier_list);
            }
            $this->assign('is_install_supply', $is_install_supply);

            return $this->fetch('goods/add_goods');
        }
    }

    /**
     * 编辑商品
     * @return mixed
     */
    public function editGoods()
    {
        $virtual_goods_model = new VirtualGoodsModel();
        if (request()->isJson()) {

            $category_id = input('category_id', 0);// 分类id
            $category_json = json_encode($category_id);//分类字符串
            $category_id = ',' . implode(',', $category_id) . ',';

            $data = [
                'goods_id' => input('goods_id', 0),// 商品id
                'goods_name' => input('goods_name', ''),// 商品名称,
                'goods_attr_class' => input('goods_attr_class', ''),// 商品类型id,
                'goods_attr_name' => input('goods_attr_name', ''),// 商品类型名称,
                'is_limit' => input('is_limit', '0'),// 商品是否开启限购,
                'limit_type' => input('limit_type', '1'),// 限购类型,
                'site_id' => $this->site_id,
                'category_id' => $category_id,
                'category_json' => $category_json,
                'goods_image' => input('goods_image', ''),// 商品主图路径
                'goods_content' => input('goods_content', ''),// 商品详情
                'goods_state' => input('goods_state', ''),// 商品状态（1.正常0下架）
                'price' => input('price', 0),// 商品价格（取第一个sku）
                'market_price' => input('market_price', 0),// 市场价格（取第一个sku）
                'cost_price' => input('cost_price', 0),// 成本价（取第一个sku）
                'sku_no' => input('sku_no', ''),// 商品sku编码
                'weight' => input('weight', ''),// 重量
                'volume' => input('volume', ''),// 体积
                'goods_stock' => input('goods_stock', 0),// 商品库存（总和）
                'goods_stock_alarm' => input('goods_stock_alarm', 0),// 库存预警
                'goods_spec_format' => input('goods_spec_format', ''),// 商品规格格式
                'goods_attr_format' => input('goods_attr_format', ''),// 商品参数格式
                'introduction' => input('introduction', ''),// 促销语
                'keywords' => input('keywords', ''),// 关键词
                'unit' => input('unit', ''),// 单位
                'sort' => input('sort', 0),// 排序,
                'video_url' => input('video_url', ''),// 视频
                'goods_sku_data' => input('goods_sku_data', ''),// SKU商品数据
                'goods_service_ids' => input('goods_service_ids', ''),// 商品服务id集合
                'label_id' => input('label_id', ''),// 商品分组id
                'brand_id' => input('brand_id', 0),//品牌id
                'virtual_sale' => input('virtual_sale', 0),// 虚拟销量
                'max_buy' => input('max_buy', 0),// 限购
                'min_buy' => input('min_buy', 0),// 起售
                'recommend_way' => input('recommend_way', 0), // 推荐方式，1：新品，2：精品，3；推荐
                'timer_on' => strtotime(input('timer_on', 0)),//定时上架
                'timer_off' => strtotime(input('timer_off', 0)),//定时下架
                'spec_type_status' => input('spec_type_status', 0),
                'is_consume_discount' => input('is_consume_discount', 0),//是否参与会员折扣
                'qr_id' => input('qr_id', 0),//社群二维码id
                'template_id' => input('template_id', 0),//商品海报id
                'sale_show' => input('sale_show', 0),//
                'stock_show' => input('stock_show', 0),//
                'market_price_show' => input('market_price_show', 0),//
                'barrage_show' => input('barrage_show', 0),//
                'form_id' => input('form_id', 0),
                'sale_channel' => input('sale_channel', 'all'),
                'sale_store' => input('sale_store', 'all'),
                'supplier_id' => input('supplier_id', 0)
            ];
            $res = $virtual_goods_model->editGoods($data);
            return $res;
        } else {
            $goods_model = new GoodsModel();
            $goods_id = input('goods_id', 0);
            $goods_info = $goods_model->editGetGoodsInfo([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ])[ 'data' ];
            if (empty($goods_info)) $this->error('未获取到商品数据', href_url('shop/goods/lists'));

            $goods_sku_list = $goods_model->getGoodsSkuList([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $this->site_id ] ], 'sku_id,sku_name,sku_no,sku_spec_format,price,market_price,cost_price,stock,virtual_indate,sku_image,sku_images,goods_spec_format,spec_name,stock_alarm,is_default', '')[ 'data' ];
            $goods_info[ 'sku_list' ] = $goods_sku_list;
            $this->assign('goods_info', $goods_info);

            //获取一级商品分类
            $goods_category_model = new GoodsCategoryModel();
            $condition = [
                [ 'pid', '=', 0 ],
                [ 'site_id', '=', $this->site_id ]
            ];
            $goods_category_list = $goods_category_model->getCategoryList($condition, 'category_id,category_name,level,commission_rate')[ 'data' ];
            $this->assign('goods_category_list', $goods_category_list);

            //获取商品类型
            $goods_attr_model = new GoodsAttributeModel();
            $attr_class_list = $goods_attr_model->getAttrClassList([ [ 'site_id', '=', $this->site_id ] ], 'class_id,class_name')[ 'data' ];
            $this->assign('attr_class_list', $attr_class_list);

            // 商品服务
            $goods_service_model = new GoodsServiceModel();
            $service_list = $goods_service_model->getServiceList([ [ 'site_id', '=', $this->site_id ] ], 'id,service_name,icon')[ 'data' ];
            $this->assign('service_list', $service_list);

            // 商品标签
            $goods_label_model = new GoodsLabelModel();
            $label_list = $goods_label_model->getLabelList([ [ 'site_id', '=', $this->site_id ] ], 'id,label_name', 'sort ASC')[ 'data' ];
            $this->assign('label_list', $label_list);
            //获取品牌
            $goods_brand_model = new GoodsBrandModel();
            $brand_list = $goods_brand_model->getBrandList([ [ 'site_id', '=', $this->site_id ] ], 'brand_id, brand_name')[ 'data' ];
            $this->assign('brand_list', $brand_list);

            //获取商品海报
            $poster_list = event('PosterTemplate', [ 'site_id' => $this->site_id ], true);
            if (!empty($poster_list)) {
                $poster_list = $poster_list[ 'data' ];
            }
            $this->assign('poster_list', $poster_list);

            $form_is_exit = addon_is_exit('form', $this->site_id);
            if ($form_is_exit) {
                $form_list = ( new Form() )->getFormList([ [ 'site_id', '=', $this->site_id ], [ 'form_type', '=', 'goods' ], [ 'is_use', '=', 1 ] ], 'id desc', 'id, form_name')[ 'data' ];
                $this->assign('form_list', $form_list);
            }
            $this->assign('form_is_exit', $form_is_exit);

            $store_is_exit = addon_is_exit('store', $this->site_id);
            if ($store_is_exit && $goods_info[ 'sale_store' ] != 'all') {
                $store_list = ( new StoreModel() )->getStoreList([ [ 'site_id', '=', $this->site_id ], [ 'store_id', 'in', $goods_info[ 'sale_store' ] ] ], 'store_id,store_name,status,address,full_address,is_frozen');
                $this->assign('store_list', $store_list[ 'data' ]);
            }
            $this->assign('store_is_exit', $store_is_exit);

            $is_install_supply = addon_is_exit('supply');
            if ($is_install_supply) {
                $supplier_model = new SupplierModel();
                $supplier_list = $supplier_model->getSupplyList([ [ 'supplier_site_id', '=', $this->site_id ] ], 'supplier_id,title', 'supplier_id desc')['data'];
                $this->assign('supplier_list', $supplier_list);
            }
            $this->assign('is_install_supply', $is_install_supply);

            return $this->fetch('goods/edit_goods');
        }
    }

    /**
     * 卡密管理
     * @return array|mixed|void
     */
    public function carmichael()
    {
        $virtual_goods_model = new VirtualGoodsModel();
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $goods_id = input('goods_id', 0);
            $is_sold = input('is_sold', 0);
            $condition = [
                [ 'gv.goods_id', '=', $goods_id ],
                [ 'gv.site_id', '=', $this->site_id ]
            ];
            if ($is_sold) {
                $condition[] = [ 'gv.order_id', '<>', 0 ];
            } else {
                $condition[] = [ 'gv.order_id', '=', 0 ];
            }
            $join = [
                [ 'goods_sku gs', 'gs.sku_id = gv.sku_id', 'left' ],
                [ 'member m', 'm.member_id = gv.member_id', 'left' ],
            ];
            $field = 'gv.id,gv.sku_name,gv.card_info,gv.order_id,gv.sku_id,gv.sold_time,gs.spec_name,m.nickname, m.headimg';
            $res = $virtual_goods_model->getVirtualGoodsPageList($condition, $page, $page_size, 'id desc,sold_time desc', $field, 'gv', $join);
            return $res;
        }
        $goods_id = input('goods_id', 0);
        $this->assign('goods_id', $goods_id);

        $goods_info = $virtual_goods_model->getGoodsDetail($goods_id, $this->site_id);
        if (empty($goods_info[ 'data' ])) $this->error('未获取到商品信息');
        $this->assign('goods', $goods_info[ 'data' ]);
        $temp_condition = array (
            [ 'goods_id', '=', $goods_id ],
            [ 'site_id', '=', $this->site_id ],
            [ 'order_id', '=', 0 ]
        );
        $this->assign('stock', $virtual_goods_model->getVirtualGoodsCount($temp_condition)[ 'data' ] ?? 0);
        return $this->fetch('goods/carmichael');
    }

    /**
     * 下载卡密导入模板
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function downloadTemplate()
    {
        // 实例化excel
        $phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $phpExcel->getProperties()->setTitle('卡密数据导入模板');
        $phpExcel->getProperties()->setSubject('卡密数据导入模板');
        // 对单元格设置居中效果
        $phpExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $phpExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        //单独添加列名称
        $phpExcel->setActiveSheetIndex(0);
        $phpExcel->getActiveSheet()->setCellValue('A1', '卡号');//可以指定位置
        $phpExcel->getActiveSheet()->setCellValue('B1', '密码');

        // 设置第一个sheet为工作的sheet
        $phpExcel->setActiveSheetIndex(0);
        // 保存Excel 2007格式文件，保存路径为当前路径，名字为export.xlsx
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($phpExcel, 'Xlsx');
        $file = date('卡密数据导入模板', time()) . '.xlsx';
        $objWriter->save($file);

        header('Content-type:application/octet-stream');

        $filename = basename($file);
        header('Content-Disposition:attachment;filename = ' . $filename);
        header('Accept-ranges:bytes');
        header('Accept-length:' . filesize($file));
        readfile($file);
        unlink($file);
        exit;
    }

    /**
     * 添加卡密
     * @return array
     */
    public function addCarmichael()
    {
        if (request()->isJson()) {
            $virtual_goods_model = new VirtualGoodsModel();
            $goods_id = input('goods_id', 0);
            $sku_id = input('sku_id', 0);
            $data = input('data', '');
            $carmichael = explode(',', $data);
            $res = $virtual_goods_model->addGoodsVirtual($this->site_id, $goods_id, $sku_id, $carmichael);
            return $res;
        }
    }

    /**
     * 导入数据
     */
    public function import()
    {
        if (request()->isJson()) {
            $virtual_goods_model = new VirtualGoodsModel();
            $path = input('path', '');
            $res = $virtual_goods_model->importData($path);
            return $res;
        }
    }

    /**
     * 删除卡密
     */
    public function deleteGoodsVirtual()
    {
        if (request()->isJson()) {
            $ids = input('id', '');
            $goods_id = input('goods_id', '');
            $virtual_goods_model = new VirtualGoodsModel();
            $res = $virtual_goods_model->deleteGoodsVirtual([ [ 'order_id', '=', 0 ], [ 'id', 'in', $ids ], [ 'site_id', '=', $this->site_id ], [ 'goods_id', '=', $goods_id ] ]);
            return $res;
        }
    }

    /**
     * 编辑卡密
     * @return array
     */
    public function editGoodsVirtual()
    {
        if (request()->isJson()) {
            $virtual_goods_model = new VirtualGoodsModel();
            $id = input('id', '');
            $card_info = [
                'cardno' => input('cardno', ''),
                'password' => input('password', '')
            ];
            $res = $virtual_goods_model->updateGoodsVirtual([ 'card_info' => json_encode($card_info) ], [ [ 'id', '=', $id ], [ 'site_id', '=', $this->site_id ], [ 'order_id', '=', 0 ] ]);
            return $res;
        }
    }
}