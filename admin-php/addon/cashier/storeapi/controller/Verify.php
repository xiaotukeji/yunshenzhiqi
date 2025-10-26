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

use app\model\verify\Verify as VerifyModel;
use app\model\verify\VerifyRecord;
use app\storeapi\controller\BaseStoreApi;

/**
 * 订单核销控制器
 * Class Verify
 * @package addon\shop\siteapi\controller
 */
class Verify extends BaseStoreApi
{

    /**
     * 获取核销码信息
     * @return false|string
     */
    public function info()
    {
        $code = $this->params[ 'code' ] ?? '';
        $verify_model = new VerifyModel();

        $condition = [
            [ 'verify_code', '=', $code ],
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', 'in', [0, $this->store_id] ]
        ];
        $info = $verify_model->getVerifyInfo($condition);
        if(!empty($info['data'])){
            $check_store_res = $verify_model->checkStore($info['data'], $this->store_id);
            if($check_store_res['code'] < 0){
                return $this->response($check_store_res);
            }
        }
        return $this->response($info);
    }

    /**
     * 核销类型
     */
    public function verifyType()
    {
        $verify_model = new VerifyModel();
        $verify_type = $verify_model->getVerifyType();
        return $this->response($this->success($verify_type));
    }

    /**
     * 核销
     */
    public function verify()
    {
        $verify_code = $this->params[ 'verify_code' ] ?? '';
        $info = [
            'verifier_id' => $this->uid,
            'verifier_name' => $this->user_info[ 'username' ],
            'verify_from' => 'shop',
            'store_id' => $this->store_id
        ];
        $verify_model = new VerifyModel();
        $res = $verify_model->verify($info, $verify_code);
        return $this->response($res);
    }

    /**
     * 核销记录
     * @return false|string
     */
    public function recordLists()
    {
        $verify_model = new VerifyRecord();

        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $verify_type = $this->params[ 'verify_type' ] ?? '';//验证类型
        $search_text = $this->params[ 'search_text' ] ?? '';
        $start_time = $this->params[ 'start_time' ] ?? '';
        $end_time = $this->params[ 'end_time' ] ?? '';

        $condition = [
            [ 'store_id', '=', $this->store_id ],
        ];
        if (!empty($search_text)) {
            $condition[] = [ 'verify_code|verifier_name', 'like', '%' . $search_text . '%' ];
        }

        if (!empty($start_time) && empty($end_time)) {
            $condition[] = [ 'verify_time', '>=', date_to_time($start_time) ];
        } elseif (empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'verify_time', '<=', date_to_time($end_time) ];
        } elseif (!empty($start_time) && !empty($end_time)) {
            $condition[] = [ 'verify_time', 'between', [ date_to_time($start_time), date_to_time($end_time) ] ];
        }

        $list = $verify_model->getVerifyRecordsPageList($condition, $page, $page_size, '*', 'verify_time desc');

        return $this->response($list);
    }

    /**
     * 核销记录详情
     * @return false|string
     */
    public function recordsDetail()
    {
        $id = $this->params[ 'id' ] ?? 0;
        $verify_model = new VerifyRecord();
        $condition = [
            [ 'vr.id', '=', $id ],
            [ 'vr.store_id', '=', $this->store_id ]
        ];
        $field = 'vr.*,v.verify_type_name,v.expire_time,v.verify_total_count,v.verify_use_num,v.verify_content_json,v.member_id, m.nickname,m.headimg,m.mobile';
        $join = [
            [ 'verify v', 'v.verify_code = vr.verify_code', 'left' ],
            [ 'member m', 'v.member_id = m.member_id', 'left' ],
        ];
        $verify_detail = $verify_model->getVerifyRecordsInfo($condition, $field, 'vr', $join)[ 'data' ] ?? [];
        if (!empty($verify_detail)) {
            $verify_detail['verify_content_json'] = json_decode($verify_detail['verify_content_json'], true);
        }
        return $this->response($this->success($verify_detail));
    }

}