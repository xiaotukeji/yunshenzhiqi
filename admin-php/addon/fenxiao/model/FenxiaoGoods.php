<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\model;

use addon\fenxiao\model\Fenxiao as FenxiaoModel;
use app\model\BaseModel;
use app\model\goods\Goods as GoodsModel;

/**
 * 分销商品  addon\fenxiao\model\FenxiaoGoods
 */
class FenxiaoGoods extends BaseModel
{

    /**
     * 修改分销商品
     * @param $data
     * @param $condition
     * @return array
     */
    public function editGoodsFenxiao($data, $condition)
    {
        $re = model('goods')->update($data, $condition);
        return $this->success($re);
    }

    /**
     * 修改分销状态
     * @param $goods_id
     * @param $is_fenxiao
     * @param $site_id
     * @return array
     */
    public function modifyGoodsFenxiaoStatus($goods_id, $is_fenxiao, $site_id)
    {
        $fenxiao_goods_skus = model('fenxiao_goods_sku')->getList([['goods_id', '=', $goods_id]]);
        model('goods')->startTrans();
        try {
            if (empty($fenxiao_goods_skus)) {
                $level_list = model('fenxiao_level')->getList([['site_id', '=', $site_id], ['status', '=', 1]]);
                $goods_model = new GoodsModel();
                $goods_info = $goods_model->getGoodsDetail($goods_id);
                $fenxiao_goods_sku_data = [];
                foreach ($level_list as $level) {
                    foreach ($goods_info['data']['sku_data'] as $sku) {
                        $fenxiao_sku = [
                            'goods_id' => $goods_id,
                            'level_id' => $level['level_id'],
                            'sku_id' => $sku['sku_id'],
                            'one_rate' => $level['one_rate'],
                            'one_money' => 0,
                            'two_rate' => $level['two_rate'],
                            'two_money' => 0,
                            'three_rate' => $level['three_rate'],
                            'three_money' => 0,
                        ];
                        $fenxiao_goods_sku_data[] = $fenxiao_sku;
                    }
                }
                model('fenxiao_goods_sku')->addList($fenxiao_goods_sku_data);
            }

            model('goods')->update(['is_fenxiao' => $is_fenxiao], [['goods_id', '=', $goods_id], ['site_id', '=', $site_id]]);
            model('goods')->commit();
            return $this->success(1);
        } catch (\Exception $e) {
            model('goods')->rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 取消参与分销
     * @param $goods_ids
     * @param $site_id
     * @return array
     */
    public function modifyGoodsIsFenxiao($goods_ids, $is_fenxiao, $site_id)
    {
        $res = model('goods')->update(['is_fenxiao' => $is_fenxiao], [['goods_id', 'in', $goods_ids], ['site_id', '=', $site_id]]);
        return $this->success($res);
    }

    /**
     * 查询商品分销详情
     * @param $goods_sku_detail_array
     */
    public function getGoodsFenxiaoDetailInApi($goods_sku_detail_array, $member_id, $site_id, $discount_price = 0)
    {
        $config = new Config();
        $words_config = $config->getFenxiaoWordsConfig($site_id)['data']['value'];
        $basic_config = $config->getFenxiaoBasicsConfig($site_id)['data']['value'];
        $data = [
            'words_account' => $words_config['account'],
            'commission_money' => 0.00,
            'is_commission_money' => $basic_config['is_commission_money'],
        ];
        $goods_sku_detail_array['goods_sku_detail']['fenxiao_detail'] = $data;
        //检测当前用户是否是分销商以及分销商等级
        $member_info = $goods_sku_detail_array['member_info'];
        $goods_sku_detail = $goods_sku_detail_array['goods_sku_detail'];
        if ($member_info['is_fenxiao'] == 0 || $goods_sku_detail['is_fenxiao'] == 0) {

            return $goods_sku_detail_array;
        }
        //查询分销商等级
        $fenxiao_model = new FenxiaoModel();
        $fenxiao_info = $fenxiao_model->getFenxiaoInfo([['member_id', '=', $member_id]], 'fenxiao_id,level_id')['data'];
        if (empty($fenxiao_info)) return $goods_sku_detail_array;
        // $discount_price = $goods_sku_detail['fenxiao_price'] > 0 ? $goods_sku_detail['fenxiao_price'] : $goods_sku_detail['discount_price'];
        if ($goods_sku_detail['fenxiao_price'] > 0) {
            $discount_price = $goods_sku_detail['fenxiao_price'];
        } else {
            if ($discount_price == 0) {
                if (!empty($goods_sku_detail['member_price'])) {
                    $discount_price = min($goods_sku_detail['member_price'], $goods_sku_detail['discount_price']);
                } else {
                    $discount_price = $goods_sku_detail['discount_price'];
                }
            } else {
                $discount_price = $discount_price;  // 特殊营销活动直接指定计算价格
            }
        }
        if ($goods_sku_detail['fenxiao_type'] == 1) {
            $fenxiao_level = new FenxiaoLevel();
            $level_info = $fenxiao_level->getLevelInfo([['level_id', '=', $fenxiao_info['level_id']]], 'one_rate')['data'];
            if (!empty($level_info)) {
                $data['commission_money'] = number_format($discount_price * $level_info['one_rate'] / 100, 2, '.', '');
            }
        } else {
            $fenxiao_sku_info = model('fenxiao_goods_sku')->getInfo([['level_id', '=', $fenxiao_info['level_id']], ['sku_id', '=', $goods_sku_detail['sku_id']]], 'one_money, one_rate');
            if (!empty($fenxiao_sku_info)) {
                $data['commission_money'] = $fenxiao_sku_info['one_money'];
                if ($fenxiao_sku_info['one_rate'] > 0) {
                    $data['commission_money'] = number_format($discount_price * $fenxiao_sku_info['one_rate'] / 100, 2, '.', '');
                }
            }
        }
        $goods_sku_detail_array['goods_sku_detail']['fenxiao_detail'] = $data;
        return $goods_sku_detail_array;
    }

    /**
     * 查询商品规格列表分销信息
     * @param $goods_sku_list
     * @param $member_id
     * @param $site_id
     * @return mixed
     */
    public function getGoodsSkuListFenxiaoInApi($goods_sku_list, $member_id, $site_id)
    {
        $config = new Config();
        $words_config = $config->getFenxiaoWordsConfig($site_id)['data']['value'];
        $basic_config = $config->getFenxiaoBasicsConfig($site_id)['data']['value'];
        $data = [
            'words_account' => $words_config['account'],
            'commission_money' => 0.00,
            'is_commission_money' => $basic_config['is_commission_money'],
        ];
        //组装数据
        foreach ($goods_sku_list as $k => $v) {
            $goods_sku_list[$k]['fenxiao_detail'] = $data;
        }
        //检测当前用户是否是分销商以及分销商等级
        $goods_sku_info = $goods_sku_list[0];
        $member_info = $goods_sku_list[0]['member_info'];
        if ($member_info['is_fenxiao'] == 0 || $goods_sku_info['is_fenxiao'] == 0) {
            return $goods_sku_list;
        }
        //查询分销商等级
        $fenxiao_model = new FenxiaoModel();
        $fenxiao_info = $fenxiao_model->getFenxiaoInfo([['member_id', '=', $member_id]], 'fenxiao_id,level_id')['data'];
        if (empty($fenxiao_info)) return $goods_sku_list;

        if ($goods_sku_info['fenxiao_type'] == 1) {
            $fenxiao_level = new FenxiaoLevel();
            $level_info = $fenxiao_level->getLevelInfo([['level_id', '=', $fenxiao_info['level_id']]], 'one_rate')['data'];
            if (!empty($level_info)) {
                foreach ($goods_sku_list as $k => $v) {

                    if ($v['fenxiao_price'] > 0) {
                        $discount_price = $v['fenxiao_price'];
                    } else {
                        if (!empty($v['member_price'])) {
                            $discount_price = min($v['member_price'], $v['discount_price']);
                        } else {
                            $discount_price = $v['discount_price'];
                        }
                    }
                    // $discount_price = $v['fenxiao_price'] > 0 ? $v['fenxiao_price'] : $v['discount_price'];
                    $goods_sku_list[$k]['fenxiao_detail']['commission_money'] = number_format($discount_price * $level_info['one_rate'] / 100, 2, '.', '');
                }
            }
        } else {
            $fenxiao_sku_list = model('fenxiao_goods_sku')->getList([['level_id', '=', $fenxiao_info['level_id']], ['goods_id', '=', $goods_sku_info['goods_id']]], 'sku_id, one_money, one_rate');
            $fenxiao_sku_list = array_column($fenxiao_sku_list, null, 'sku_id');
            foreach ($goods_sku_list as $k => $v) {
                $fenxiao_sku_info = $fenxiao_sku_list[$v['sku_id']] ?? [];
                if (!empty($fenxiao_sku_info)) {
                    $discount_price = $v['fenxiao_price'] > 0 ? $v['fenxiao_price'] : $v['discount_price'];
                    $goods_sku_list[$k]['fenxiao_detail']['commission_money'] = $fenxiao_sku_info['one_money'];
                    if ($fenxiao_sku_info['one_rate'] > 0) {
                        $goods_sku_list[$k]['fenxiao_detail']['commission_money'] = number_format($discount_price * $fenxiao_sku_info['one_rate'] / 100, 2, '.', '');
                    }
                }
            }

        }
        return $goods_sku_list;
    }
}