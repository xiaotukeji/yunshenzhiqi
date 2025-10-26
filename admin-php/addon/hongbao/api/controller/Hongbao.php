<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\hongbao\api\controller;

use app\api\controller\BaseApi;
use addon\hongbao\model\Hongbao as HongbaoModel;
use addon\hongbao\model\HongbaoGroup;
use app\model\member\Member as MemberModel;
use think\facade\Db;

/**
 * 裂变红包
 * Class hongbao
 * @package addon\hongbao\api\controller
 */
class Hongbao extends BaseApi
{
    /**
     * 瓜分活动列表
     * @return false|string
     */
    public function lists()
    {
        $token = $this->checkToken();
        $member_id = '';
        if ($token[ 'code' ] >= 0) {
            $member_id = $this->member_id;
        }
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $name = $this->params['name'] ?? '';
        $status = $this->params['status'] ?? '';

        $condition = [];
        if ($status != '') {
            $condition[] = [ 'status', '=', $status ];
        }
        $condition[] = [ 'status', '=', 1 ];

        $condition[] = [ 'site_id', '=', $this->site_id ];
        $condition[] = [ 'name', 'like', '%' . $name . '%' ];
        $order = 'create_time desc';
        $field = 'hongbao_id,site_id,name,start_time,end_time,money,divide_num,image';
        $hongbao_model = new HongbaoModel();
        $data = $hongbao_model->getHongbaoPageList($condition, $page, $page_size, $order, $field);
        $group_arr = [];
        if ($member_id != '') {
            $friends_group_model = new HongbaoGroup();
            $group_list = $friends_group_model->getHongbaoGroupList([ [ 'header_id', '=', $member_id ], [ 'site_id', '=', $this->site_id ] ], 'hongbao_id,status')[ 'data' ];
            if ($group_list) {
                $group_arr = array_column($group_list, 'status', 'hongbao_id');
            }
        }
        if ($data[ 'data' ]) {
            foreach ($data[ 'data' ][ 'list' ] as $k => $v) {
                #g_status  2 去瓜分 1瓜分成工（去查看）  0组队中
                $data[ 'data' ][ 'list' ][ $k ][ 'g_status' ] = $group_arr[ $v[ 'hongbao_id' ] ] ?? 2;
            }
        }
        return $this->response($data);
    }

    /**
     * 我的裂变红包
     */
    public function launchPage()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $page = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;
        $status = $this->params[ 'status' ] ?? '';

        $condition = [
            [ 'g.site_id', '=', $this->site_id ],
        ];
        $condition[] = [ '', 'exp', Db::raw("concat(',',g.group_member_ids,',') like '%,$this->member_id,%'") ];
        if ($status != '') {
            $condition[] = [ 'g.status', '=', $status ];
        }
        $friends_group_model = new HongbaoGroup();
        $join = [
            [ 'promotion_hongbao p', 'g.hongbao_id = p.hongbao_id', 'left' ],
        ];

        $field = 'g.group_id,g.site_id,g.start_time,g.end_time,g.status as g_status,p.hongbao_id,g.group_member_ids,g.is_look,p.name,p.divide_num,p.money,p.image';
        $data = $friends_group_model->getHongbaoGroupPageList($condition, $page, $page_size, 'g.start_time desc', $field, 'g', $join);
        return $this->response($data);
    }

    /**
     * 裂变红包详情
     */
    public function info()
    {
        $token = $this->checkToken();

        $hongbao_id = $this->params['hongbao_id'] ?? 0;#活动Id
        $group_id = $this->params['group_id'] ?? 0;#分组Id
        $inviter_id = $this->params['inviter_id'] ?? 0;#邀请人Id
        if (empty($hongbao_id)) {
            return $this->response($this->error('', 'REQUEST_HONGBAO_ID'));
        }
        $hongbao_model = new HongbaoModel();
        $condition = [
            [ 'hongbao_id', '=', $hongbao_id ],
            [ 'site_id', '=', $this->site_id ]
        ];

        $info = $hongbao_model->getHongbaoInfo($condition);
        if (empty($info[ 'data' ])) return $this->response($this->error('', '未查询到活动信息'));
        $friends_group_model = new HongbaoGroup();
        $group_condition = [
            [ 'a.hongbao_id', '=', $hongbao_id ],
            [ 'a.site_id', '=', $this->site_id ],

        ];
        if ($group_id) {
            $group_condition[] = [ 'a.group_id', '=', $group_id ];
        } else {
            $group_condition[] = [ 'a.header_id', '=', $this->member_id ];
            $group_condition[] = [ 'a.status', '<=', 1 ];
        }
        $field = 'a.*,m.username as header_username,m.nickname as header_nickname,m.headimg as header_headimg';
        $alias = 'a';
        $join = [
            [ 'member m', 'a.header_id = m.member_id', 'left' ]
        ];

        $group = $friends_group_model->getHongbaoGroupInfo($group_condition, $field, $alias, $join)[ 'data' ] ?? [];

        #邀请人信息
        $info[ 'data' ][ 'inviter_info' ] = [];
        if ($inviter_id) {
            $member_model = new MemberModel();
            $inviter_info = $member_model->getMemberInfo([ [ 'member_id', '=', $inviter_id ], [ 'site_id', '=', $this->site_id ] ], 'member_id,username,nickname,headimg')[ 'data' ];
            if ($inviter_info) {
                $info[ 'data' ][ 'inviter_info' ][ 'member_id' ] = $inviter_info[ 'member_id' ] ?? '';
                $info[ 'data' ][ 'inviter_info' ][ 'username' ] = $inviter_info[ 'username' ] ?? '';
                $info[ 'data' ][ 'inviter_info' ][ 'nickname' ] = $inviter_info[ 'nickname' ] ?? '';
                $info[ 'data' ][ 'inviter_info' ][ 'headimg' ] = $inviter_info[ 'headimg' ] ?? '';
            }
        }
        $info[ 'data' ][ 'group_info' ] = $group;
        $info[ 'data' ][ 'member_id' ] = $this->member_id;
        return $this->response($info);
    }

    /**
     * 推广海报
     * @return false|string
     */
    public function poster()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $hongbao_id = $this->params['hongbao_id'] ?? 0;
        $group_id = $this->params['group_id'] ?? 0;
        $inviter_id = $this->params['inviter_id'] ?? 0;#邀请人Id
        $app_type = $this->params['app_type'] ?? 'h5';
        $member_id = $this->member_id;
        $arr = [
            'hid' => $hongbao_id,
            'gid' => $group_id,
            'id' => $inviter_id
        ];
        $hongbao_model = new HongbaoModel();
        $qrcode = $hongbao_model->poster($arr, $app_type, $this->site_id, $member_id);

        return $this->response($qrcode);
    }

    /**
     * 发起瓜分
     * @return false|string
     */
    public function launch()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $hongbao_id = $this->params['hongbao_id'] ?? 0;
        if (empty($hongbao_id)) {
            return $this->response($this->error('', 'REQUEST_ID'));
        }

        $hongbao_model = new HongbaoModel();
        $res = $hongbao_model->launch($hongbao_id, $this->member_id, $this->site_id);

        return $this->response($res);
    }

    /**
     * 帮瓜分
     * @return false|string
     */
    public function divide()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $group_id = $this->params['group_id'] ?? 0;
        if (empty($group_id)) {
            return $this->response($this->error('', 'GROUP_ID'));
        }

        $hongbao_model = new HongbaoModel();
        $res = $hongbao_model->divide($group_id, $this->member_id, $this->site_id);

        return $this->response($res);
    }

}