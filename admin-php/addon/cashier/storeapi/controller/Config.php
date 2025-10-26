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

use addon\cashier\model\Cashier as CashierModel;
use app\storeapi\controller\BaseStoreApi;
use app\model\order\Config as OrderConfigModel;

/**
 * Class Config
 * @package addon\cashier\storeapi\controller
 */
class Config extends BaseStoreApi
{

    public function __construct()
    {
        $this->site_id = request()->siteid();
        $this->params = input();
    }

    /**
     * 获取收银台主题风格配置
     * @return false|string
     */
    public function getThemeConfig()
    {
        $res = (new CashierModel())->getThemeConfig($this->site_id)[ 'data' ][ 'value' ];
        return $this->response($this->success($res));
    }

    /**
     * 设置收银台会员搜索方式配置
     * @return false|string
     */
    public function setMemberSearchWayConfig()
    {
        $data = [
            'way' => $this->params[ 'way' ] ?? 'exact'
        ];
        $res = (new CashierModel())->setMemberSearchWayConfig($data, $this->store_id, $this->site_id);
        return $this->response($this->success($res));
    }

    /**
     * 获取收银台会员搜索方式配置
     * @return false|string
     */
    public function getMemberSearchWayConfig()
    {
        $res = (new CashierModel())->getMemberSearchWayConfig($this->store_id, $this->site_id)[ 'data' ][ 'value' ];
        return $this->response($this->success($res));
    }

    /**
     * 插件是否存在
     */
    public function addonIsExist()
    {
        $addon = new \app\model\system\Addon();
        $addon_is_exist = $addon->addonIsExist();
        return $this->response($this->success($addon_is_exist));
    }

    /**
     * 订单提醒
     */
    public function orderRemind()
    {
        $config_model = new OrderConfigModel();
        $config_info = $config_model->getOrderRemindConfig($this->site_id)['data']['value'];
        return $this->response($this->success($config_info));
    }
}