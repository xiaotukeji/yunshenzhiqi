<?php

namespace addon\printer\data\sdk\yilianyun\api;

use addon\printer\data\sdk\yilianyun\config\YlyConfig;
use addon\printer\model\Printer;
use think\Exception;

class PrintService extends RpcService
{

    /**
     * 打印接口
     *
     * @param $machineCode string 机器码
     * @param $content string 打印内容
     * @param $originId string 商户系统内部订单号，要求32个字符内，只能是数字、大小写字母
     * @return mixed
     */
    public function index($machineCode, $content, $originId, $printer = [])
    {
        static $call_num = 0;
        $call_num ++;
        $res = $this->client->call('print/index', array ( 'machine_code' => $machineCode, 'content' => $content, 'origin_id' => $originId ));

        if (is_null($res)) {
            throw new Exception("invalid response.");
        }
        //token过期处理
        if ($res->error == 18 && $printer && $call_num <= 3) {
            $printer_model = new Printer();
            $config = new YlyConfig($printer[ 'open_id' ], $printer[ 'apikey' ]);
            $access_token = $printer_model->getYlyToken($config, $printer[ 'printer_id' ], 1);
            $res = (new self($access_token, $config))->index($machineCode, $content, $originId, $printer);
        }
        if (isset($res->error) && $res->error != 0) {
            $errorDescription = isset($res->body) ? $res->error_description . $res->body : $res->error_description;
            throw new Exception('Call method print/index error code is ' . $res->error . ' error message is ' . $errorDescription);
        }

        return $res;
    }
}