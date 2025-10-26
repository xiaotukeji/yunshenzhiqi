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

namespace addon\cashier\storeapi\controller;

use app\storeapi\controller\BaseStoreApi;
use GatewayClient\Gateway;

/**
 * 消息推送
 */
class Push extends BaseStoreApi
{
    //初始化
    protected function initGateway()
    {
        $config_info = require root_path().'/config/gateway.php';
        Gateway::$registerAddress = $config_info['gateway']['register_address'];
    }

    //uid
    protected function getUid($store_id)
    {
        return 'store_'.$store_id;
    }

    //捕获错误
    protected function pushError(\Exception $e)
    {
        return $this->response($this->error([$e->getFile(),$e->getLine(),$e->getMessage()], '推送服务未开启，请联系管理员'));
    }

    /**
     * 绑定
     */
    public function bind()
    {
        $client_id = $this->params['client_id'] ?? '';
        if(empty($client_id)){
            return $this->response($this->error(null, '客户端id不能为空'));
        }

        try{
            $this->initGateway();
            Gateway::bindUid($client_id, $this->getUid($this->store_id));
            return $this->response($this->success());
        }catch(\Exception $e){
            return $this->pushError($e);
        }
    }

    /**
     * 改变绑定
     */
    public function changeBind()
    {
        $client_id = $this->params['client_id'] ?? '';
        $old_store_id = $this->params['old_store_id'] ?? '';
        if(empty($client_id)){
            return $this->response($this->error(null, '客户端id不能为空'));
        }

        try{
            $this->initGateway();
            Gateway::unbindUid($client_id, $this->getUid($old_store_id));
            Gateway::bindUid($client_id, $this->getUid($this->store_id));
            return $this->response($this->success());
        }catch(\Exception $e){
            return $this->pushError($e);
        }
    }

    /**
     * 下线
     */
    public function offline()
    {
        $client_id = $this->params['client_id'] ?? '';
        if(empty($client_id)){
            return $this->response($this->error(null, '客户端id不能为空'));
        }

        try{
            $this->initGateway();
            Gateway::closeClient($client_id);
            return $this->response($this->success());
        }catch(\Exception $e){
            return $this->pushError($e);
        }
    }

    /**
     * 服务状态
     */
    public function status()
    {
        try{
            $this->initGateway();
            Gateway::isUidOnline($this->getUid($this->store_id));
            return $this->response($this->success());
        }catch(\Exception $e){
            return $this->pushError($e);
        }
    }
}