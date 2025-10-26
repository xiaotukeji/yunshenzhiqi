<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\wechatpay\model;

use addon\shopcomponent\model\Weapp;
use app\exception\ApiException;
use app\model\order\OrderMessage;
use app\model\system\Cron;
use app\model\BaseModel;
use addon\weapp\model\Config as WeappConfig;
use addon\wechat\model\Config as WechatConfig;
use app\model\system\Pay as PayModel;
use think\facade\Log;
use addon\wechatpay\model\Config as WechatPayConfig;
/**
 * 微信支付配置
 * 版本 1.0.4
 */
class Pay extends BaseModel
{
    /**
     * 支付接口实例
     * @var
     */
    private $app;

    /**
     * 是否是小程序端
     * @var int
     */
    private $is_weapp = 0;

    /**
     * 支付配置
     * @var array|mixed
     */
    private $config = [];

    /**
     * 微信支付接口版本
     * @var string
     */
    private $api = 'v2';

    /**
     * 站点id
     * @var
     */
    private $site_id;

    public function __construct($is_weapp = 0, $site_id = 1)
    {
        $this->is_weapp = $is_weapp;
        $this->site_id = $site_id;

        // 支付配置
        $config_model = new Config();
        $this->config = $config_model->getPayConfig($site_id)[ 'data' ][ 'value' ];
        if (empty($this->config)) throw new ApiException(-1, "平台未配置微信支付");

        $this->api = $this->config[ 'api_type' ];
        $this->config[ 'site_id' ] = $site_id;

        if ($is_weapp == 0) {
            $wechat_config = ( new WechatConfig() )->getWechatConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->config[ 'appid' ] = $wechat_config[ 'appid' ] ?? '';
        } else {
            $weapp_config = ( new WeappConfig() )->getWeappConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->config[ 'appid' ] = $weapp_config[ 'appid' ] ?? '';
        }

        $this->factory();
    }

    /**
     * 实例化支付接口
     */
    public function factory()
    {
        $class = 'addon\\wechatpay\\model\\' . ucfirst($this->api);
        if (!class_exists($class)) throw new ApiException(-1, "Class '{$class}' not found");

        try {
            $this->app = new $class($this->config);
        } catch (\Exception $e) {
            Log::write('微信支付配置错误:' . $e->getMessage() . $e->getFile() . $e->getLine());
            throw new ApiException(-1, "微信支付配置错误");
        }
    }

    /**
     *
     * 生成支付
     * @param $param
     */
    public function pay($param)
    {
        if (!$this->config[ 'pay_status' ]) return $this->error([], '平台未启用微信支付');

        //清除绑定支付数据
        $pay_model = new PayModel();
        $clear_res = $pay_model->clearMchPay($param[ "out_trade_no" ], 'wechatpay');
        if($clear_res['code'] < 0) return $clear_res;

        if ($param[ 'trade_type' ] == 'JSAPI' || $param[ 'trade_type' ] == 'APPLET') {
            $member_info = model('member')->getInfo([ [ "member_id", "=", $param[ "member_id" ] ] ], 'weapp_openid,wx_openid');
            if (empty($member_info)) return $this->error(-1, '未获取到会员信息');
            $param[ 'openid' ] = $this->is_weapp ? $member_info[ 'weapp_openid' ] : $member_info[ 'wx_openid' ];
        }

        try {
            $result = $this->app->pay($param);

            //绑定支付数据
            if($result['code'] >= 0){
                $pay_model->bindMchPay($param[ "out_trade_no" ], [
                    "pay_type" => 'wechatpay',
                    "is_weapp" => $this->is_weapp,
                    "app_id" => $this->config[ "appid" ],
                ]);
            }

            // 对视频号订单做处理
            if ($result[ 'code' ] == 0 && in_array($param[ 'scene' ], [ 1175, 1176, 1177, 1191, 1195 ])) {
                $weapp_model = new Weapp($param[ 'site_id' ]);
                $prepay_id = str_replace('prepay_id=', '', $result[ 'data' ][ 'data' ][ 'package' ]);
                $order_info = $this->getOrderInfo($param[ "out_trade_no" ], $param[ 'openid' ], $prepay_id, $param[ 'scene' ]);
                $res = $weapp_model->createOrder($order_info);
                if ($res[ 'code' ] >= 0) {
                    $order_params = [
                        "order_id" => $res[ 'data' ][ 'data' ][ 'order_id' ],
                        "out_order_id" => $res[ 'data' ][ 'data' ][ 'out_order_id' ],
                        "openid" => $param[ "openid" ]
                    ];
                    $config = $weapp_model->getPaymentParams($order_params);
                    $result = $this->success([
                        'type' => 'jsapi',
                        'data' => $config[ 'data' ][ 'payment_params' ]
                    ]);
                }
            }
            return $result;
        } catch (\Exception $e) {
            Log::write('微信支付接口调用失败，请求参数：' . json_encode($param) . ' 错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            return $this->error([], '微信支付接口调用失败');
        }
    }

    /**
     * 支付异步通知
     * @return mixed
     */
    public function payNotify()
    {
        return $this->app->payNotify();
    }

    /**
     * 关闭支付
     * @param $param
     * @return array
     */
    public function close($param)
    {
        try {
            return $this->app->payClose($param);
        } catch (\Exception $e) {
            Log::write('微信订单关闭失败，请求参数：' . json_encode($param) . ' 错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            return $this->error([], '微信订单关闭失败');
        }
    }

    /**
     * 微信原路退款
     * @param $param
     * @return array
     */
    public function refund($param)
    {
        if (!$this->config[ 'refund_status' ]) return $this->error([], '平台未启用微信退款');

        try {
            $mch_info = empty($param[ "pay_info" ][ 'mch_info' ]) ? [] : json_decode($param[ "pay_info" ][ 'mch_info' ], true);
            $this->config[ "appid" ] = $mch_info[ "app_id" ] ?? $this->config[ "appid" ];//替换为商户自己的appid
            $this->factory();

            return $this->app->refund($param);
        } catch (\Exception $e) {
            Log::write('微信退款失败，请求参数：' . json_encode($param) . ' 错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            return $this->error(['file' => $e->getFile(),'line'=>$e->getLine(),'message'=>$e->getMessage()], '微信退款失败');
        }
    }

    /**
     * 微信转账到零钱
     * @param array $param
     * @return array
     * @throws ApiException
     */
    public function transfer(array $param)
    {
        if (!$this->config[ 'transfer_status' ]) return $this->error([], '平台未启用微信转账');
        if ($this->api != $this->config[ 'transfer_type' ]) {
            $this->api = $this->config[ 'transfer_type' ];
            $this->factory();
        }
        try {
            $result = $this->app->transfer($param);
            return $result;
        } catch (\Exception $e) {
            Log::write('微信转账接口调用失败，错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            return $this->error([], '微信转账接口调用失败');
        }
    }

    /**
     * 获取支付所需订单信息
     * @param $out_trade_no
     * @param $openid
     * @param $prepay_id
     * @param int $scene
     * @return array
     */
    private function getOrderInfo($out_trade_no, $openid, $prepay_id, $scene = 0)
    {
        $order_info = model('order')->getInfo([ [ 'out_trade_no', '=', $out_trade_no ] ], 'site_id,create_time,order_id,order_type,member_id,order_money,delivery_money,name,full_address,mobile,delivery_store_info,delivery_store_name');
        $data = [
            'create_time' => date("Y-m-d H:i:s", $order_info[ 'create_time' ]),
            'out_order_id' => $order_info[ 'order_id' ],
            'openid' => $openid,
            'path' => ( new OrderMessage() )->handleUrl($order_info[ 'order_type' ], $order_info[ 'order_id' ]),
            'out_user_id' => $order_info[ 'member_id' ],
            'order_detail' => [
                'product_infos' => [],
                'pay_info' => [
                    'pay_method' => '微信支付',
                    'prepay_id' => $prepay_id,
                    'prepay_time' => date('Y-m-d H:i:s', time())
                ],
                'price_info' => [
                    'order_price' => $order_info[ 'order_money' ] * 100,
                    'freight' => $order_info[ 'delivery_money' ] * 100,
                    'discounted_price' => 0,
                    'additional_price' => 0
                ]
            ],
            'delivery_detail' => [],
            'fund_type' => 1,
            'expire_time' => time() + 3600
        ];
        // 配送方式
        switch ( $order_info[ 'order_type' ] ) {
            case 2:
                $delivery_store_info = json_decode($order_info[ 'delivery_store_info' ], true);
                $data[ 'delivery_detail' ][ 'delivery_type' ] = 4; //用户自提
                $data[ 'address_info' ] = [
                    'receiver_name' => $order_info[ 'delivery_store_name' ],
                    'detailed_address' => $delivery_store_info[ 'full_address' ],
                    'tel_number' => $delivery_store_info[ 'telphone' ]
                ];
                break;
            case 3:
                $data[ 'delivery_detail' ][ 'delivery_type' ] = 3; //线下配送
                break;
            case 4:
                $data[ 'delivery_detail' ][ 'delivery_type' ] = 2; //无需快递
                break;
            default:
                $data[ 'delivery_detail' ][ 'delivery_type' ] = 1; //正常快递
                $data[ 'address_info' ] = [
                    'receiver_name' => $order_info[ 'name' ],
                    'detailed_address' => $order_info[ 'full_address' ],
                    'tel_number' => $order_info[ 'mobile' ]
                ];
                break;
        }
        $order_goods = model('order_goods')->getList([ [ 'order_id', '=', $order_info[ 'order_id' ] ] ], 'goods_id,sku_id,num,price,sku_name,sku_image');
        foreach ($order_goods as $goods) {
            $data['order_detail']['product_infos'][] = [
                'out_product_id' => $goods['goods_id'],
                'out_sku_id' => $goods['sku_id'],
                'product_cnt' => numberFormat($goods['num']),
                'sale_price' => $goods['price'] * 100,
                'path' => 'pages/goods/detail?sku_id=' . $goods['sku_id'],
                'title' => $goods['sku_name'],
                'head_img' => img($goods['sku_image']),
                'sku_real_price' => $goods['price'] * 100
            ];
        }
        $video_number_scene = [ 1175, 1176, 1177, 1191, 1195 ]; // 视频号场景值
        if (in_array($scene, $video_number_scene)) model('order')->update([ 'is_video_number' => 1 ], [ [ 'order_id', '=', $order_info[ 'order_id' ] ] ]);
        return $data;
    }


    /**
     * 付款码支付
     * @param $param
     * @return array|mixed|void
     * @throws ApiException
     */
    public function micropay($param)
    {
        if (!$this->config[ 'pay_status' ]) return $this->error([], '平台未启用微信支付');

        if ($this->api != 'v2') {
            $this->api = 'v2';
            $this->factory();
        }

        try {
            $pay_model = new PayModel();

            //清空绑定支付数据
            $clear_res = $pay_model->clearMchPay($param[ "out_trade_no" ], 'wechatpay');
            if($clear_res['code'] < 0) return $clear_res;
            //绑定支付数据
            $pay_model->bindMchPay($param[ "out_trade_no" ], [
                "pay_type" => 'wechatpay',
                "app_id" => $this->config[ "appid" ],
            ]);

            $res = $this->app->micropay($param);
            if ($res[ 'code' ] != 0) {
                if (isset($res['data']['err_code'])) {
                    if ($res['data']['err_code'] == 'TRADE_ERROR'){
                        (new PayModel())->edit(['pay_status' => PayModel::PAY_STATUS_CANCEL], ['out_trade_no' => $param[ "out_trade_no" ] ]);
                    }
                }
                return $res;
            }
            $res = $pay_model->onlinePay($param[ 'out_trade_no' ], 'wechatpay', $res[ 'data' ][ 'transaction_id' ], 'wechatpay');
            return $res;
        } catch (\Exception $e) {
            Log::write('微信付款码支付失败，请求参数：' . json_encode($param) . ' 错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            return $this->error([], '微信付款码支付失败');
        }
    }


    /**
     * 微信转账到零钱新版本回调
     */
    public function transferNotify()
    {
        if (!$this->config[ 'transfer_status' ]) return $this->error([], '平台未启用微信转账');
        if ($this->api != $this->config[ 'transfer_type' ]) {
            $this->api = $this->config[ 'transfer_type' ];
            $this->factory();
        }
        try {
            return $this->app->transferNotify();
        } catch (\Exception $e) {
            Log::write('微信转账接口回调处理失败，错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            return $this->error([], '微信转账接口回调处理失败');
        }
    }


    /**
     * 撤销微信转账
     * @param $out_bill_no 商户单号
     * @return array
     */

    public function transferCancel($out_bill_no){

        if (!$this->config[ 'transfer_status' ]) return $this->error([], '平台未启用微信转账');
        if ($this->api != $this->config[ 'transfer_type' ]) {
            $this->api = $this->config[ 'transfer_type' ];
            $this->factory();
        }
        try {
            return $this->app->transferCancel($out_bill_no);
        } catch (\Exception $e) {
            Log::write('微信转账接口回调处理失败，错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            return $this->error([], '微信转账接口回调处理失败');
        }
    }
}
