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

namespace addon\store\api\controller;

use addon\store\model\Reserve as ReserveModel;
use app\api\controller\BaseApi;
use app\model\system\UserGroup;


class Reserve extends BaseApi
{
    /**
     * 预约状态
     * @return false|string
     */
    public function status()
    {
        $reserve_state = ( new ReserveModel )->reserve_state;
        return $this->response($this->success($reserve_state));
    }

    /**
     * 添加预约
     * @return mixed
     */
    public function addReserve()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            return $this->response($token);
        }
        $goods = json_decode($this->params[ 'goods' ], true) ?? [];
        $store_id = $this->params[ 'store_id' ] ?? 0;
        $date = $this->params[ 'date' ] ?? '';
        $time = $this->params[ 'time' ] ?? '';
        $remark = $this->params[ 'remark' ] ?? '';

        $reserve_model = new ReserveModel();

        $res = $reserve_model->addReserve([
            'site_id' => $this->site_id,
            'app_module' => $this->app_module,
            'member_id' => $this->member_id,
            'goods' => $goods,
            'store_id' => $store_id,
            'date' => $date,
            'time' => $time,
            'remark' => $remark,
            'source' => 'member'
        ]);
        return $this->response($res);
    }

    /**
     * 修改预约
     * @return mixed|void
     */
    public function updateReserve()
    {

        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            return $this->response($token);
        }
        $goods = json_decode($this->params[ 'goods' ], true) ?? [];
        $store_id = $this->params[ 'store_id' ] ?? 0;
        $date = $this->params[ 'date' ] ?? '';
        $time = $this->params[ 'time' ] ?? '';
        $remark = $this->params[ 'remark' ] ?? '';
        $reserve_id = $this->params[ 'reserve_id' ] ?? 0;

        $reserve_model = new ReserveModel();

        $res = $reserve_model->editReserve([
            'site_id' => $this->site_id,
            'app_module' => $this->app_module,
            'member_id' => $this->member_id,
            'goods' => $goods,
            'store_id' => $store_id,
            'date' => $date,
            'time' => $time,
            'remark' => $remark,
            'reserve_id' => $reserve_id,
        ]);

        return $this->response($res);
    }

    /**
     * 预约列表
     */
    public function lists()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            return $this->response($token);
        }
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $search_text = $this->params['search_text'] ?? '';
        $reserve_state = $this->params['reserve_state'] ?? 'all';

        $condition = [
            [ 'noy.site_id', '=', $this->site_id ],
            [ 'noy.member_id', '=', $this->member_id ],
        ];
        if ($reserve_state != 'all') {
            $condition[] = [ 'noy.reserve_state', '=', $reserve_state ];
        }
        if (!empty($search_text)) {
            $condition[] = [ 'noy.reserve_item', 'like', '%' . $search_text . '%' ];
        }

        $field = 'noy.store_id, noy.member_id, noy.remark, noy.reserve_id, noy.reserve_name, noy.reserve_state_name, noy.reserve_state, noy.reserve_time, noy.reserve_item, noy.create_time, noy.source, nm.headimg, nm.nickname, nm.mobile, os.store_name';
        $reserve_model = new ReserveModel();
        $list = $reserve_model->getReservePageList($condition, $page, $page_size, 'noy.create_time desc', $field);
        foreach ($list[ 'data' ][ 'list' ] as $k => $v) {

            $list[ 'data' ][ 'list' ][ $k ][ 'item' ] = $reserve_model->getReserveItemList([
                [
                    'oyi.reserve_id', '=', $v[ 'reserve_id' ]
                ]
            ], 'g.goods_name,sku.service_length,g.goods_id,g.sku_id,g.price,ys.username,oyi.reserve_user_id,sku.sku_image,sku.sku_images', 'reserve_item_id desc', 'oyi',
                [
                    [ 'goods g', 'g.sku_id = oyi.reserve_goods_sku_id', 'right' ],
                    [ 'goods_sku sku', 'sku.sku_id = oyi.reserve_goods_sku_id', 'right' ],
                    [ 'user ys', 'oyi.reserve_user_id = ys.uid', 'left' ]
                ])[ 'data' ];
        }
        return $this->response($list);
    }

    /**
     * 预约设置
     * @return mixed
     */
    public function getConfig()
    {
        $model = new ReserveModel();
        $store_id = $this->params['store_id'] ?? 0;
        $config = $model->getReserveConfig($this->site_id, $store_id);
        return $this->response($config);
    }

    /**
     * 取消预约
     * @return array
     */
    public function cancel()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            return $this->response($token);
        }
        $reserve_id = $this->params[ 'reserve_id' ] ?? 0;
        $reserve_model = new ReserveModel();
        $res = $reserve_model->cancelReserve($reserve_id, $this->site_id, $this->member_id);

        return $this->response($res);
    }

    /**
     * 删除预约
     * @return array
     */
    public function deleteReserve()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            return $this->response($token);
        }
        $reserve_id = $this->params[ 'reserve_id' ] ?? 0;
        $reserve_model = new ReserveModel();
        $res = $reserve_model->deleteReserve($reserve_id, $this->site_id, $this->member_id);
        return $this->response($res);

    }

    /**
     * 预约详情
     * @return mixed|void
     */
    public function detail()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) {
            return $this->response($token);
        }
        $reserve_id = $this->params[ 'reserve_id' ] ?? 0;

        $model = new ReserveModel();

        $info = $model->getReserveInfo([
            [ 'oy.reserve_id', '=', $reserve_id ],
            [ 'oy.site_id', '=', $this->site_id ],
            [ 'oy.member_id', '=', $this->member_id ],
        ], 'oy.*, nm.headimg, nm.nickname, nm.mobile,os.store_name, os.longitude,os.latitude,os.province_id,os.city_id,os.district_id,os.community_id,os.address,os.full_address', 'oy', [
            [ 'member nm', 'oy.member_id = nm.member_id', 'left' ],
            [ 'store os', 'oy.store_id = os.store_id', 'left' ]
        ])[ 'data' ];

        if (empty($info)) {
            return $this->response($this->error('', '未获取到预约信息'));
        }

        $info[ 'item' ] = $model->getReserveItemList([
            [
                'oyi.reserve_id', '=', $reserve_id
            ]
        ], 'g.goods_name,sku.service_length,g.goods_id,g.sku_id,g.price,ys.username,oyi.reserve_user_id,sku.sku_image', 'reserve_item_id desc', 'oyi',
            [
                [ 'goods g', 'g.sku_id = oyi.reserve_goods_sku_id', 'right' ],
                [ 'goods_sku sku', 'sku.sku_id = oyi.reserve_goods_sku_id', 'right' ],
                [ ' user ys', 'oyi.reserve_user_id = ys.uid', 'left' ]
            ])[ 'data' ];

        return $this->response($this->success($info));
    }

    /**
     * 员工管理
     * @return mixed
     */
    public function servicer()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $page_index = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $search_text = $this->params['search_text'] ?? '';
        $store_id = $this->params['store_id'] ?? '';
        $condition = [
            [ 'u.site_id', '=', $this->site_id ],
        ];
        $condition[] = [ 'ug.store_id', '=', $store_id ];

        if (!empty($search_text)) {
            $condition[] = [ 'u.username', 'like', "%{$search_text}%" ];
        }

        $user_model = new UserGroup();
        $result = $user_model->getUserPageList($condition, $page_index, $page_size, 'u.uid desc', 'u.username,u.status,u.uid', 'ug', [
            [ 'user u', 'ug.uid=u.uid', 'left' ]
        ]);

        return $this->response($result);
    }

    /**
     * 查询所有员工
     * @return mixed
     */
    public function servicerList()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $search_text = $this->params['search_text'] ?? '';
        $store_id = $this->params['store_id'] ?? '';
        $condition = [
            [ 'u.site_id', '=', $this->site_id ],
        ];
        $condition[] = [ 'ug.store_id', '=', $store_id ];

        if (!empty($search_text)) {
            $condition[] = [ 'u.username', 'like', "%{$search_text}%" ];
        }

        $user_model = new UserGroup();
        $result = $user_model->getUserList($condition, 'u.username,u.status,u.uid,u.group_name', 'u.uid desc', 'ug', [
            [ 'user u', 'ug.uid=u.uid', 'left' ]
        ]);

        return $this->response($result);
    }

    /**
     * 预约时间设置
     * @return mixed
     */
    public function getTimeConfig()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $model = new ReserveModel();
        $store_id = $this->params['store_id'] ?? '';
        $config = $model->getReserveConfig($this->site_id, $store_id);
        $time = strtotime(date('Y-m-d'));
        if ($config[ 'data' ][ 'value' ][ 'interval' ] == 30) $config[ 'data' ][ 'value' ][ 'interval' ] = "0.5";
        if ($config[ 'data' ][ 'value' ][ 'interval' ] == 60) $config[ 'data' ][ 'value' ][ 'interval' ] = "1";
        if ($config[ 'data' ][ 'value' ][ 'interval' ] == 90) $config[ 'data' ][ 'value' ][ 'interval' ] = "1.5";
        if ($config[ 'data' ][ 'value' ][ 'interval' ] == 120) $config[ 'data' ][ 'value' ][ 'interval' ] = "2";
        $config[ 'data' ][ 'value' ][ 'start_time' ] = time_to_date($time + $config[ 'data' ][ 'value' ][ 'start' ], "H:i");
        $config[ 'data' ][ 'value' ][ 'end_time' ] = time_to_date($time + $config[ 'data' ][ 'value' ][ 'end' ], "H:i");
        return $this->response($config);
    }
}