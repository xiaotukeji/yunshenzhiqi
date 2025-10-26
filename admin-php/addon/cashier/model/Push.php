<?php


namespace addon\cashier\model;


use app\model\BaseModel;
use GatewayClient\Gateway;
use think\facade\Log;
use app\dict\order\OrderDict;

class Push extends BaseModel
{
    public function orderPay($order_info)
    {
        try{
            if(!empty($order_info['store_id'])){
                $config_info = require root_path().'/config/gateway.php';
                Gateway::$registerAddress = $config_info['gateway']['register_address'];
                $uid = 'store_'.$order_info['store_id'];
                $data = [];
                $fields = ['order_id','order_type','order_type_name'];
                foreach ($fields as $field){
                    $data[$field] = $order_info[$field];
                }
                //语音通知类型
                if($order_info['order_type'] == OrderDict::cashier){
                    $data['audio'] = 'cashier_order_pay_audio';
                }else{
                    $data['audio'] = 'order_pay_audio';
                }
                if(Gateway::isUidOnline($uid)){
                    Gateway::sendToUid($uid, json_encode([ 'type' => 'order_pay', 'data' => $data ]));
                    return success();
                }
            }
        }catch(\Exception $e){
            Log::write('订单支付消息推送捕获错误');
            Log::write(['file' => $e->getFile(), 'line' => $e->getLine(), 'message' => $e->getMessage()]);
        }
    }
}