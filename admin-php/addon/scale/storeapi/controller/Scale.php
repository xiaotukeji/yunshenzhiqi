<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 上海牛之云网络科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\scale\storeapi\controller;

use app\storeapi\controller\BaseStoreApi;
use addon\scale\model\Scale as ScaleModel;

/**
 * 电子秤控制器
 */
class Scale extends BaseStoreApi
{
    /**
     * 获取电子秤列表
     * @return false|string
     */
    public function page()
    {
        $page_index = $this->params[ 'page' ] ?? 1;
        $page_size = $this->params[ 'page_size' ] ?? PAGE_LIST_ROWS;

        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', '=', $this->store_id ],
        ];

        $data = ( new ScaleModel() )->getScalePageList($page_index, $page_size, $condition, 'name,brand,model,brand_name,model_name,status,create_time,scale_id,config,type,network_type');
        return $this->response($data);
    }

    /**
     * 获取详情
     * @return false|string
     */
    public function detail()
    {
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', '=', $this->store_id ],
            [ 'scale_id', '=', $this->params[ 'scale_id' ] ],
        ];

        $data = ( new ScaleModel() )->getScaleInfo($condition, 'scale_id,name,brand_name,model_name,status,create_time,brand,model,config,type,network_type');
        if (!empty($data[ 'data' ])) {
            $data[ 'data' ][ 'config' ] = json_decode($data[ 'data' ][ 'config' ], true);
        }
        return $this->response($data);
    }

    /**
     * 获取收银秤信息
     * @return false|string
     */
    public function cashierScale()
    {
        $condition = [
            [ 'site_id', '=', $this->site_id ],
            [ 'store_id', '=', $this->store_id ],
            [ 'type', '=', 'cashier' ],
        ];

        $data = ( new ScaleModel() )->getScaleInfo($condition, 'scale_id,name,brand_name,model_name,status,create_time,brand,model,config,type,network_type');
        if (!empty($data[ 'data' ])) {
            $data[ 'data' ][ 'config' ] = json_decode($data[ 'data' ][ 'config' ], true);
        }
        return $this->response($data);
    }

    /**
     * 电子秤品牌型号
     * @return false|string
     */
    public function scaleBrand()
    {
        $data = ( new ScaleModel() )->getScaleBrandEnum();
        return $this->response($this->success($data));
    }

    /**
     * 添加电子秤
     * @return false|string
     */
    public function add()
    {
        $data = [
            'site_id' => $this->site_id,
            'store_id' => $this->store_id,
            'name' => $this->params[ 'name' ],
            'status' => $this->params[ 'status' ] ?? 1,
            'brand' => $this->params[ 'brand' ],
            'model' => $this->params[ 'model' ],
            'config' => $this->params[ 'config' ] ?? '{}',
            'type' => $this->params[ 'type' ],
            'network_type' => $this->params[ 'network_type' ]
        ];
        $res = ( new ScaleModel() )->addScale($data);
        return $this->response($res);
    }

    /**
     * 编辑电子秤
     * @return false|string
     */
    public function edit()
    {
        $data = [
            'scale_id' => $this->params[ 'scale_id' ],
            'site_id' => $this->site_id,
            'store_id' => $this->store_id,
            'name' => $this->params[ 'name' ],
            'status' => $this->params[ 'status' ] ?? 1,
            'config' => $this->params[ 'config' ] ?? '{}',
            'type' => $this->params[ 'type' ],
            'network_type' => $this->params[ 'network_type' ]
        ];
        $res = ( new ScaleModel() )->editScale($data);
        return $this->response($res);
    }

    /**
     * 删除电子秤
     * @return false|string
     */
    public function delete()
    {
        $res = ( new ScaleModel() )->deleteScale([ [ 'scale_id', '=', $this->params[ 'scale_id' ] ], [ 'site_id', '=', $this->params[ 'site_id' ] ], [ 'store_id', '=', $this->params[ 'store_id' ] ] ]);
        return $this->response($res);
    }
}