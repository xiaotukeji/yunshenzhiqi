<?php


namespace addon\wechatpay\model;
use addon\fenxiao\model\FenxiaoWithdraw;
use addon\memberwithdraw\model\Withdraw;
use addon\store\model\StoreWithdraw;
use app\model\BaseModel;

/**
 * 提现转账相关配置
 */
class TransferConfig extends BaseModel
{

    //转账来源
    public array $from_type = [
        'member_withdraw' => '会员提现',
        'fenxiao_withdraw'=> '分销提现',
        'store_withdraw' => '门店提现'
     ];


    public array $class_match = [
        'member_withdraw' => Withdraw::class,
        'fenxiao_withdraw'=> FenxiaoWithdraw::class,
        'store_withdraw' => StoreWithdraw::class
    ];

    //转账字段映射关系
    public array $transfer_match = [
        'member_withdraw' => 'member_transfer',
        'fenxiao_withdraw'=> 'fenxiao_transfer',
        'store_withdraw' => 'store_transfer'
    ];
}