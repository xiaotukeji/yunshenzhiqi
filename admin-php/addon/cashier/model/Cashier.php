<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\cashier\model;

use app\dict\order_refund\OrderRefundDict;
use app\model\BaseModel;
use app\model\system\Config;
use Exception;
use ZipArchive;
use think\facade\Db;

/**
 * @author Administrator
 */
class Cashier extends BaseModel
{
    private $path = 'addon/cashier/source';

    /**
     * 交接班
     * @param $user_info
     * @param $site_id
     * @param $store_id
     * @return array
     */
    public function changeShifts($user_info, $site_id, $store_id)
    {
        $data = $this->getShiftsData($site_id, $store_id);
        $data = array_merge($data, [
            'site_id' => $site_id,
            'store_id' => $store_id,
            'uid' => $user_info[ 'uid' ]
        ]);
        $id = model('change_shifts_record')->add($data);
        if ($id) {
            return $this->success($id);
        } else {
            return $this->error('', '交接班数据添加失败！');
        }
    }

    /**
     * 查询班次内数据
     * @param $site_id
     * @param $store_id
     * @return array
     */
    public function getShiftsData($site_id, $store_id)
    {
        $last_info = model('change_shifts_record')->getFirstData([ [ 'site_id', '=', $site_id ], [ 'store_id', '=', $store_id ] ], 'end_time', 'end_time desc');
        $start_time = empty($last_info) ? 0 : $last_info[ 'end_time' ];
        $end_time = time();

        $order_condition = [
            [ 'site_id', '=', $site_id ],
            [ 'store_id', '=', $store_id ],
            [ 'order_scene', '=', 'cashier' ],
            [ 'order_status', '=', 10 ],
            [ 'finish_time', 'between', [ $start_time, $end_time ] ]
        ];
        $order_condition[ 5 ] = [ 'cashier_order_type', '=', 'goods' ];
        $billing_stat = model('order')->getInfo($order_condition, 'count(order_id) as num, ifnull(sum(pay_money), 0) as pay_money');

        $order_condition[ 5 ] = [ 'cashier_order_type', '=', 'card' ];
        $card_stat = model('order')->getInfo($order_condition, 'count(order_id) as num, ifnull(sum(pay_money), 0) as pay_money');

        $order_condition[ 5 ] = [ 'cashier_order_type', '=', 'recharge' ];
        $recharge_stat = model('order')->getInfo($order_condition, 'count(order_id) as num, ifnull(sum(pay_money), 0) as pay_money');

        $order_condition[ 5 ] = [ 'pay_type', '=', 'cash' ];
        $cash = model('order')->getInfo($order_condition, 'count(order_id) as num, ifnull(sum(pay_money), 0) as pay_money');

        $order_condition[ 5 ] = [ 'pay_type', '=', 'alipay' ];
        $alipay = model('order')->getInfo($order_condition, 'count(order_id) as num, ifnull(sum(pay_money), 0) as pay_money');

        $order_condition[ 5 ] = [ 'pay_type', '=', 'wechatpay' ];
        $wechatpay = model('order')->getInfo($order_condition, 'count(order_id) as num, ifnull(sum(pay_money), 0) as pay_money');

        $order_condition[ 5 ] = [ 'pay_type', '=', 'own_wechatpay' ];
        $own_wechatpay = model('order')->getInfo($order_condition, 'count(order_id) as num, ifnull(sum(pay_money), 0) as pay_money');

        $order_condition[ 5 ] = [ 'pay_type', '=', 'own_alipay' ];
        $own_alipay = model('order')->getInfo($order_condition, 'count(order_id) as num, ifnull(sum(pay_money), 0) as pay_money');

        $order_condition[ 5 ] = [ 'pay_type', '=', 'own_pos' ];
        $own_pos = model('order')->getInfo($order_condition, 'count(order_id) as num, ifnull(sum(pay_money), 0) as pay_money');

        $refund_condition = [
            [ 'o.site_id', '=', $site_id ],
            [ 'o.store_id', '=', $store_id ],
            [ 'o.order_scene', '=', 'cashier' ],
            [ 'og.refund_status', '=', OrderRefundDict::REFUND_COMPLETE ],
            [ 'og.refund_time', 'between', [ $start_time, $end_time ] ]
        ];
        $refund_stat = model('order_goods')->getInfo($refund_condition, 'count(og.order_goods_id) as num, ifnull(sum(refund_pay_money), 0) as money', 'og', [
            [ 'order o', 'o.order_id = og.order_id', 'inner' ]
        ]);

        $sale_goods_count = $this->getSaleGoodsCount([
            ['o.store_id', '=', $store_id],
            ['o.pay_time', '>', $start_time],
            ['o.pay_time', '<=', $end_time],
        ])['data'];

        return [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'billing_count' => $billing_stat[ 'num' ],
            'billing_money' => $billing_stat[ 'pay_money' ],
            'buycard_count' => $card_stat[ 'num' ],
            'buycard_money' => $card_stat[ 'pay_money' ],
            'recharge_count' => $recharge_stat[ 'num' ],
            'recharge_money' => $recharge_stat[ 'pay_money' ],
            'refund_count' => $refund_stat[ 'num' ],
            'refund_money' => $refund_stat[ 'money' ],
            'cash_count' => $cash[ 'num' ],
            'cash' => $cash[ 'pay_money' ],
            'alipay_count' => $alipay[ 'num' ],
            'alipay' => $alipay[ 'pay_money' ],
            'wechatpay_count' => $wechatpay[ 'num' ],
            'wechatpay' => $wechatpay[ 'pay_money' ],
            'own_wechatpay_count' => $own_wechatpay[ 'num' ],
            'own_wechatpay' => $own_wechatpay[ 'pay_money' ],
            'own_alipay_count' => $own_alipay[ 'num' ],
            'own_alipay' => $own_alipay[ 'pay_money' ],
            'own_pos_count' => $own_pos[ 'num' ],
            'own_pos' => $own_pos[ 'pay_money' ],
            'sale_goods_count' => $sale_goods_count,
        ];
    }

    /**
     * 查询交班记录
     * @param array $condition
     * @param bool $field
     * @param string $order
     * @param int $page
     * @param int $list_rows
     * @param string $alias
     * @param array $join
     * @return array
     */
    public function getchangeShiftsPageList($condition = [], $field = true, $order = '', $page = 1, $list_rows = PAGE_LIST_ROWS, $alias = 'a', $join = [])
    {
        $data = model('change_shifts_record')->pageList($condition, $field, $order, $page, $list_rows, $alias, $join);
        return $this->success($data);
    }

    /**
     * 查询交班信息记录
     * @param array $condition
     * @param bool $field
     * @return array
     */
    public function getChangeShiftsRecordInfo($condition = [], $field = true, $alias = 'a', $join = null)
    {
        $data = model('change_shifts_record')->getInfo($condition, $field, $alias, $join);
        return $this->success($data);
    }

    /**
     * 刷新收银端
     * @return array
     */
    public function refreshCashier()
    {
        try {

            $path = $this->path . '/default';
            $cashier_path = 'cashregister'; // 收银端生成目录
            $config_path = 'cashregister/static/js'; // 收银模板文件目录
            if (!is_dir($path) || count(scandir($path)) <= 3) {
                return $this->error('', '未找到源码包，请检查目录文件！');
            }

            if (is_dir($cashier_path)) {
                // 先将之前的文件删除
                if (count(scandir($cashier_path)) > 1) deleteDir($cashier_path);
            } else {
                // 创建收银目录
                mkdir($cashier_path, intval('0777', 8), true);
            }

            // 将原代码包拷贝到收银目录下
            recurseCopy($path, $cashier_path);
            $this->copyFile($config_path);
            file_put_contents($cashier_path . '/refresh.log', time());
            return $this->success();
        } catch (Exception $e) {
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 替换配置信息，API请求域名地址、图片、地图密钥等
     * @param $source_path
     * @param string $domain
     */
    private function copyFile($source_path, $domain = __ROOT__)
    {
        $files = scandir($source_path);
        foreach ($files as $path) {
            if ($path != '.' && $path != '..') {
                $temp_path = $source_path . '/' . $path;
                if (file_exists($temp_path)) {
                    if (preg_match('/(index.)(\w{8})(.js)$/', $temp_path)) {
                        $content = file_get_contents($temp_path);
                        $content = $this->paramReplace($content, $domain);
                        file_put_contents($temp_path, $content);
                    }
                }
            }
        }
    }

    /**
     * 参数替换
     * @param $string
     * @param string $domain
     * @return string|string[]|null
     */
    private function paramReplace($string, $domain = __ROOT__)
    {
        $patterns = [
            '/\{\{\$baseUrl\}\}/',
            '/\{\{\$imgDomain\}\}/',
            '/\{\{\$webSocket\}\}/'
        ];
        $socket_url = (strstr(__ROOT__, 'https://') === false ? str_replace('http', 'ws', __ROOT__) : str_replace('https', 'wss', __ROOT__)) . '/wss';
        $replacements = [
            $domain,
            $domain,
            $socket_url,
        ];
        return preg_replace($patterns, $replacements, $string);
    }

    /**
     * 下载收银端uni-app源码
     * @return array
     */
    public function downloadOs()
    {
        try {
            $source_file_path = $this->path . '/os';
            if (!is_dir($source_file_path) || count(scandir($source_file_path)) <= 3) {
                return $this->error('', '未找到源码包，请检查目录文件！');
            }
            $file_arr = getFileMap($source_file_path);

            if (!empty($file_arr)) {
                $zipname = 'cashier_os_' . date('YmdHi') . '.zip';
                $zip = new ZipArchive();
                $res = $zip->open($zipname, ZipArchive::CREATE);
                if ($res === TRUE) {
                    foreach ($file_arr as $file_path => $file_name) {
                        if (is_dir($file_path)) {
                            $file_path = str_replace($source_file_path . '/', '', $file_path);
                            $zip->addEmptyDir($file_path);
                        } else {
                            $zip_path = str_replace($source_file_path . '/', '', $file_path);
                            $zip->addFile($file_path, $zip_path);
                        }
                    }
                    $zip->close();

                    header('Content-Type: application/zip');
                    header('Content-Transfer-Encoding: Binary');
                    header('Content-Length: ' . filesize($zipname));
                    header("Content-Disposition: attachment; filename=\"" . basename($zipname) . "\"");
                    readfile($zipname);
                    @unlink($zipname);
                }
            }
            return $this->success();
        } catch (Exception $e) {
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 获取收银台收款设置
     * @param int $site_id
     * @param int $store_id
     * @return array
     */
    public function getCashierCollectMoneyConfig(int $site_id, int $store_id)
    {
        $config = (new Config())->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'CASHIER_COLLECT_MONEY_CONFIG_' . $store_id ] ])[ 'data' ][ 'value' ];
        if (empty($config)) {
            $config = [
                'reduction' => 1,
                'point' => 1,
                'balance' => 1,
                'balance_safe' => 0,
                'sms_verify' => 0,
                'pay_type' => [ 'third', 'cash', 'own_wechatpay', 'own_alipay', 'own_pos' ]
            ];
        }
        return $this->success($config);
    }

    /**
     * 收银台收款设置
     * @param int $site_id
     * @param int $store_id
     * @param array $config
     * @return array
     */
    public function setCashierCollectMoneyConfig(int $site_id, int $store_id, array $config)
    {
        return (new Config())->setConfig($config, '收银端收款设置', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', 'shop' ], [ 'config_key', '=', 'CASHIER_COLLECT_MONEY_CONFIG_' . $store_id ] ]);
    }

    /**
     * 设置收银台主题风格配置
     * @param $data
     * @param int $site_id
     * @param string $app_module
     * @return array
     */
    public function setThemeConfig($data, $site_id = 1, $app_module = 'shop')
    {
        $config = new Config();
        $res = $config->setConfig($data, '收银台主题风格配置', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', 'CASHIER_THEME_CONFIG' ] ]);
        return $res;
    }

    /**
     * 获取收银台主题风格配置
     * @param int $site_id
     * @param string $app_module
     * @return array
     */
    public function getThemeConfig($site_id = 1, $app_module = 'shop')
    {
        $config = new Config();
        $res = $config->getConfig([
            [ 'site_id', '=', $site_id ],
            [ 'app_module', '=', $app_module ],
            [ 'config_key', '=', 'CASHIER_THEME_CONFIG' ]
        ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = $this->getThemeList()[ 'data' ][ 0 ];
        }
        return $res;
    }

    /**
     * 收银台主题风格列表
     * @return array
     */
    public function getThemeList()
    {
        //  todo 这里支持扩展色调
        $res = [
            [
                'title' => '橙色', // 标题
                'name' => 'orange', // 标识
                'color' => '#FA6400' // 主色调
            ],
            [
                'title' => '紫色',
                'name' => 'purple',
                'color' => '#A253FF'
            ],
            [
                'title' => '粉色',
                'name' => 'pink',
                'color' => '#ff08a7'
            ],
            [
                'title' => '棕色',
                'name' => 'brown',
                'color' => '#CFAF70'
            ],
            [
                'title' => '绿色',
                'name' => 'green',
                'color' => '#19C650'
            ],
            [
                'title' => '蓝色',
                'name' => 'blue',
                'color' => '#105CFB'
            ],
            [
                'title' => '红色',
                'name' => 'red',
                'color' => '#F4391c'
            ]
        ];

        return $this->success($res);
    }

    /**
     * 设置收银台会员搜索方式配置
     * @param $data
     * @param $store_id
     * @param int $site_id
     * @param string $app_module
     * @return array
     */
    public function setMemberSearchWayConfig($data, $store_id, $site_id = 1, $app_module = 'shop')
    {
        $config = new Config();
        $res = $config->setConfig($data, '收银台会员搜索方式配置', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', 'CASHIER_MEMBER_SEARCH_WAY_CONFIG_' . $store_id ] ]);
        return $res;
    }

    /**
     * 获取收银台会员搜索方式配置
     * @param $store_id
     * @param int $site_id
     * @param string $app_module
     * @return array
     */
    public function getMemberSearchWayConfig($store_id, $site_id = 1, $app_module = 'shop')
    {
        $config = new Config();
        $res = $config->getConfig([
            [ 'site_id', '=', $site_id ],
            [ 'app_module', '=', $app_module ],
            [ 'config_key', '=', 'CASHIER_MEMBER_SEARCH_WAY_CONFIG_' . $store_id ]
        ]);

        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'way' => 'exact' // exact：精确搜索，list：列表搜索
            ];
        }
        return $res;
    }

    /**
     * 获取销售统计
     * @param $info
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSaleGoodsCount($condition)
    {
        //分组查询
        $list = Db::name('order_goods')
            ->alias('og')
            ->join('order o', 'o.order_id = og.order_id', 'inner')
            ->field("sum(og.num) as num,og.sku_id,IF(o.order_from = 'cashier', 'offline', 'online') as sale_channel")
            ->where($condition)
            ->group('og.sku_id,sale_channel')
            ->select()
            ->toArray();
        //初始化数据
        $data = [
            'ids' => [],
            'num' => 0,
            'online_ids' => [],
            'online_num' => 0,
            'offline_ids' => [],
            'offline_num' => 0,
        ];
        //汇总数据
        foreach($list as $val){
            $data['ids'][$val['sku_id']] = $val['sku_id'];
            $data['num'] += $val['num'];
            if($val['sale_channel'] == 'online'){
                $data['online_ids'][$val['sku_id']] = $val['sku_id'];
                $data['online_num'] += $val['num'];
            }else{
                $data['offline_ids'][$val['sku_id']] = $val['sku_id'];
                $data['offline_num'] += $val['num'];
            }
        }
        //id数组转换为种类数量
        $data['class_num'] = count($data['ids']);
        unset($data['ids']);
        $data['online_class_num'] = count($data['online_ids']);
        unset($data['online_ids']);
        $data['offline_class_num'] = count($data['offline_ids']);
        unset($data['offline_ids']);

        return $this->success($data);
    }
}