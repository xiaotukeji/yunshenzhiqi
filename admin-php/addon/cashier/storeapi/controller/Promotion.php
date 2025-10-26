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

use addon\cashier\model\Group;
use app\model\system\User as UserModel;
use app\model\system\UserGroup;
use app\storeapi\controller\BaseStoreApi;


/**
 * 用户控制器
 * Class User
 * @package addon\shop\siteapi\controller
 */
class Promotion extends BaseStoreApi
{
    public function getPromotionQrcode()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $promotion_model = new \app\model\system\Promotion();
        $option_json = $this->params['option'] ?? '';
        $option = $option_json ? json_decode($option_json, true) : [];
        $res = $promotion_model->getPromotionQrcode([
            'page_name' => $this->params['page_name'] ?? '',
            'option' => $option,
            'app_type' => $this->params['app_type'] ?? 'h5',
            'site_id' => $this->site_id,
        ]);

        return $this->response($res);
    }
}