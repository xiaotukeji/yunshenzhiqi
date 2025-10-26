<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\api\controller;

use addon\fenxiao\model\Config;
use addon\fenxiao\model\FenxiaoApply;
use addon\fenxiao\model\Fenxiao;
use app\api\controller\BaseApi;

/**
 * 申请分销商
 */
class Apply extends BaseApi
{
    /**
     * 判断分销商名称是否存在
     */
    public function existFenxiaoName()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $fenxiao_name = $this->params['fenxiao_name'] ?? '';//分销商名称
        if (empty($fenxiao_name)) {
            return $this->response($this->error('', 'REQUEST_FENXIAO_NAME'));
        }

        $apply_model = new FenxiaoApply();
        $res = $apply_model->existFenxiaoName($fenxiao_name, $this->site_id);

        return $this->response($res);
    }

    /**
     * 申请成为分销商
     */
    public function applyFenxiao()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $fenxiao_name = $this->params['fenxiao_name'] ?? '';//分销商名称
        $mobile = $this->params['mobile'] ?? '';//联系电话

        $config = new Config();
        $basics_config = $config->getFenxiaoBasicsConfig($this->site_id)[ 'data' ][ 'value' ];
        if (!$basics_config[ 'level' ]) return $this->response($this->error('', '未开启分销功能'));

        if ($basics_config[ 'is_apply' ] == 1) {
            if (empty($fenxiao_name)) {
                return $this->response($this->error('', 'REQUEST_FENXIAO_NAME'));
            }
            if (empty($mobile)) {
                return $this->response($this->error('', 'REQUEST_MOBILE'));
            }
            $apply_model = new FenxiaoApply();
            $res = $apply_model->applyFenxiao($this->member_id, $this->site_id, $fenxiao_name, $mobile);
        } else if ($basics_config[ 'is_apply' ] == 0) {
            $apply_model = new Fenxiao();
            $res = $apply_model->autoBecomeFenxiao($this->member_id, $this->site_id);
        } else {
            return $this->response($this->error('', '未开启分销商申请'));
        }

        return $this->response($res);
    }

    public function info()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $apply_model = new Fenxiao();
        $apply_model->getFenxiaoInfo([ [ 'member_id', '=', $this->member_id ] ], 'apply_id,fenxiao_name,parent,member_id,mobile,nickname,headimg,level_id,level_name,status');
    }

    /**
     * 获取申请分销商状态
     * @return false|string
     */
    public function status()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $apply_model = new FenxiaoApply();
        $res = $apply_model->getFenxiaoApplyInfo([ [ 'member_id', '=', $this->member_id ] ], 'status');
        return $this->response($res);
    }

}