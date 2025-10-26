<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\goodscircle\model;

use app\model\BaseModel;
use addon\weapp\model\Config as WeappConfigModel;
use addon\wxoplatform\model\Config as WxOplatformConfigModel;
use EasyWeChat\Factory;

/**
 *  好物圈
 */
class GoodsCircle extends BaseModel
{
    private $app;

    public function __construct($site_id = 0)
    {
        //微信小程序配置
        $weapp_config_model = new WeappConfigModel();
        $weapp_config = $weapp_config_model->getWeappConfig($site_id)['data']['value'];

        if (isset($weapp_config[ 'is_authopen' ]) && addon_is_exit('wxoplatform')) {
            $plateform_config_model = new WxOplatformConfigModel();
            $plateform_config = $plateform_config_model->getOplatformConfig()['data']['value'];

            $config = [
                'app_id' => $plateform_config['appid'] ?? '',
                'secret' => $plateform_config['secret'] ?? '',
                'token' => $plateform_config['token'] ?? '',
                'aes_key' => $plateform_config['aes_key'] ?? '',
                'log' => [
                    'level' => 'debug',
                    'permission' => 0777,
                    'file' => 'runtime/log/wechat/oplatform.logs',
                ],
            ];
            $open_platform = Factory::openPlatform($config);
            $this->app = $open_platform->miniProgram($weapp_config[ 'authorizer_appid' ], $weapp_config[ 'authorizer_refresh_token' ]);
        } else {
            $config = [
                'app_id' => $weapp_config['appid'] ?? '',
                'secret' => $weapp_config['appsecret'] ?? '',
                'response_type' => 'array',
                'log' => [
                    'level' => 'debug',
                    'permission' => 0777,
                    'file' => 'runtime/log/wechat/easywechat.logs',
                ],
            ];
            $this->app = Factory::miniProgram($config);
        }
    }

    /**
     * 导入或更新物品信息
     */
    public function importProduct($goods_ids)
    {
        if (!empty($goods_ids)) {
            $data = [
                'product_list' => []
            ];
            $goods_list = model('goods')->getList([ [ 'goods_id', 'in', explode(',', $goods_ids) ] ], 'goods_id,goods_name,introduction,goods_image,sku_id,category_id,goods_attr_format');
            if (!empty($goods_list)) {
                try {
                    foreach ($goods_list as $goods_item) {
                        $sku_list = model('goods_sku')->getList([ [ 'goods_id', '=', $goods_item[ 'goods_id' ] ] ], 'sku_id,discount_price,price,goods_state,stock');
                        foreach ($sku_list as $k => $v) {
                            $sku_list[ $k ][ 'stock' ] = numberFormat($sku_list[ $k ][ 'stock' ]);
                        }
                        $goods_data = [
                            'item_code' => $goods_item[ 'goods_id' ],
                            'title' => $goods_item[ 'goods_name' ],
                            'desc' => $goods_item[ 'introduction' ],
                            'category_list' => [],
                            'image_list' => array_map(function($value) {
                                return img($value);
                            }, explode(',', $goods_item[ 'goods_image' ])),
                            'src_wxapp_path' => '/pages/goods/detail?sku_id=' . $goods_item[ 'sku_id' ],
                            'sku_list' => [],
                            'attr_list' => []
                        ];
                        if (!empty($goods_item[ 'category_id' ])) {
                            $category_id = trim($goods_item[ 'category_id' ], ',');
                            $goods_category = model('goods_category')->getList([ [ 'category_id', 'in', explode(',', $category_id) ] ], 'category_name');
                            if (!empty($goods_category)) {
                                foreach ($goods_category as $item) {
                                    $goods_data["category_list"][] = $item['category_name'];
                                }
                            }
                        }
                        if (!empty($goods_item[ 'goods_attr_format' ])) {
                            $goods_attr = json_decode($goods_item[ 'goods_attr_format' ], true);
                            foreach ($goods_attr as $attr_item) {
                                $attr_data = [
                                    'name' => $attr_item[ 'attr_name' ],
                                    'value' => $attr_item[ 'attr_value_name' ]
                                ];
                                $goods_data["attr_list"][] = $attr_data;
                            }
                        }
                        foreach ($sku_list as $sku_item) {
                            $sku_data = [
                                'sku_id' => $sku_item[ 'sku_id' ],
                                'price' => $sku_item[ 'discount_price' ] * 100,
                                'original_price' => $sku_item[ 'price' ] * 100
                            ];
                            $sku_data[ 'status' ] = $sku_item[ 'goods_state' ] == 1 ? 1 : 2;
                            if ($sku_item[ 'stock' ] == 0) $sku_data[ 'status' ] = 3;
                            $goods_data["sku_list"][] = $sku_data;
                        }
                        $data['product_list'][] = $goods_data;
                    }
                    $result = $this->app->mall->product->import($data);
                    if (isset($result[ 'errcode' ]) && $result[ 'errcode' ] == 0) {
                        return $this->success();
                    } else {
                        return $this->error('', $result[ 'errmsg' ]);
                    }
                } catch (\Exception $e) {
                    return $this->error('', $e->getMessage());
                }
            }
        }
    }

    /**
     * 导入订单每次最大10条
     * @param $order_id
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function importOrder($order_ids)
    {
        if (!empty($order_ids)) {
            $data = [
                'order_list' => []
            ];
            $order_join = [
                [ 'member m', 'm.member_id = o.member_id', 'left' ]
            ];
            $order_list = model('order')->getList([ [ 'order_id', 'in', explode(',', $order_ids) ] ], 'o.order_id,o.order_no,o.goods_money,o.pay_money,o.delivery_money,o.create_time,o.pay_time,o.buyer_message,o.pay_type,o.order_status_name,m.weapp_openid', '', 'o', $order_join, '', 10);
            if (!empty($order_list)) {
                try {
                    foreach ($order_list as $order_item) {
                        $order_data = [
                            'order_id' => $order_item[ 'order_id' ],
                            'create_time' => $order_item[ 'create_time' ],
                            'pay_finish_time' => $order_item[ 'pay_time' ],
                            'desc' => $order_item[ 'buyer_message' ],
                            'fee' => $order_item[ 'pay_money' ],
                            'trans_id' => $order_item[ 'order_no' ],
                            'ext_info' => [
                                'product_info' => [
                                    'item_list' => []
                                ],
                                'express_info' => [
                                    'price' => $order_item[ 'delivery_money' ]
                                ],
                                'brand_info' => [
                                    'contact_detail_page' => [
                                        'path' => '/pages/index/index'
                                    ]
                                ],
                                'payment_method' => $order_item[ 'pay_type' ] == 'wechatpay' ? 1 : 2,
                                'user_open_id' => $order_item[ 'weapp_openid' ],
                                'order_detail_page' => [
                                    'path' => '/pages/order/detail?order_id=' . $order_item[ 'order_id' ]
                                ],
                                'total_fee' => $order_item[ 'goods_money' ]
                            ]
                        ];
                        switch ( $order_item[ 'order_status_name' ] ) {
                            case '待发货':
                                $order_data[ 'status' ] = 3;
                                break;
                            case '已发货':
                                $order_data[ 'status' ] = 4;
                                break;
                            case '已完成':
                                $order_data[ 'status' ] = 100;
                                break;
                        }
                        $order_goods_join = [
                            [ 'goods g', 'og.goods_id = g.goods_id', 'left' ]
                        ];
                        $order_goods_list = model('order_goods')->getList([ [ 'order_id', '=', $order_item[ 'order_id' ] ] ], 'og.goods_id,og.sku_id,og.num,og.goods_money,og.sku_image,og.sku_name,og.price,g.category_id', '', 'og', $order_goods_join);
                        foreach ($order_goods_list as $goods_item) {
                            $goods_data = [
                                'item_code' => $goods_item[ 'goods_id' ],
                                'sku_id' => $goods_item[ 'sku_id' ],
                                'amount' => numberFormat($goods_item[ 'num' ]),
                                'total_fee' => $goods_item[ 'goods_money' ] * 100,
                                'thumb_url' => img($goods_item[ 'sku_image' ]),
                                'title' => $goods_item[ 'sku_name' ],
                                'unit_price' => $goods_item[ 'price' ],
                                'original_price' => $goods_item[ 'price' ],
                                'category_list' => [],
                                'item_detail_page' => [
                                    'path' => '/pages/goods/detail?sku_id=' . $goods_item[ 'sku_id' ]
                                ]
                            ];
                            if (!empty($goods_item[ 'category_id' ])) {
                                $category_id = trim($goods_item[ 'category_id' ], ',');
                                $goods_category = model('goods_category')->getList([ [ 'category_id', 'in', explode(',', $category_id) ] ], 'category_name');
                                if (!empty($goods_category)) {
                                    foreach ($goods_category as $item) {
                                        $goods_data["category_list"][] = $item['category_name'];
                                    }
                                }
                            }
                            $order_data['ext_info']['product_info']['item_list'][] = $goods_data;
                        }
                        $data['order_list'][] = $order_data;
                    }
                    $result = $this->app->mall->order->add($data);
                    if (isset($result[ 'errcode' ]) && $result[ 'errcode' ] == 0) {
                        return $this->success();
                    } else {
                        return $this->error('', $result[ 'errmsg' ]);
                    }
                } catch (\Exception $e) {
                    return $this->error('', $e->getMessage());
                }
            }
        }
    }
}