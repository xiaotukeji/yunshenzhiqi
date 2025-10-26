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

use app\model\system\AppVersionManage;
use app\storeapi\controller\BaseStoreApi;

/**
 * Class AppVersion
 * @package addon\cashier\storeapi\controller
 */
class AppVersion extends BaseStoreApi
{
    /**
     * 检测更新
     * @return false|string
     */
    public function checkUpdate()
    {
        $condition = [
            [ 'app_key', '=', $this->params[ 'app_key' ] ],
            [ 'platform', '=', $this->params[ 'platform' ] ],
            [ 'version_no', '>', $this->params[ 'version_no' ] ],
        ];

        $data = ( new AppVersionManage() )->getVersionFirstInfo($condition, '*', 'version_no desc');
        return $this->response($data);
    }

}