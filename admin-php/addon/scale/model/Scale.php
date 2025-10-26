<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\scale\model;

use app\model\BaseModel;

/**
 * 电子秤
 */
class Scale extends BaseModel
{
    /**
     * 电子秤枚举数据
     * @var array[]
     */
    private $scale_brand_enum = [
        'aclas' => [
            'brand' => 'aclas',
            'brand_name' => '顶尖',
            'model_list' => [
                'OS2G' => [
                    'model' => 'OS2G',
                    'model_name' => 'OS2G'
                ],
                'LS' => [
                    'model' => 'LS',
                    'model_name' => 'LS/LH/LP系列'
                ]
            ]
        ],
        'dahua' => [
            'brand' => 'dahua',
            'brand_name' => '大华',
            'model_list' => [
                'TM' => [
                    'model' => 'TM',
                    'model_name' => 'TM-F系列'
                ]
            ]
        ]
    ];

    /**
     * 获取支持的电子秤品牌型号
     * @return array[]
     */
    public function getScaleBrandEnum()
    {
        return $this->scale_brand_enum;
    }

    /**
     * 添加电子秤
     * @param $params
     * @return array
     */
    public function addScale($params)
    {

        $brand = $this->scale_brand_enum[ $params[ 'brand' ] ] ?? [];
        if (empty($brand)) return $this->error('', '不支持的品牌');

        $model = $brand[ 'model_list' ][ $params[ 'model' ] ] ?? [];
        if (empty($model)) return $this->error('', '不支持的型号');

        $data = [
            'site_id' => $params[ 'site_id' ],
            'store_id' => $params[ 'store_id' ],
            'name' => $params[ 'name' ],
            'brand' => $brand[ 'brand' ],
            'brand_name' => $brand[ 'brand_name' ],
            'model' => $model[ 'model' ],
            'model_name' => $model[ 'model_name' ],
            'config' => $params[ 'config' ],
            'status' => $params[ 'status' ] ?? 1,
            'create_time' => time(),
            'type' => $params[ 'type' ],
            'network_type' => $params[ 'network_type' ]
        ];
        /*if ($params[ 'type' ] == 'cashier') {
            model('scale')->update([ 'type' => 'barcode' ], [ [ 'site_id', '=', $params[ 'site_id' ] ], [ 'store_id', '=', $params[ 'store_id' ] ], [ 'type', '=', 'cashier' ] ]);
        }*/
        $res = model('scale')->add($data);
        return $this->success($res);
    }

    /**
     * 修改电子秤
     * @param $params
     * @return array|int|string
     */
    public function editScale($params)
    {
        $data = [
            'name' => $params[ 'name' ],
            'config' => $params[ 'config' ],
            'status' => $params[ 'status' ] ?? 1,
            'type' => $params[ 'type' ],
            'network_type' => $params[ 'network_type' ]
        ];
        /*if ($params[ 'type' ] == 'cashier') {
            model('scale')->update([ 'type' => 'barcode' ], [ [ 'site_id', '=', $params[ 'site_id' ] ], [ 'store_id', '=', $params[ 'store_id' ] ], [ 'type', '=', 'cashier' ] ]);
        }*/
        $res = model('scale')->update($data, [ [ 'scale_id', '=', $params[ 'scale_id' ] ], [ 'site_id', '=', $params[ 'site_id' ] ], [ 'store_id', '=', $params[ 'store_id' ] ] ]);
        return $this->success($res);
    }

    /**
     * 删除电子秤
     * @param $condition
     * @return array
     */
    public function deleteScale($condition)
    {
        $num = model('scale')->delete($condition);
        return $this->success($num);
    }

    /**
     * 获取电子秤分页列表
     * @param $condition
     * @param $field
     * @param $order
     * @param $limit
     * @return array
     */
    public function getScalePageList($page, $page_size, $condition = [], $field = '*', $order = '')
    {
        $list = model('scale')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 查询电子秤详情
     * @param $condition
     * @param $field
     * @return array
     */
    public function getScaleInfo($condition, $field = '*')
    {
        $data = model('scale')->getInfo($condition, $field);
        return $this->success($data);
    }
}