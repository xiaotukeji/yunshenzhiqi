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

namespace addon\store\storeapi\controller;

use addon\store\model\Reserve as ReserveModel;
use app\dict\goods\GoodsDict;
use app\model\goods\Goods as GoodsModel;
use app\model\member\Member as MemberModel;
use app\model\system\UserGroup;
use app\storeapi\controller\BaseStoreApi;

class Reserve extends BaseStoreApi
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
     * 预约记录（按周）
     * @return false|string
     */
    public function getReserveByWeek()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $reserve_model = new ReserveModel();

        $length = $this->params[ 'length' ] ?? 0;
        $data = $this->getWeekDay();

        foreach ($data as $wk => $w_item) {
            $field = 'noy.reserve_id,noy.reserve_state,noy.reserve_time,nm.nickname';
            $list = $reserve_model->getReservePageList([
                [ 'noy.site_id', '=', $this->site_id ],
                [ 'noy.reserve_time', 'between', [ $w_item[ 'start' ], $w_item[ 'end' ] ] ]
            ], 1, PAGE_LIST_ROWS, 'noy.create_time desc', $field);
            if (!empty($list[ 'data' ][ 'list' ])) {
                foreach ($list[ 'data' ][ 'list' ] as $k => $item) {
                    $list[ 'data' ][ 'list' ][ $k ][ 'item' ] = $reserve_model->getReserveItemList([
                        [
                            'oyi.reserve_id', '=', $item[ 'reserve_id' ]
                        ]
                    ], 'g.goods_name,g.goods_id,g.sku_id', 'reserve_item_id desc', 'oyi',
                        [ [ 'goods g', 'g.sku_id = oyi.reserve_goods_sku_id', 'right' ] ])[ 'data' ];
                }
            }
            $data[ $wk ][ 'data' ] = $list[ 'data' ];
        }

        return $this->response($this->success($data));
    }

    /**
     * 添加预约
     * @return mixed
     */
    public function add()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $reserve_model = new ReserveModel();
        $data = $reserve_model->addReserve([
            'site_id' => $this->site_id,
            'app_module' => $this->app_module,
            'member_id' => $this->params[ 'member_id' ] ?? 0,
            'goods' => isset($this->params[ 'goods' ]) ? json_decode($this->params[ 'goods' ], true) : [],
            'store_id' => $this->store_id,
            'date' => $this->params[ 'date' ] ?? '',
            'time' => $this->params[ 'time' ] ?? '',
            'remark' => $this->params[ 'desc' ] ?? '',
            'source' => 'store'
        ]);
        return $this->response($data);
    }

    /**
     * 修改预约
     * @return mixed
     */
    public function update()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $reserve_model = new ReserveModel();
        $res = $reserve_model->editReserve([
            'site_id' => $this->site_id,
            'app_module' => $this->app_module,
            'reserve_id' => $this->params[ 'reserve_id' ] ?? 0,
            'goods' => isset($this->params[ 'goods' ]) ? json_decode($this->params[ 'goods' ], true) : [],
            'date' => $this->params[ 'date' ] ?? '',
            'time' => $this->params[ 'time' ] ?? '',
            'remark' => $this->params[ 'desc' ] ?? '',
        ]);
        return $this->response($res);
    }

    /**
     * 预约配置
     */
    public function getConfig()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $reserve_model = new ReserveModel();
        $config = $reserve_model->getReserveConfig($this->site_id, $this->store_id);
        return $this->response($this->success($config[ 'data' ][ 'value' ]));
    }

    /**
     * 预约配置
     */
    public function setConfig()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $week = $this->params[ 'week' ] ?? '';
        $data = [
            'week' => json_encode(explode(',', $week)),
            'start' => $this->params[ 'start' ] ?? 32400,
            'end' => $this->params[ 'end' ] ?? 79200,
            'interval' => $this->params[ 'interval' ] ?? 30,
            'advance' => $this->params[ 'advance' ] ?? 1,
            'max' => $this->params[ 'max' ] ?? 1,
        ];
        $reserve_model = new ReserveModel();
        $res = $reserve_model->setReserveConfig($data, $this->site_id, $this->store_id);
        return $this->response($res);
    }

    /**
     * 预约列表
     */
    public function lists()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $page = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;
        $search_text = $this->params['search_text'] ?? '';
        $reserve_state = $this->params['reserve_state'] ?? 'all';
        $start = $this->params['start'] ?? 0;
        $end = $this->params['end'] ?? 0;

        $condition = [
            [ 'noy.site_id', '=', $this->site_id ],
            [ 'noy.store_id', '=', $this->store_id ],
        ];
        if ($reserve_state != 'all') {
            $condition[] = [ 'noy.reserve_state', '=', $reserve_state ];
        }
        if (!empty($search_text)) {
            $condition[] = [ 'nm.mobile|nm.nickname', 'like', '%' . $search_text . '%' ];
        }
        if ($start && $end) {
            $condition[] = [ 'noy.reserve_time', 'between', [ $start, $end ] ];
        } else {
            if ($start && !$end) {
                $condition[] = [ 'noy.reserve_time', '>=', $start ];
            } else {
                if (!$start && $end) {
                    $condition[] = [ 'noy.reserve_time', '<=', $end ];
                }
            }
        }

        $field = 'noy.member_id, noy.remark, noy.reserve_id, noy.reserve_name, noy.reserve_state_name, noy.reserve_state, noy.reserve_time, noy.create_time, nm.headimg, nm.nickname, nm.mobile, nm.headimg';
        $reserve_model = new ReserveModel();
        $result = $reserve_model->getReservePageList($condition, $page, $page_size, 'noy.create_time desc', $field);
        if (!empty($result[ 'data' ][ 'list' ])) {
            foreach ($result[ 'data' ][ 'list' ] as $k => $item) {
                $result[ 'data' ][ 'list' ][ $k ][ 'item' ] = $reserve_model->getReserveItemList([
                    [
                        'oyi.reserve_id', '=', $item[ 'reserve_id' ]
                    ]
                ], 'g.goods_name,g.goods_id,g.sku_id', 'reserve_item_id desc', 'oyi',
                    [ [ 'goods g', 'g.sku_id = oyi.reserve_goods_sku_id', 'right' ] ])[ 'data' ];
            }
        }

        return $this->response($result);
    }

    /**
     * 员工管理
     * @return mixed
     */
    public function servicer()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $store_id = $this->store_id;
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
     * 获取一周内的时间段
     * @return false|string
     */
    public function getWeekDay()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $length = $this->params[ 'length' ] ?? 0;

        $first_day = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
        $first_day = strtotime($length . ' week', $first_day);

        $week = [ '周日', '周一', '周二', '周三', '周四', '周五', '周六' ];
        $data = [];
        for ($i = 0; $i < 7; $i++) {
            $time = strtotime("+ {$i} day", $first_day);
            $data[] = [
                'start' => $time,
                'end' => strtotime(date('Y-m-d 23:59:59', $time)),
                'date' => date('m/d', $time),
                'w' => date('w', $time),
                'week' => $week[date('w', $time)],
                'currday' => date('Y-m-d', $time) == date('Y-m-d') ? 1 : 0
            ];
        }
        return $this->response($this->success($data));
    }

    /**
     * 确认预约
     * @return false|string
     */
    public function confirm()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $reserve_id = $this->params['reserve_id'] ?? 0;
        $reserve_model = new ReserveModel();
        $res = $reserve_model->confirmReserve($reserve_id, $this->site_id);

        return $this->response($res);
    }

    /**
     * 取消预约
     * @return false|string
     */
    public function cancel()
    {

        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $reserve_id = $this->params['reserve_id'] ?? 0;
        $reserve_model = new ReserveModel();
        $res = $reserve_model->cancelReserve($reserve_id, $this->site_id);

        return $this->response($res);
    }

    /**
     * 确认到店
     * @return false|string
     */
    public function confirmToStore()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $reserve_id = $this->params['reserve_id'] ?? 0;
        $reserve_model = new ReserveModel();
        $res = $reserve_model->confirmToStore($reserve_id, $this->site_id);
        return $this->response($res);
    }

    /**
     * 确认完成
     * @return false|string
     */
    public function complete()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $reserve_id = $this->params['reserve_id'] ?? 0;

        $reserve_model = new ReserveModel();
        $res = $reserve_model->confirmComplete($reserve_id, $this->site_id);
        return $this->response($res);
    }

    /**
     * 服务列表
     * @return false|string
     */
    public function serviceList()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);

        $page_index = $this->params['page'] ?? 1;
        $page_size = $this->params['page_size'] ?? PAGE_LIST_ROWS;

        $goods = new GoodsModel();
        $condition = [];
        $condition[] = [ 'gs.site_id', '=', $this->site_id ];
        $condition[] = [ 'g.goods_class', '=', GoodsDict::service ];
        $condition[] = [ 'g.goods_state', '=', 1 ];
        $condition[] = [ 'g.is_delete', '=', 0 ];
        $alias = 'gs';

        $field = 'gs.is_consume_discount,gs.discount_config,gs.discount_method,gs.member_price,gs.goods_id,gs.sort,gs.sku_id,gs.sku_name,gs.price,gs.market_price,gs.discount_price,gs.stock,(g.sale_num + g.virtual_sale) as sale_num,(gs.sale_num + gs.virtual_sale) as sale_sort,gs.sku_image,gs.goods_name,gs.site_id,gs.is_free_shipping,gs.introduction,gs.promotion_type,g.goods_image,g.promotion_addon,gs.is_virtual,g.goods_spec_format,g.recommend_way,gs.max_buy,gs.min_buy,gs.unit,gs.is_limit,gs.limit_type,g.label_name,g.stock_show,g.sale_show,g.market_price_show,g.barrage_show,g.sale_channel,g.sale_store';
        $join = [
            [ 'goods g', 'gs.sku_id = g.sku_id', 'inner' ]
        ];

        $list = $goods->getGoodsSkuPageList($condition, $page_index, $page_size, 'g.sort desc', $field, $alias, $join);

        return $this->response($list);
    }

    /**
     * 预约详情
     * @return mixed|void
     */
    public function detail()
    {
        $token = $this->checkToken();
        if ($token[ 'code' ] < 0) return $this->response($token);
        $reserve_id = $this->params['reserve_id'] ?? 0;

        $model = new ReserveModel();

        $info = $model->getReserveInfo([
            [ 'reserve_id', '=', $reserve_id ],
            [ 'oy.site_id', '=', $this->site_id ]
        ], 'oy.*, nm.headimg, nm.nickname, nm.mobile,os.store_name', 'oy', [
            [ 'member nm', 'oy.member_id = nm.member_id', 'left' ],
            [ 'store os', 'oy.store_id = os.store_id', 'left' ]
        ])[ 'data' ];

        if (empty($info)) return $this->response($this->error('', '缺少必须参数'));

        $info[ 'item' ] = $model->getReserveItemList([
            [
                'oyi.reserve_id', '=', $reserve_id
            ],

        ], 'g.goods_name,g.goods_id,g.sku_id,g.price,sku.service_length,ys.username,oyi.reserve_user_id as uid', 'reserve_item_id desc', 'oyi',
            [ [ 'goods g', 'g.sku_id = oyi.reserve_goods_sku_id', 'right' ], [ 'goods_sku sku', 'sku.sku_id = oyi.reserve_goods_sku_id', 'right' ], [ 'user ys', 'oyi.reserve_user_id = ys.uid', 'left' ] ])[ 'data' ];

        $member_model = new MemberModel();

        $info[ 'member' ] = $member_model->getMemberInfo([ [ 'member_id', '=', $info[ 'member_id' ] ], [ 'site_id', '=', $this->site_id ] ], 'nickname,mobile,member_id')[ 'data' ] ?? [];

        return $this->response($this->success($info));
    }
}
