<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\fenxiao\shop\controller;

use addon\fenxiao\model\Config as ConfigModel;
use addon\fenxiao\model\FenxiaoLevel as FenxiaoLevelModel;
use addon\fenxiao\model\FenxiaoWithdraw;
use app\model\goods\Goods as GoodsModel;
use app\model\system\Document;
use app\shop\controller\BaseShop;

/**
 *  分销设置
 */
class Config extends BaseShop
{

    /**
     *  分销基础设置
     */
    public function basics()
    {
        $model = new ConfigModel();

        if (request()->isJson()) {
            $data = [
                'level' => input('level', ''),//分销层级
                'internal_buy' => input('internal_buy', ''),//分销内购
                'is_examine' => input('is_examine', ''),//是否需要审核（0关闭 1开启）
                'self_purchase_rebate' => input('self_purchase_rebate', ''),//是否开启分销商自购返佣（0关闭 1开启）
                'fenxiao_condition' => input('fenxiao_condition', ''),//成为分销商条件(0无条件 1申请 2消费次数 3消费金额 4购买指定商品)
                'consume_count' => input('consume_count', ''),//消费次数
                'consume_money' => input('consume_money', ''), //消费金额
                'goods_ids' => input('goods_ids', ''), //指定商品id
                'consume_condition' => input('consume_condition', ''),//消费条件(1付款后 2订单完成)
                'perfect_info' => input('perfect_info', ''),//完善资料
                'child_condition' => input('child_condition', ''),//成为下线条件
                'is_apply' => input('is_apply', ''),//是否开启分销申请（0关闭 1开启）
                'is_commission_money' => input('is_commission_money', ''),//是否开启商品详情一级佣金（0关闭 1开启）
                'one_rate' => input('one_rate', 0.00),
                'two_rate' => input('two_rate', 0.00),
                'three_rate' => input('three_rate', 0.00),
            ];

            $res = $model->setFenxiaoBasicsConfig($data, 1, $this->site_id);
            return $res;
        } else {
            $basics = $model->getFenxiaoBasicsConfig($this->site_id);
            $this->assign('basics_info', $basics[ 'data' ][ 'value' ]);

            $fenxiao = $model->getFenxiaoConfig($this->site_id)[ 'data' ][ 'value' ];

            $fenxiao[ 'goods_list' ] = '';
            if ($fenxiao[ 'fenxiao_condition' ] == 4) { // 购买指定商品
                $goods_model = new GoodsModel();
                $condition[] = [ 'goods_id', 'in', $fenxiao[ 'goods_ids' ] ];
                $condition[] = [ 'site_id', '=', $this->site_id ];
                $fenxiao[ 'goods_list' ] = $goods_model->getGoodsList($condition, 'goods_id,goods_name,goods_image,price,goods_stock')[ 'data' ];
            }
            $this->assign('fenxiao_info', $fenxiao);

            $relation = $model->getFenxiaoRelationConfig($this->site_id);
            $this->assign('relation_info', $relation[ 'data' ][ 'value' ]);

            $level_model = new FenxiaoLevelModel();
            $level_info = $level_model->getLevelInfo([ [ 'site_id', '=', $this->site_id ], [ 'is_default', '=', 1 ] ], 'one_rate,two_rate,three_rate')[ 'data' ];
            $this->assign('level', $level_info);

            return $this->fetch('config/basics');
        }

    }

    /**
     * 分销协议设置
     */
    public function agreement()
    {
        $model = new ConfigModel();

        if (request()->isJson()) {
            $data = [
                'is_agreement' => input('is_agreement', ''),//是否显示申请协议
                'agreement_title' => input('agreement_title', ''),//协议标题
                'agreement_content' => input('agreement_content', ''),//协议内容
                'img' => input('img', ''),//申请页面顶部图片
            ];
            $res = $model->setFenxiaoAgreementConfig($data, 1, $this->site_id);
            return $res;
        } else {
            $agreement = $model->getFenxiaoAgreementConfig($this->site_id);
            $this->assign('agreement_info', $agreement[ 'data' ][ 'value' ]);

            $document_model = new Document();
            $document = $document_model->getDocument([ [ 'site_id', '=', $this->site_id ], [ 'app_module', '=', 'shop' ], [ 'document_key', '=', 'FENXIAO_AGREEMENT'] ]);
            $this->assign('document', $document[ 'data' ]);

            return $this->fetch('config/agreement');
        }
    }

    /**
     *  分销结算设置
     */
    public function settlement()
    {
        $model = new ConfigModel();
        if (request()->isJson()) {
            $transfer_type = '';
            if (!empty(input('transfer_type'))) {
                $transfer_type = implode(',', input('transfer_type'));
            }
            $data = [
                'account_type' => input('account_type', ''),//佣金计算方式
                'settlement_day' => input('settlement_day', ''),//天数
                'withdraw' => input('withdraw', ''),//最低提现额度
                'withdraw_rate' => input('withdraw_rate', ''),//佣金提现手续费
//                'min_no_fee' => input('min_no_fee', ''),//最低免手续费区间
//                'max_no_fee' => input('max_no_fee', ''),//最高免手续费区间
                'withdraw_status' => input('withdraw_status', ''),//提现审核
                'withdraw_type' => input('withdraw_type', ''),//提现方式,

                'transfer_type' => $transfer_type,//转账方式,
                'is_auto_transfer' => input('is_auto_transfer', 0),//是否自动转账 1 手动转账  2 自动转账
//                'min' => input('min', 0),//提现最低额度
                'max' => input('max', 0),//提现最高额度
            ];
            $res = $model->setFenxiaoSettlementConfig($data, 1, $this->site_id);
            return $res;
        } else {
//            $settlement = $model->getFenxiaoSettlementConfig($this->site_id);
//            $this->assign('settlement_info', $settlement[ 'data' ][ 'value' ]);
            $withdraw_config = $model->getFenxiaoWithdrawConfig($this->site_id)[ 'data' ][ 'value' ] ?? [];
            $this->assign('withdraw_info', $withdraw_config);

            $fenxiao_withdraw_model = new FenxiaoWithdraw();
            $transfer_type_list = $fenxiao_withdraw_model->getTransferType($this->site_id);
            $transfer_type_list[ 'balance' ] = '余额';
            $this->assign('transfer_type_list', $transfer_type_list);

            return $this->fetch('config/settlement');
        }

    }

    /**
     *  分销文字设置
     */
    public function words()
    {
        $model = new ConfigModel();
        if (request()->isJson()) {
            $data = [
                'concept' => input('concept', ''),//分销概念
                'fenxiao_name' => input('fenxiao_name', ''),//分销商名称
                'withdraw' => input('withdraw', ''),//提现名称
                'account' => input('account', ''),//佣金
                'my_team' => input('my_team', ''),//我的团队
                'child' => input('child', ''),//下线
            ];

            $res = $model->setFenxiaoWordsConfig($data, 1, $this->site_id);
            return $res;
        } else {
            $config_info = $model->getFenxiaoWordsConfig($this->site_id)[ 'data' ][ 'value' ];
            $this->assign('config_info', $config_info);

            return $this->fetch('config/words');
        }

    }

    /**
     * 活动规则
     */
    public function promoteRule()
    {
        $document_model = new Document();
        if (request()->isJson()) {
            $content = input('content', '');
            $res = $document_model->setDocument('分销推广规则', $content, [ [ 'site_id', '=', $this->site_id ], [ 'app_module', '=', 'shop' ], [ 'document_key', '=', 'FENXIAO_PROMOTE_RULE'] ]);
            return $res;
        } else {
            $document = $document_model->getDocument([ [ 'site_id', '=', $this->site_id ], [ 'app_module', '=', 'shop' ], [ 'document_key', '=', 'FENXIAO_PROMOTE_RULE'] ]);
            $this->assign('document', $document[ 'data' ]);

            return $this->fetch('config/promote_rule');
        }
    }
}