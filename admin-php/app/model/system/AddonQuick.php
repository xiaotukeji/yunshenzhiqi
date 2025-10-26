<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com

 * =========================================================
 */

namespace app\model\system;

use app\model\BaseModel;
use app\model\system\Upgrade as UpgradeModel;


class AddonQuick extends BaseModel
{

    /**
     * 添加快捷方式
     * @param $data
     * @return array
     */
    public function addAddonQuickMode($data)
    {
        //判断是否已存在该插件
        $addon_count = model('addon_quick')->getCount([ [ 'name', '=', $data[ 'name' ] ] ]);
        if ($addon_count > 0) {
            return $this->error('', '该插件已添加快捷方式，请不要重复添加');
        }

        $data[ 'create_time' ] = time();
        $res = model('addon_quick')->add($data);
        return $this->success($res);
    }

    /**
     * 删除快捷方式
     * @param array $condition
     * @return array
     */
    public function deleteAddonQuickMode($condition = [])
    {
        $res = model('addon_quick')->delete($condition);
        return $this->success($res);
    }

    /**
     * 获取快捷方式信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getAddonQuickModeInfo($condition = [], $field = '*')
    {
        $info = model('addon_quick')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取快捷方式类表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getAddonQuickModeList($condition = [], $order = '', $field = '*')
    {
        $list = model('addon_quick')->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 判断快捷方式插件是否已安装
     * @param $uninstall
     * @param $install
     * @return array
     */
    public function isInstallAddonQuick($uninstall, $install)
    {
        //未安装的插件
        $uninstall_name_arr = array_column($uninstall, 'name');
        //已安装的插件
        $install_name_arr = array_column($install, 'name');
        //获取快捷方式插件
        $addon_quick_list = $this->getAddonQuickModeList([], '', '*');

        if (empty($addon_quick_list[ 'data' ])) {
            return [
                'uninstall' => $uninstall,
                'install' => $install
            ];
        } else {

            foreach ($addon_quick_list[ 'data' ] as $k => $v) {

                //判断是否在已安装的插件中
                if (!in_array($v[ 'name' ], $install_name_arr)) {
                    //判断是否在未安装的插件中
                    if (empty($uninstall_name_arr) || !in_array($v[ 'name' ], $uninstall_name_arr)) {
                        $v[ 'is_quick' ] = 1;
                        $v[ 'download' ] = 1;
                        $uninstall[] = $v;
                    }
                }
            }

            return [
                'uninstall' => $uninstall,
                'install' => $install
            ];
        }
    }

    /**
     * 根据插件类型获取官网插件
     * @param $addon_list
     * @param $type
     * @return array
     */
    public function getAddonQuickByAddonType($addon_list, $type)
    {
        //获取官网所有插件
        //$upgrade_model = new UpgradeModel();
        //$website_addon_list = $upgrade_model->getPluginGoodsList();
        $website_addon_list = [
            [
                'goods_name' => '库存管理',
                'goods_image' => 'upload/1/common/images/20220806/20220806095314165975079412375.png',
                'addon_goods_key' => 'stock',
                'introduction' => '库存管理',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20220806/20220806095314165975079412375.png',
                'package_name' => '单商户V5多门店版'
            ],
            [
                'goods_name' => '收银台',
                'goods_image' => 'upload/1/common/images/20220806/20220806095314165975079412375.png',
                'addon_goods_key' => 'cashier',
                'introduction' => '收银台',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20220806/20220806095314165975079412375.png',
                'package_name' => '单商户V5多门店版'
            ],
            [
                'goods_name' => '卡项与服务商品',
                'goods_image' => 'upload/1/common/images/20220806/20220806095314165975079412375.png',
                'addon_goods_key' => 'cardservice',
                'introduction' => '卡项与服务商品，创建卡项与服务商品',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20220806/20220806095314165975079412375.png',
                'package_name' => '单商户V5多门店版'
            ],
            [
                'goods_name' => '官方模板二',
                'goods_image' => 'upload/1/common/images/20220806/20220806095314165975079412375.png',
                'addon_goods_key' => 'diy_default2',
                'introduction' => '官方模板二',
                'type_mark' => 'template',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20220806/20220806095314165975079412375.png',
                'package_name' => '单商户V5多门店版'
            ],
            [
                'goods_name' => '官方模板一',
                'goods_image' => 'upload/1/common/images/20220806/20220806095314165975079412375.png',
                'addon_goods_key' => 'diy_default2',
                'introduction' => '官方模板一',
                'type_mark' => 'template',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20220806/20220806095314165975079412375.png',
                'package_name' => '单商户V5多门店版'
            ],
            [
                'goods_name' => '盲盒',
                'goods_image' => 'upload/1/common/images/20211122/20211122115329163755320992510.png',
                'addon_goods_key' => 'blindbox',
                'introduction' => '盲盒',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20220806/20220806095314165975079412375.png',
                'package_name' => ''
            ],
            [
                'goods_name' => '裂变红包',
                'goods_image' => 'upload/1/common/images/20211122/20211122115221163755314178870.png',
                'addon_goods_key' => 'hongbao',
                'introduction' => '裂变红包',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20220806/20220806095314165975079412375.png',
                'package_name' => ''
            ],
            [
                'goods_name' => '拼团返利',
                'goods_image' => 'upload/1/common/images/20211026/20211026045912163523875230085.png',
                'addon_goods_key' => 'pinfan',
                'introduction' => '拼团返利',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20220806/20220806095314165975079412375.png',
                'package_name' => ''
            ],
            [
                'goods_name' => '打包一口价',
                'goods_image' => 'upload/1/common/images/20211018/20211018023855163453913563870.png',
                'addon_goods_key' => 'bale',
                'introduction' => '打包一口价',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20211018/20211018023855163453913563870.png',
                'package_name' => ''
            ],
            [
                'goods_name' => '节日有礼',
                'goods_image' => 'upload/1/common/images/20211018/20211018023337163453881733928.png',
                'addon_goods_key' => 'scenefestival',
                'introduction' => '节日有礼',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20211018/20211018023337163453881733928.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '生日有礼',
                'goods_image' => 'upload/1/common/images/20211018/20211018023116163453867671461.png',
                'addon_goods_key' => 'birthdaygift',
                'introduction' => '生日有礼',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20211018/20211018023116163453867671461.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '好友瓜分劵',
                'goods_image' => 'upload/1/common/images/20211018/20211018022940163453858055379.png',
                'addon_goods_key' => 'divideticket',
                'introduction' => '好友瓜分劵',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20211018/20211018022940163453858055379.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '商品海报',
                'goods_image' => 'upload/1/common/images/20210720/20210720065134162677829416090.png',
                'addon_goods_key' => 'postertemplate',
                'introduction' => '商品海报',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210720/20210720065134162677829416090.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '虚拟评价',
                'goods_image' => 'upload/1/common/images/20210720/20210720064751162677807111241.png',
                'addon_goods_key' => 'virtualevaluation',
                'introduction' => '虚拟评价',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210720/20210720064751162677807111241.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '代客下单',
                'goods_image' => 'upload/1/common/images/20210720/20210720064640162677800061133.png',
                'addon_goods_key' => 'replacebuy',
                'introduction' => '代客下单',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210720/20210720064640162677800061133.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '社群接龙',
                'goods_image' => 'upload/1/common/images/20210720/20210720064501162677790157245.png',
                'addon_goods_key' => 'jielong',
                'introduction' => '社群接龙',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210720/20210720064501162677790157245.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '礼品卡',
                'goods_image' => 'upload/1/common/images/20210720/20210720064319162677779969794.png',
                'addon_goods_key' => 'giftcard',
                'introduction' => '礼品卡',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210720/20210720064319162677779969794.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '系统表单',
                'goods_image' => 'upload/1/common/images/20210531/20210531112649162243160990018.png',
                'addon_goods_key' => 'form',
                'introduction' => '表单自定义信息收集',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210531/20210531112649162243160990018.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '商家手机管理端',
                'goods_image' => 'upload/1/common/images/20210508/20210508031207162045792733940.png',
                'addon_goods_key' => 'mobileshop',
                'introduction' => '商家手机管理端',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210508/20210508031207162045792733940.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '微信视频号',
                'goods_image' => 'upload/1/common/images/20210508/20210508030959162045779952786.png',
                'addon_goods_key' => 'shopcomponent',
                'introduction' => '实现小程序与视频号的连接',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210508/20210508030959162045779952786.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '商品采集',
                'goods_image' => 'upload/1/common/images/20210225/20210225031015161423701579638.png',
                'addon_goods_key' => 'goodsgrab',
                'introduction' => '一键采集商品，助力店铺高效铺货！',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210225/20210225031015161423701579638.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '电子卡密',
                'goods_image' => 'upload/1/common/images/20210225/20210225030749161423686905663.png',
                'addon_goods_key' => 'virtualcard',
                'introduction' => '电子卡密',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210225/20210225030749161423686905663.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '商品预售',
                'goods_image' => 'upload/1/common/images/20210225/20210225025210161423593080745.png',
                'addon_goods_key' => 'presale',
                'introduction' => '商品预售',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210225/20210225030749161423686905663.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => 'PC电脑端',
                'goods_image' => 'upload/1/common/images/20210118/20210118021948161095078842940.png',
                'addon_goods_key' => 'pc',
                'introduction' => '电脑端管理',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210118/20210118021948161095078842940.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '超级会员卡',
                'goods_image' => 'upload/1/common/images/20210116/20210116024047161077924759542.png',
                'addon_goods_key' => 'supermember',
                'introduction' => '超级会员卡',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20210116/20210116024047161077924759542.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '邀请奖励',
                'goods_image' => 'upload/1/common/images/20201225/20201225025153160887911369173.png',
                'addon_goods_key' => 'memberrecommend',
                'introduction' => '邀请奖励',
                'type_mark' => 'member',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20201225/20201225025153160887911369173.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '客服',
                'goods_image' => 'upload/1/common/images/20200831/20200831023413159885565345371.png',
                'addon_goods_key' => 'servicer',
                'introduction' => '客服',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831023413159885565345371.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '会员注销',
                'goods_image' => 'upload/1/common/images/20200831/20200831121317159884719722320.png',
                'addon_goods_key' => 'membercancel',
                'introduction' => '会员注销',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121317159884719722320.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '阿里云OSS',
                'goods_image' => 'upload/1/common/images/20200831/20200831121317159884719721795.png',
                'addon_goods_key' => 'alioss',
                'introduction' => '阿里云OSS',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121317159884719721795.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '会员消费',
                'goods_image' => 'upload/1/common/images/20200831/20200831121318159884719813494.png',
                'addon_goods_key' => 'memberconsume',
                'introduction' => '会员消费',
                'type_mark' => 'member',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121318159884719813494.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '会员价',
                'goods_image' => 'upload/1/common/images/20200831/20200831121318159884719808811.png',
                'addon_goods_key' => 'memberprice',
                'introduction' => '会员消费',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121318159884719808811.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '会员充值',
                'goods_image' => 'upload/1/common/images/20200831/20200831121318159884719808535.png',
                'addon_goods_key' => 'memberrecharge',
                'introduction' => '会员充值',
                'type_mark' => 'member',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121318159884719808535.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '会员注册',
                'goods_image' => 'upload/1/common/images/20200831/20200831121318159884719868695.png',
                'addon_goods_key' => 'memberregister',
                'introduction' => '会员注册',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121318159884719868695.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '会员签到',
                'goods_image' => 'upload/1/common/images/20200831/20200831121318159884719806310.png',
                'addon_goods_key' => 'membersignin',
                'introduction' => '会员签到',
                'type_mark' => 'member',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121318159884719806310.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '会员提现',
                'goods_image' => 'upload/1/common/images/20200831/20200831121318159884719803074.png',
                'addon_goods_key' => 'memberwithdraw',
                'introduction' => '会员提现',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121318159884719803074.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '拼团',
                'goods_image' => 'upload/1/common/images/20200831/20200831121319159884719972566.png',
                'addon_goods_key' => 'pintuan',
                'introduction' => '拼团',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121319159884719972566.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '限时折扣',
                'goods_image' => 'upload/1/common/images/20200831/20200831121321159884720116441.png',
                'addon_goods_key' => 'discount',
                'introduction' => '限时折扣',
                'type_mark' => 'member',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121321159884720116441.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '满额包邮',
                'goods_image' => 'upload/1/common/images/20200831/20200831121319159884719919698.png',
                'addon_goods_key' => 'freeshipping',
                'introduction' => '满额包邮',
                'type_mark' => 'member',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121319159884719919698.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '积分商城',
                'goods_image' => 'upload/1/common/images/20200831/20200831121318159884719872681.png',
                'addon_goods_key' => 'pointexchange',
                'introduction' => '积分商城',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121318159884719872681.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '满减活动',
                'goods_image' => 'upload/1/common/images/20200831/20200831121318159884719879178.png',
                'addon_goods_key' => 'manjian',
                'introduction' => '满减活动',
                'type_mark' => 'member',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121318159884719879178.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '七牛云上传',
                'goods_image' => 'upload/1/common/images/20200831/20200831121320159884720001497.png',
                'addon_goods_key' => 'qiniu',
                'introduction' => '七牛云上传',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121320159884720001497.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '限时秒杀',
                'goods_image' => 'upload/1/common/images/20200831/20200831121320159884720051389.png',
                'addon_goods_key' => 'seckill',
                'introduction' => '限时秒杀',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121320159884720051389.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '多门店管理',
                'goods_image' => 'upload/1/common/images/20200831/20200831121317159884719723619.png',
                'addon_goods_key' => 'store',
                'introduction' => '多门店管理',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121317159884719723619.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '专题活动',
                'goods_image' => 'upload/1/common/images/20200831/20200831121321159884720139565.png',
                'addon_goods_key' => 'topic',
                'introduction' => '专题活动',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121321159884720139565.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '微信小程序',
                'goods_image' => 'upload/1/common/images/20200831/20200831121320159884720034793.png',
                'addon_goods_key' => 'weapp',
                'introduction' => '微信小程序',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121320159884720034793.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '微信公众号',
                'goods_image' => 'upload/1/common/images/20200831/20200831121320159884720018798.png',
                'addon_goods_key' => 'wechat',
                'introduction' => '微信公众号',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121320159884720018798.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '微信支付',
                'goods_image' => 'upload/1/common/images/20200831/20200831121320159884720025171.png',
                'addon_goods_key' => 'wechatpay',
                'introduction' => '微信支付',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121320159884720025171.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '小程序直播',
                'goods_image' => 'upload/1/common/images/20200831/20200831121321159884720101021.png',
                'addon_goods_key' => 'live',
                'introduction' => '小程序直播',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121321159884720101021.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '微信圈子',
                'goods_image' => 'upload/1/common/images/20200831/20200831121320159884720017187.png',
                'addon_goods_key' => 'goodscircle',
                'introduction' => '微信圈子',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121321159884720101021.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '电子面单',
                'goods_image' => 'upload/1/common/images/20200831/20200831121317159884719725185.png',
                'addon_goods_key' => 'electronicsheet',
                'introduction' => '电子面单',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121317159884719725185.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '幸运抽奖',
                'goods_image' => 'upload/1/common/images/20200831/20200831121320159884720079756.png',
                'addon_goods_key' => 'turntable',
                'introduction' => '幸运抽奖',
                'type_mark' => 'member',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121320159884720079756.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '刮刮乐',
                'goods_image' => 'upload/1/common/images/20200831/20200831121318159884719804391.png',
                'addon_goods_key' => 'cards',
                'introduction' => '刮刮乐',
                'type_mark' => 'member',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121318159884719804391.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '砸金蛋',
                'goods_image' => 'upload/1/common/images/20200831/20200831121321159884720140899.png',
                'addon_goods_key' => 'egg',
                'introduction' => '砸金蛋',
                'type_mark' => 'member',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121321159884720140899.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '小票打印',
                'goods_image' => 'upload/1/common/images/20200831/20200831121321159884720107987.png',
                'addon_goods_key' => 'printer',
                'introduction' => '小票打印',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121321159884720107987.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '积分抵现',
                'goods_image' => 'upload/1/common/images/20200831/20200831121318159884719881059.png',
                'addon_goods_key' => 'pointcash',
                'introduction' => '积分抵现',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121318159884719881059.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '店铺笔记',
                'goods_image' => 'upload/1/common/images/20200831/20200831121321159884720121453.png',
                'addon_goods_key' => 'notes',
                'introduction' => '店铺笔记',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121321159884720121453.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => 'v3Tov4迁移数据',
                'goods_image' => 'upload/1/common/images/20200831/20200831121317159884719722320.png',
                'addon_goods_key' => 'v3tov4',
                'introduction' => 'v3Tov4迁移数据',
                'type_mark' => 'tool',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121317159884719722320.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '牛云短信',
                'goods_image' => 'upload/1/common/images/20200831/20200831121317159884719722320.png',
                'addon_goods_key' => 'niusms',
                'introduction' => '牛云短信',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121319159884719970328.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '阿里云短信',
                'goods_image' => 'upload/1/common/images/20200831/20200831121317159884719721666.png',
                'addon_goods_key' => 'alisms',
                'introduction' => '阿里云短信',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121317159884719721666.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '团购',
                'goods_image' => 'upload/1/common/images/20200831/20200831121320159884720010538.png',
                'addon_goods_key' => 'groupbuy',
                'introduction' => '团购',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121320159884720010538.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '分销',
                'goods_image' => 'upload/1/common/images/20200831/20200831121317159884719730188.png',
                'addon_goods_key' => 'fenxiao',
                'introduction' => '分销',
                'type_mark' => 'member',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121317159884719730188.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '优惠券',
                'goods_image' => 'upload/1/common/images/20200831/20200831121321159884720104694.png',
                'addon_goods_key' => 'coupon',
                'introduction' => '优惠券',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121321159884720104694.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '组合套餐',
                'goods_image' => 'upload/1/common/images/20200831/20200831121321159884720134467.png',
                'addon_goods_key' => 'bundling',
                'introduction' => '组合套餐',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121321159884720134467.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '砍价',
                'goods_image' => 'upload/1/common/images/20200831/20200831121318159884719881825.png',
                'addon_goods_key' => 'bargain',
                'introduction' => '砍价',
                'type_mark' => 'shop',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121318159884719881825.png',
                'package_name' => '单商户V5基础版'
            ],
            [
                'goods_name' => '支付宝支付',
                'goods_image' => 'upload/1/common/images/20200831/20200831121321159884720125679.png',
                'addon_goods_key' => 'alipay',
                'introduction' => '支付宝支付',
                'type_mark' => 'system',
                'logo_img' => 'https://res.niushop.com/upload/1/common/images/20200831/20200831121321159884720125679.png',
                'package_name' => '单商户V5基础版'
            ],
        ];

        $arr = [];
        if (empty($website_addon_list)) {
            return $arr;
        } else {

            $addon_name_arr = array_column($addon_list, 'name');
            foreach ($website_addon_list as $k => $v) {

                if ($v[ 'type_mark' ] == $type) {

                    if (empty($addon_list)) {
                        $arr[] = $v;
                    } else {
                        //判断是否在插件中
                        if (!in_array($v[ 'addon_goods_key' ], $addon_name_arr)) {
                            $arr[] = $v;
                        }
                    }

                }
            }

            return $arr;
        }
    }
}
