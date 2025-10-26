<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\virtualcard\model;

use addon\discount\model\Discount;
use app\model\BaseModel;
use app\model\order\OrderCommon;
use app\model\stock\GoodsStock;
use app\model\storegoods\StoreGoods;
use app\model\system\Cron;
use app\model\system\Stat;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use app\model\goods\GoodsCommon;

/**
 * 虚拟商品
 */
class VirtualGoods extends GoodsCommon
{
    private $goods_class = ['id' => 3, 'name' => '电子卡密'];

    private $goods_state = [
        1 => '销售中',
        0 => '仓库中'
    ];

    public function getGoodsState()
    {
        return $this->goods_state;
    }

    public function getGoodsClass()
    {
        return $this->goods_class;
    }

    /**
     * 商品添加
     * @param $data
     */
    public function addGoods($data)
    {
        model('goods')->startTrans();

        try {

            if (!empty($data[ 'goods_attr_format' ])) {

                $goods_attr_format = json_decode($data[ 'goods_attr_format' ], true);
                $keys = array_column($goods_attr_format, 'sort');
                if (!empty($keys)) {
                    array_multisort($keys, SORT_ASC, SORT_NUMERIC, $goods_attr_format);
                    $data[ 'goods_attr_format' ] = json_encode($goods_attr_format);
                }
            }

            $goods_image = $data[ 'goods_image' ];
            $first_image = explode(',', $goods_image)[ 0 ];

            //SKU商品数据
            if (!empty($data[ 'goods_sku_data' ])) {
                $data[ 'goods_sku_data' ] = json_decode($data[ 'goods_sku_data' ], true);
            }

            //商品编码检测
            $sku_no_check = $this->checkSkuNoRepeat(['sku_list' => $data['goods_sku_data'], 'site_id' => $data['site_id'], 'goods_id' => $data['goods_id'] ?? 0]);
            if($sku_no_check['code'] < 0){
                model('goods')->rollback();
                return $sku_no_check;
            }

            //获取标签名称
            $label_name = '';
            if ($data[ 'label_id' ]) {
                $label_info = model('goods_label')->getInfo([ [ 'id', '=', $data[ 'label_id' ] ] ], 'label_name');
                $label_name = $label_info[ 'label_name' ] ?? '';
            }
            $brand_name = '';
            if ($data[ 'brand_id' ]) {
                $brand_info = model('goods_brand')->getInfo([ [ 'brand_id', '=', $data[ 'brand_id' ] ] ], 'brand_name');
                $brand_name = $brand_info[ 'brand_name' ] ?? '';
            }
            $goods_data = [
                'goods_image' => $goods_image,
                'price' => $data[ 'goods_sku_data' ][ 0 ][ 'price' ],
                'market_price' => $data[ 'goods_sku_data' ][ 0 ][ 'market_price' ],
                'cost_price' => $data[ 'goods_sku_data' ][ 0 ][ 'cost_price' ],
                'goods_spec_format' => $data[ 'goods_spec_format' ],
                'category_id' => $data[ 'category_id' ],
                'category_json' => $data[ 'category_json' ],
                'label_id' => $data[ 'label_id' ],
                'label_name' => $label_name,
                'timer_on' => $data[ 'timer_on' ],
                'timer_off' => $data[ 'timer_off' ],
                'sale_show' => $data[ 'sale_show' ] ?? 1,
                'stock_show' => $data[ 'stock_show' ] ?? 1,
                'market_price_show' => $data[ 'market_price_show' ] ?? 1,
                'barrage_show' => $data[ 'barrage_show' ] ?? 1,
                'is_consume_discount' => $data[ 'is_consume_discount' ],
            ];

            $common_data = [
                'goods_name' => $data[ 'goods_name' ],
                'goods_class' => $this->goods_class[ 'id' ],
                'goods_class_name' => $this->goods_class[ 'name' ],
                'goods_attr_class' => $data[ 'goods_attr_class' ],
                'goods_attr_name' => $data[ 'goods_attr_name' ],
                'is_limit' => $data[ 'is_limit' ] ?? 0,
                'limit_type' => $data[ 'limit_type' ] ?? 1,
                'site_id' => $data[ 'site_id' ],
                'goods_content' => $data[ 'goods_content' ],
                'goods_state' => $data[ 'goods_state' ],
                'goods_stock_alarm' => $data[ 'goods_stock_alarm' ],
                'is_virtual' => 1,
                'virtual_indate' => 0,
                'goods_attr_format' => $data[ 'goods_attr_format' ],
                'introduction' => $data[ 'introduction' ],
                'keywords' => $data[ 'keywords' ],
                'unit' => $data[ 'unit' ],
                'brand_id' => $data[ 'brand_id' ],//品牌id
                'brand_name' => $brand_name,//品牌名称
                'video_url' => $data[ 'video_url' ],
                'sort' => $data[ 'sort' ],
                'goods_service_ids' => $data[ 'goods_service_ids' ],
                'create_time' => time(),
                'virtual_sale' => $data[ 'virtual_sale' ],
                'max_buy' => $data[ 'max_buy' ],
                'min_buy' => $data[ 'min_buy' ],
                'recommend_way' => $data[ 'recommend_way' ],
                'qr_id' => $data[ 'qr_id' ] ?? 0,
                'template_id' => $data[ 'template_id' ] ?? 0,
                'form_id' => $data[ 'form_id' ] ?? 0,
                'sale_channel' => $data[ 'sale_channel' ] ?? 'all',
                'sale_store' => $data[ 'sale_store' ] ?? 'all',
                'supplier_id' => $data[ 'supplier_id' ] ?? 0
            ];

            $goods_id = model('goods')->add(array_merge($goods_data, $common_data));

            $goods_stock = 0;

            //添加sku商品
            foreach ($data[ 'goods_sku_data' ] as $item) {
//                $carmichael = 0;
//                if (isset($item[ 'carmichael' ])) {
//                    if (is_array($item[ 'carmichael' ])) {
//                        $carmichael = count($item[ 'carmichael' ]);
//                    }
//                }
                $sku_data = [
                    'sku_name' => $data[ 'goods_name' ] . ' ' . $item[ 'spec_name' ],
                    'spec_name' => $item[ 'spec_name' ],
                    'sku_no' => $item[ 'sku_no' ],
                    'sku_spec_format' => !empty($item[ 'sku_spec_format' ]) ? json_encode($item[ 'sku_spec_format' ]) : '',
                    'price' => $item[ 'price' ],
                    'market_price' => $item[ 'market_price' ],
                    'cost_price' => $item[ 'cost_price' ],
                    'discount_price' => $item[ 'price' ],//sku折扣价（默认等于单价）
//                    'stock' => $carmichael,
                    'stock_alarm' => $item[ 'stock_alarm' ],
                    'sku_image' => !empty($item[ 'sku_image' ]) ? $item[ 'sku_image' ] : $first_image,
                    'sku_images' => $item[ 'sku_images' ],
                    'goods_id' => $goods_id,
                    'is_default' => $item[ 'is_default' ] ?? 0,
                    'is_consume_discount' => $data[ 'is_consume_discount' ],
                    'site_id' => $data[ 'site_id' ]
                ];
                $sku_data = array_merge($sku_data, $common_data);
                $sku_id = model('goods_sku')->add($sku_data);

                // 添加到虚拟商品表
                if (isset($item[ 'carmichael' ])) {
                    if (is_array($item[ 'carmichael' ]) && count($item[ 'carmichael' ])) {
                        $goods_stock += count($item[ 'carmichael' ]);
                        $virtual_goods = [];
                        foreach ($item[ 'carmichael' ] as $carmichael_item) {
                            $carmichael_item = htmlspecialchars(addslashes($carmichael_item));
                            $card = explode(' ', $carmichael_item);
                            $card_arr = [
                                'cardno' => $card[ 0 ] ?? '',
                                'password' => $card[ 1 ] ?? ''
                            ];
                            $virtual_goods[] = [
                                'site_id' => $data[ 'site_id' ],
                                'sku_id' => $sku_id,
                                'sku_name' => $data[ 'goods_name' ] . ' ' . $item[ 'spec_name' ],
                                'card_info' => json_encode($card_arr),
                                'goods_id' => $goods_id
                            ];
                        }
                        model('goods_virtual')->addList($virtual_goods);
                    }
                }
            }

            // 赋值第一个商品sku_id
            $first_info = model('goods_sku')->getFirstData([ 'goods_id' => $goods_id ], 'sku_id', 'is_default desc,sku_id asc');
            model('goods')->update([ 'sku_id' => $first_info[ 'sku_id' ] ], [ [ 'goods_id', '=', $goods_id ] ]);

            if (!empty($data[ 'goods_spec_format' ])) {
                // 刷新SKU商品规格项 / 规格值JSON字符串
                $this->dealGoodsSkuSpecFormat($goods_id, $data[ 'goods_spec_format' ]);
            }

            $cron = new Cron();
            //定时上下架
            if ($goods_data[ 'timer_on' ] > 0) {
                $cron->addCron(1, 0, '商品定时上架', 'CronGoodsTimerOn', $goods_data[ 'timer_on' ], $goods_id);
            }
            if ($goods_data[ 'timer_off' ] > 0) {
                $cron->addCron(1, 0, '商品定时下架', 'CronGoodsTimerOff', $goods_data[ 'timer_off' ], $goods_id);
            }

            //添加统计
            $stat = new Stat();
//            $stat->addShopStat([ 'add_goods_count' => 1, 'site_id' => $data[ 'site_id' ] ]);
            $stat->switchStat([ 'type' => 'add_goods', 'data' => [ 'add_goods_count' => 1, 'site_id' => $data[ 'site_id' ] ] ]);
            $stat->switchStat([ 'type' => 'goods_on', 'data' => [ 'site_id' => $data[ 'site_id' ] ] ]);

            //同步计算卡密库存数据
            $this->statisticsStock($goods_id);
            model('goods')->commit();

            return $this->success($goods_id);
        } catch ( Exception $e) {
            model('goods')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 商品编辑
     * @param $data
     */
    public function editGoods($data)
    {

        model('goods')->startTrans();

        try {

            if (!empty($data[ 'goods_attr_format' ])) {

                $goods_attr_format = json_decode($data[ 'goods_attr_format' ], true);
                $keys = array_column($goods_attr_format, 'sort');
                if (!empty($keys)) {
                    array_multisort($keys, SORT_ASC, SORT_NUMERIC, $goods_attr_format);
                    $data[ 'goods_attr_format' ] = json_encode($goods_attr_format);
                }
            }

            $goods_id = $data[ 'goods_id' ];
            $goods_image = $data[ 'goods_image' ];
            $first_image = explode(',', $goods_image)[ 0 ];

            //SKU商品数据
            if (!empty($data[ 'goods_sku_data' ])) {
                $data[ 'goods_sku_data' ] = json_decode($data[ 'goods_sku_data' ], true);
            }

            //商品编码检测
            $sku_no_check = $this->checkSkuNoRepeat(['sku_list' => $data['goods_sku_data'], 'site_id' => $data['site_id'], 'goods_id' => $data['goods_id'] ?? 0]);
            if($sku_no_check['code'] < 0){
                model('goods')->rollback();
                return $sku_no_check;
            }

            //获取标签名称
            $label_name = '';
            if ($data[ 'label_id' ]) {
                $label_info = model('goods_label')->getInfo([ [ 'id', '=', $data[ 'label_id' ] ] ], 'label_name');
                $label_name = $label_info[ 'label_name' ] ?? '';
            }
            $brand_name = '';
            if ($data[ 'brand_id' ]) {
                $brand_info = model('goods_brand')->getInfo([ [ 'brand_id', '=', $data[ 'brand_id' ] ] ], 'brand_name');
                $brand_name = $brand_info[ 'brand_name' ] ?? '';
            }
            $goods_data = [
                'goods_image' => $goods_image,
                'price' => $data[ 'goods_sku_data' ][ 0 ][ 'price' ],
                'market_price' => $data[ 'goods_sku_data' ][ 0 ][ 'market_price' ],
                'cost_price' => $data[ 'goods_sku_data' ][ 0 ][ 'cost_price' ],
                'goods_spec_format' => $data[ 'goods_spec_format' ],
                'category_id' => $data[ 'category_id' ],
                'category_json' => $data[ 'category_json' ],
                'label_id' => $data[ 'label_id' ],
                'label_name' => $label_name,
                'timer_on' => $data[ 'timer_on' ],
                'timer_off' => $data[ 'timer_off' ],
                'sale_show' => $data[ 'sale_show' ],
                'stock_show' => $data[ 'stock_show' ],
                'market_price_show' => $data[ 'market_price_show' ],
                'barrage_show' => $data[ 'barrage_show' ],
                'is_consume_discount' => $data[ 'is_consume_discount' ],
            ];

            $common_data = [
                'goods_name' => $data[ 'goods_name' ],
                'goods_class' => $this->goods_class[ 'id' ],
                'goods_class_name' => $this->goods_class[ 'name' ],
                'goods_attr_class' => $data[ 'goods_attr_class' ],
                'goods_attr_name' => $data[ 'goods_attr_name' ],
                'is_limit' => $data[ 'is_limit' ] ?? 0,
                'limit_type' => $data[ 'limit_type' ] ?? 1,
                'site_id' => $data[ 'site_id' ],
                'goods_content' => $data[ 'goods_content' ],
                'goods_state' => $data[ 'goods_state' ],
                'goods_stock_alarm' => $data[ 'goods_stock_alarm' ],
                'is_virtual' => 1,
                'virtual_indate' => 0,
                'goods_attr_format' => $data[ 'goods_attr_format' ],
                'introduction' => $data[ 'introduction' ],
                'keywords' => $data[ 'keywords' ],
                'unit' => $data[ 'unit' ],
                'video_url' => $data[ 'video_url' ],
                'sort' => $data[ 'sort' ],
                'goods_service_ids' => $data[ 'goods_service_ids' ],
                'brand_id' => $data[ 'brand_id' ],//品牌id
                'brand_name' => $brand_name,//品牌名称
                'modify_time' => time(),
                'virtual_sale' => $data[ 'virtual_sale' ],
                'max_buy' => $data[ 'max_buy' ],
                'min_buy' => $data[ 'min_buy' ],
                'recommend_way' => $data[ 'recommend_way' ],
                'qr_id' => $data[ 'qr_id' ] ?? 0,
                'template_id' => $data[ 'template_id' ] ?? 0,
                'form_id' => $data[ 'form_id' ] ?? 0,
                'sale_channel' => $data[ 'sale_channel' ] ?? 'all',
                'sale_store' => $data[ 'sale_store' ] ?? 'all',
                'supplier_id' => $data[ 'supplier_id' ] ?? 0
            ];

            model('goods')->update(array_merge($goods_data, $common_data), [ [ 'goods_id', '=', $goods_id ], [ 'goods_class', '=', $this->goods_class[ 'id' ] ] ]);

            $goods_stock = 0;
            $is_off_store_goods = 0; // 是否下架门店商品

            $discount_model = new Discount();
            $sku_id_arr = [];
            foreach ($data[ 'goods_sku_data' ] as $item) {
                $discount_info = [];
                if (!empty($item[ 'sku_id' ])) {
                    $discount_info_result = $discount_model->getDiscountGoodsInfo([ [ 'pdg.sku_id', '=', $item[ 'sku_id' ] ], [ 'pd.status', '=', 1 ] ], 'id');
                    $discount_info = $discount_info_result[ 'data' ];
                }

                $carmichael = 0;
                if (isset($item[ 'carmichael' ])) {
                    if (is_array($item[ 'carmichael' ])) {
                        $carmichael = count($item[ 'carmichael' ]);
                    }
                }

                $sku_data = [
                    'sku_name' => $data[ 'goods_name' ] . ' ' . $item[ 'spec_name' ],
                    'spec_name' => $item[ 'spec_name' ],
                    'sku_no' => $item[ 'sku_no' ],
                    'sku_spec_format' => !empty($item[ 'sku_spec_format' ]) ? json_encode($item[ 'sku_spec_format' ]) : '',
                    'goods_spec_format' => '',
                    'price' => $item[ 'price' ],
                    'market_price' => $item[ 'market_price' ],
                    'cost_price' => $item[ 'cost_price' ],
//                            'stock' => $carmichael,
                    'stock_alarm' => $item[ 'stock_alarm' ],
                    'sku_image' => !empty($item[ 'sku_image' ]) ? $item[ 'sku_image' ] : $first_image,
                    'sku_images' => $item[ 'sku_images' ],
                    'goods_id' => $goods_id,
                    'is_default' => $item[ 'is_default' ] ?? 0,
                    'is_consume_discount' => $data[ 'is_consume_discount' ]
                ];
                if (empty($discount_info)) {
                    $sku_data[ 'discount_price' ] = $item[ 'price' ];
                }
                if (!empty($item[ 'sku_id' ])) {
                    $sku_id = $item[ 'sku_id' ];
                    $sku_id_arr[] = $item[ 'sku_id' ];
                    model('goods_sku')->update(array_merge($sku_data, $common_data), [ [ 'sku_id', '=', $item[ 'sku_id' ] ], [ 'goods_class', '=', $this->goods_class[ 'id' ] ] ]);
                } else {
                    $sku_id = model('goods_sku')->add(array_merge($sku_data, $common_data));
                    $sku_id_arr[] = $sku_id;
                }

                // 添加到虚拟商品表
                if (isset($item[ 'carmichael' ])) {
                    if (is_array($item[ 'carmichael' ]) && count($item[ 'carmichael' ])) {
                        $goods_stock += count($item[ 'carmichael' ]);
                        $virtual_goods = [];
                        foreach ($item[ 'carmichael' ] as $carmichael_item) {
                            $carmichael_item = htmlspecialchars(addslashes($carmichael_item));
                            $card = explode(' ', $carmichael_item);
                            $card_arr = [
                                'cardno' => $card[ 0 ] ?? '',
                                'password' => $card[ 1 ] ?? ''
                            ];
                            $virtual_goods[] = [
                                'site_id' => $data[ 'site_id' ],
                                'sku_id' => $sku_id,
                                'sku_name' => $data[ 'goods_name' ] . ' ' . $item[ 'spec_name' ],
                                'card_info' => json_encode($card_arr),
                                'goods_id' => $goods_id
                            ];
                        }
                        model('goods_virtual')->addList($virtual_goods);
                    }
                }
            }

            // 移除不存在的商品SKU
            $sku_id_list = model('goods_sku')->getList([ [ 'goods_id', '=', $goods_id ] ], 'sku_id');
            $sku_id_list = array_column($sku_id_list, 'sku_id');
            foreach ($sku_id_list as $k => $v) {
                foreach ($sku_id_arr as $ck => $cv) {
                    if ($v == $cv) {
                        unset($sku_id_list[ $k ]);
                    }
                }
            }

            $sku_id_list = array_values($sku_id_list);
            if (!empty($sku_id_list)) {
                $check = $this->deleteGoodsSkuCheck($sku_id_list);
                if ($check[ 'code' ] < 0) {
                    model('goods')->rollback();
                    return $check;
                }
                $is_off_store_goods = 1;
                model('goods_sku')->delete([ [ 'sku_id', 'in', implode(',', $sku_id_list) ] ]);
            }

            // 赋值第一个商品sku_id
            $first_info = model('goods_sku')->getFirstData([ 'goods_id' => $goods_id ], 'sku_id', 'is_default desc,sku_id asc');
            model('goods')->update([ 'sku_id' => $first_info[ 'sku_id' ] ], [ [ 'goods_id', '=', $goods_id ] ]);

            if (!empty($data[ 'goods_spec_format' ])) {
                // 刷新SKU商品规格项 / 规格值JSON字符串
                $this->dealGoodsSkuSpecFormat($goods_id, $data[ 'goods_spec_format' ]);
            }

            if($is_off_store_goods == 1) {
                (new StoreGoods())->modifyStoreGoodsState($goods_id, 0);
            }

            $cron = new Cron();
            $cron->deleteCron([ [ 'event', '=', 'CronGoodsTimerOn' ], [ 'relate_id', '=', $goods_id ] ]);
            $cron->deleteCron([ [ 'event', '=', 'CronGoodsTimerOff' ], [ 'relate_id', '=', $goods_id ] ]);
            //定时上下架
            if ($goods_data[ 'timer_on' ] > 0) {
                $cron->addCron(1, 0, '商品定时上架', 'CronGoodsTimerOn', $goods_data[ 'timer_on' ], $goods_id);
            }
            if ($goods_data[ 'timer_off' ] > 0) {
                $cron->addCron(1, 0, '商品定时下架', 'CronGoodsTimerOff', $goods_data[ 'timer_off' ], $goods_id);
            }

            $this->statisticsStock($goods_id);
            //添加统计
            $stat = new Stat();
            $stat->switchStat([ 'type' => 'goods_on', 'data' => [ 'site_id' => $data[ 'site_id' ] ] ]);
            model('goods')->commit();
            return $this->success($goods_id);
        } catch ( Exception $e) {
            model('goods')->rollback();
            return $this->error($e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

    /**
     * 获取商品详情
     * @param $goods_id
     * @return array
     */
    public function getGoodsDetail($goods_id, $site_id)
    {
        $info = model('goods')->getInfo([ [ 'is_delete', '=', 0 ], [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $site_id ], [ 'goods_class', '=', 3 ] ], '*');
        $field = 'sku_id, sku_name,spec_name, sku_no, sku_spec_format, price, market_price, cost_price, discount_price, stock,
                  weight, volume,  sku_image, sku_images, sort,member_price,fenxiao_price';
        $sku_data = model('goods_sku')->getList([ [ 'goods_id', '=', $goods_id ] ], $field);

        if (!empty($sku_data)) {
            foreach ($sku_data as $k => $v) {
                $sku_data[ $k ][ 'member_price' ] = $v[ 'member_price' ] == '' ? '' : json_decode($v[ 'member_price' ], true);
                $sku_data[ $k ][ 'stock' ] = numberFormat($sku_data[ $k ][ 'stock' ]);
            }
        }
        if (!empty($info)) {
            if (isset($info[ 'goods_stock' ])) {
                $info[ 'goods_stock' ] = numberFormat($info[ 'goods_stock' ]);
            }
            if (isset($info[ 'sale_num' ])) {
                $info[ 'sale_num' ] = numberFormat($info[ 'sale_num' ]);
            }
            if (isset($info[ 'virtual_sale' ])) {
                $info[ 'virtual_sale' ] = numberFormat($info[ 'virtual_sale' ]);
            }
            if (isset($info[ 'real_stock' ])) {
                $info[ 'real_stock' ] = numberFormat($info[ 'real_stock' ]);
            }
            $info[ 'sku_data' ] = $sku_data;
        }
        return $this->success($info);
    }

    /************************************************************************* 购买的虚拟产品 start *******************************************************************/
    /**
     * 生成购买的虚拟产品
     * @param $site_id
     * @param $order_id
     * @param $order_no
     * @param $sku_id
     * @param $sku_name
     * @param $code
     * @param $member_id
     * @param $sku_image
     */
    public function addGoodsVirtual($site_id, $goods_id, $sku_id, $data)
    {
        if (is_array($data) && count($data)) {
            $virtual_goods = [];
            foreach ($data as $carmichael_item) {
                $carmichael_item = htmlspecialchars(addslashes($carmichael_item));
                $card = explode(' ', $carmichael_item);
                $card_arr = [
                    'cardno' => $card[ 0 ] ?? '',
                    'password' => $card[ 1 ] ?? ''
                ];
                $virtual_goods[] = [
                    'site_id' => $site_id,
                    'sku_id' => $sku_id,
                    'card_info' => json_encode($card_arr),
                    'goods_id' => $goods_id
                ];
            }
            model('goods_virtual')->startTrans();
            try {
                $res = model('goods_virtual')->addList($virtual_goods);
//                model('goods')->setInc([ [ 'goods_id', '=', $goods_id ], [ 'site_id', '=', $site_id ] ], 'goods_stock', count($virtual_goods)); // 增加商品总库存
//                model('goods_sku')->setInc([ [ 'sku_id', '=', $sku_id ], [ 'site_id', '=', $site_id ] ], 'stock', count($virtual_goods)); // 增加sku库存
                //同步计算卡密库存数据
                $this->statisticsStock($goods_id);
                model('goods_virtual')->commit();
                return $this->success($res);
            } catch ( Exception $e) {
                model('goods_virtual')->rollback();
                return $this->error('', $e->getMessage());
            }
        } else {
            return $this->error('', '请输入要添加的卡密数据');
        }
    }

    /**
     * 编辑卡密数据
     * @param $data
     * @param $condition
     * @return array
     */
    public function updateGoodsVirtual($data, $condition)
    {
        $res = model('goods_virtual')->update($data, $condition);
        return $this->success();
    }

    /**
     * 删除
     * @param $condition
     */
    public function deleteGoodsVirtual($condition)
    {
        $res = model('goods_virtual')->delete($condition);
        if ($res === false)
            return $this->error();

        $check_condition = array_column($condition, 2, 0);
        $goods_id = $check_condition[ 'goods_id' ] ?? 0;
        if ($goods_id) $this->statisticsStock($goods_id);

        return $this->success();
    }


    /**
     * 虚拟商品详情
     * @param $condition
     */
    public function getVirtualGoodsInfo($condition, $field = '*')
    {
        $info = model('goods_virtual')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取虚拟商品分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getVirtualGoodsPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'id desc', $field = '*', $alias = 'a', $join = null)
    {
        $list = model('goods_virtual')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

    /**
     * 卡券数量
     * @param $condition
     */
    public function getVirtualGoodsCount($condition, $field = '*')
    {
        $count = model('goods_virtual')->getCount($condition, $field);
        return $this->success($count);
    }
    /************************************************************************* 购买的虚拟产品 end *******************************************************************/

    /**
     * 从excel中读取卡密数据
     * @param $path
     */
    public function importData($path)
    {
        $PHPReader = IOFactory::createReader('Xlsx');
        //载入文件
        $PHPExcel = $PHPReader->load($path);

        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet = $PHPExcel->getSheet(0);

        //获取总行数
        $allRow = $currentSheet->getHighestRow();

        if ($allRow < 2) {
            return $this->error('', '导入了一个空文件');
        }
        $data = [];
        for ($i = 2; $i <= $allRow; $i++) {
            //卡号
            $cardno = $PHPExcel->getActiveSheet()->getCell('A' . $i)->getValue();
            //卡号
            $password = $PHPExcel->getActiveSheet()->getCell('B' . $i)->getValue();
            $data[] = $cardno . ' ' . $password;
        }
        return $this->success(implode("\n", $data));
    }

    /**
     * 重新统计库存
     * @param $goods_id
     */
    public function statisticsStock($goods_id)
    {
        try {
            model('goods_sku')->startTrans();
            $goods_sku_list = model('goods_sku')->getList([ [ 'goods_id', '=', $goods_id ] ], 'sku_id,goods_class,site_id');
            $goods_sku_ids = array_column($goods_sku_list, 'sku_id');
            // 先删除已不存在的sku
            model('goods_virtual')->delete([ [ 'order_id', '=', 0 ], [ 'goods_id', '=', $goods_id ], [ 'sku_id', 'not in', $goods_sku_ids ] ]);
            // 重新统计sku的库存

            $site_id = 0;
            $stock_sku_list = [];
            foreach ($goods_sku_list as $v) {
                $site_id = $v[ 'site_id' ];
                $sku_id = $v[ 'sku_id' ];
                $sku_stock = model('goods_virtual')->getCount([ [ 'order_id', '=', 0 ], [ 'goods_id', '=', $goods_id ], [ 'sku_id', '=', $sku_id ] ]);
                $stock_sku_list[] = [ 'sku_id' => $sku_id, 'stock' => $sku_stock, 'goods_class' => $v[ 'goods_class' ] ];
            }
            //设置库存
            $goods_stock_model = new GoodsStock();
            $goods_stock_model->changeGoodsStock([
                'site_id' => $site_id,
                'goods_sku_list' => $stock_sku_list
            ]);
            model('goods_sku')->commit();
        } catch ( Exception $e) {
            model('goods_sku')->rollback();
        }
    }

    /**
     * 商品导入
     * @param $goods_data
     * @param $site_id
     * @return array
     */
    public function importGoods($goods_data, $site_id)
    {
        try {
            if (empty($goods_data[ 'goods_name' ])) return $this->error('', '商品名称不能为空');
            if (empty($goods_data[ 'goods_image' ])) return $this->error('', '商品主图不能为空');
            if (empty($goods_data[ 'category_1' ]) && empty($goods_data[ 'category_2' ]) && empty($goods_data[ 'category_3' ])) return $this->error('', '商品分类不能为空');

            // 处理商品分类
            $category_id = '';
            $category_json = [];
            if (!empty($goods_data[ 'category_3' ])) {
                $category_info = model('goods_category')->getInfo([ [ 'level', '=', 3 ], [ 'site_id', '=', $site_id ], [ 'category_full_name', '=', "{$goods_data['category_1']}/{$goods_data['category_2']}/{$goods_data['category_3']}" ] ], 'category_id_1,category_id_2,category_id_3');
                if (!empty($category_info)) {
                    $category_id = "{$category_info['category_id_1']},{$category_info['category_id_2']},{$category_info['category_id_3']}";
                }
            }
            if (!empty($goods_data[ 'category_2' ]) && empty($category_id)) {
                $category_info = model('goods_category')->getInfo([ [ 'level', '=', 2 ], [ 'site_id', '=', $site_id ], [ 'category_full_name', '=', "{$goods_data['category_1']}/{$goods_data['category_2']}" ] ], 'category_id_1,category_id_2');
                if (!empty($category_info)) {
                    $category_id = "{$category_info['category_id_1']},{$category_info['category_id_2']}";
                }
            }
            if (!empty($goods_data[ 'category_1' ]) && empty($category_id)) {
                $category_info = model('goods_category')->getInfo([ [ 'level', '=', 1 ], [ 'site_id', '=', $site_id ], [ 'category_name', '=', "{$goods_data['category_1']}" ] ], 'category_id_1');
                if (!empty($category_info)) {
                    $category_id = "{$category_info['category_id_1']}";
                }
            }
            if (empty($category_id)) return $this->error('', '未找到所填商品分类');
            $category_json = [ $category_id ];

            $sku_data = [];
            $goods_spec_format = [];
            $tag = 0;
            // 处理sku数据
            if (isset($goods_data[ 'sku' ])) {
                foreach ($goods_data[ 'sku' ] as $sku_item) {
                    if (empty($sku_item[ 'sku_data' ])) return $this->error('', '规格数据不能为空');

                    $spec_name = '';
                    $spec_data = explode(';', $sku_item[ 'sku_data' ]);

                    $sku_spec_format = [];
                    foreach ($spec_data as $item) {
                        $spec_item = explode(':', $item);
                        $spec_name .= ' ' . $spec_item[ 1 ];

                        // 规格项
                        $spec_index = array_search($spec_item[ 0 ], array_column($goods_spec_format, 'spec_name'));
                        if (empty($goods_spec_format) || $spec_index === false) {
                            $spec = [
                                'spec_id' => -( $tag + getMillisecond() ),
                                'spec_name' => $spec_item[ 0 ],
                                'value' => []
                            ];
                            $goods_spec_format[] = $spec;
                            $tag++;
                        } else {
                            $spec = $goods_spec_format[ $spec_index ];
                        }
                        // 规格值
                        $spec_index = array_search($spec_item[ 0 ], array_column($goods_spec_format, 'spec_name'));
                        $spec_value_index = array_search($spec_item[ 1 ], array_column($spec[ 'value' ], 'spec_value_name'));
                        if (empty($spec[ 'value' ]) || $spec_value_index === false) {
                            $spec_value = [
                                'spec_id' => $spec[ 'spec_id' ],
                                'spec_name' => $spec[ 'spec_name' ],
                                'spec_value_id' => -( $tag + getMillisecond() ),
                                'spec_value_name' => $spec_item[ 1 ],
                                'image' => '',
                            ];
                            $goods_spec_format[ $spec_index ][ 'value' ][] = $spec_value;
                            $tag++;
                        } else {
                            $spec_value = $spec[ 'value' ][ $spec_value_index ];
                        }

                        $sku_spec_format[] = [
                            'spec_id' => $spec[ 'spec_id' ],
                            'spec_name' => $spec[ 'spec_name' ],
                            'spec_value_id' => $spec_value[ 'spec_value_id' ],
                            'spec_value_name' => $spec_value[ 'spec_value_name' ],
                            'image' => '',
                        ];
                    }

                    $sku_images_arr = explode(',', $sku_item[ 'sku_image' ]);

                    $sku_temp = [
                        'spec_name' => trim($spec_name),
                        'sku_no' => $sku_item[ 'sku_code' ],
                        'sku_spec_format' => $sku_spec_format,
                        'price' => $sku_item[ 'price' ],
                        'market_price' => $sku_item[ 'market_price' ],
                        'cost_price' => $sku_item[ 'cost_price' ],
                        'stock_alarm' => $sku_item[ 'stock_alarm' ],
                        'sku_image' => empty($sku_item[ 'sku_image' ]) ? '' : $sku_images_arr[ 0 ],
                        'sku_images' => empty($sku_item[ 'sku_image' ]) ? '' : $sku_item[ 'sku_image' ],
                        'sku_images_arr' => empty($sku_item[ 'sku_image' ]) ? [] : $sku_images_arr,
                        'is_default' => 0,
                        'carmichael' => empty($sku_item[ 'carmichael' ]) ? [] : explode("\n", $sku_item[ 'carmichael' ])
                    ];

                    $sku_data[] = $sku_temp;
                }
            } else {
                $goods_img = explode(',', $goods_data[ 'goods_image' ]);
                $sku_data = [
                    [
                        'sku_id' => 0,
                        'sku_name' => $goods_data[ 'goods_name' ],
                        'spec_name' => '',
                        'sku_spec_format' => '',
                        'price' => empty($goods_data[ 'price' ]) ? 0 : $goods_data[ 'price' ],
                        'market_price' => empty($goods_data[ 'market_price' ]) ? 0 : $goods_data[ 'market_price' ],
                        'cost_price' => empty($goods_data[ 'cost_price' ]) ? 0 : $goods_data[ 'cost_price' ],
                        'sku_no' => $goods_data[ 'goods_code' ],
                        'stock_alarm' => empty($goods_data[ 'stock_alarm' ]) ? 0 : $goods_data[ 'stock_alarm' ],
                        'sku_image' => $goods_img[ 0 ],
                        'sku_images' => $goods_data[ 'goods_image' ],
                        'carmichael' => empty($goods_data[ 'carmichael' ]) ? [] : explode("\n", $goods_data[ 'carmichael' ])
                    ]
                ];
            }

            if (count($goods_spec_format) > 4) return $this->error('', '最多支持四种规格项');

            $data = [
                'goods_name' => $goods_data[ 'goods_name' ],// 商品名称,
                'goods_attr_class' => '',// 商品类型id,
                'goods_attr_name' => '',// 商品类型名称,
                'site_id' => $site_id,
                'category_id' => ',' . $category_id . ',',
                'category_json' => json_encode($category_json),
                'goods_image' => $goods_data[ 'goods_image' ],// 商品主图路径
                'goods_content' => '',// 商品详情
                'goods_state' => 0, //$goods_data['goods_state'] == 1 || $goods_data['goods_state'] == '是' ? 1 : 0,// 商品状态（1.正常0下架）
                'price' => empty($goods_data[ 'price' ]) ? 0 : $goods_data[ 'price' ],// 商品价格（取第一个sku）
                'market_price' => empty($goods_data[ 'market_price' ]) ? 0 : $goods_data[ 'market_price' ],// 市场价格（取第一个sku）
                'cost_price' => empty($goods_data[ 'cost_price' ]) ? 0 : $goods_data[ 'cost_price' ],// 成本价（取第一个sku）
                'sku_no' => $goods_data[ 'goods_code' ],// 商品sku编码
                'goods_stock_alarm' => empty($goods_data[ 'goods_stock_alarm' ]) ? 0 : $goods_data[ 'goods_stock_alarm' ],// 库存预警
                'goods_spec_format' => empty($goods_spec_format) ? '' : json_encode($goods_spec_format, JSON_UNESCAPED_UNICODE),// 商品规格格式
                'goods_attr_format' => '',// 商品参数格式
                'introduction' => $goods_data[ 'introduction' ],// 促销语
                'keywords' => $goods_data[ 'keywords' ],// 关键词
                'unit' => $goods_data[ 'unit' ],// 单位
                'sort' => '',// 排序,
                'qr_id' => empty($goods_data[ 'qr_id' ]) ? 0 : $goods_data[ 'qr_id' ],// 社群二维码id
                'template_id' => empty($goods_data[ 'template_id' ]) ? 0 : $goods_data[ 'template_id' ],// 海报id
                'is_limit' => empty($goods_data[ 'is_limit' ]) ? 0 : $goods_data[ 'is_limit' ],// 是否限购
                'limit_type' => empty($goods_data[ 'limit_type' ]) ? 0 : $goods_data[ 'limit_type' ],// 限购类型
                'video_url' => '',// 视频
                'goods_sku_data' => json_encode($sku_data, JSON_UNESCAPED_UNICODE),// SKU商品数据
                'goods_service_ids' => '',// 商品服务id集合
                'label_id' => '',// 商品分组id
                'virtual_sale' => 0,// 虚拟销量
                'max_buy' => 0,// 限购
                'min_buy' => 0,// 起售
                'recommend_way' => 0, // 推荐方式，1：新品，2：精品，3；推荐
                'timer_on' => 0,//定时上架
                'timer_off' => 0,//定时下架
                'brand_id' => 0,
                'is_consume_discount' => $goods_data[ 'is_consume_discount' ] == 1 || $goods_data[ 'is_consume_discount' ] == '是' ? 1 : 0, //是否参与会员折扣
            ];

            return $this->addGoods($data);
        } catch ( Exception $e) {
            return $this->error('', $e->getMessage());
        }
    }
}