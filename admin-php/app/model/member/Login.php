<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\model\member;

use addon\wechat\model\Message as WechatMessage;
use app\model\BaseModel;
use app\model\message\Sms;

/**
 * 登录
 *
 * @author Administrator
 *
 */
class Login extends BaseModel
{

    /**
     * 用户登录
     * @param $data
     * @return array
     */
    public function login($data)
    {

        $info = model("member")->getInfo([
            ['username|mobile|email', '=', $data['username']],
            ['password', '=', data_md5($data['password'])],
            ['site_id', '=', $data['site_id']],
            ['is_delete', '=', 0]
        ], 'member_id,username, nickname, mobile, email, status,last_login_time,can_receive_registergift');
        if (empty($info)) {
            return $this->error('', 'USERNAME_OR_PASSWORD_ERROR');
        } elseif ($info['status'] == 0) {
            return $this->error('', 'MEMBER_IS_LOCKED');
        } else {
            if ($info['can_receive_registergift'] == 1) {
                event("MemberReceiveRegisterGift", ['member_id' => $info['member_id'], 'site_id' => $data['site_id']]);
            }
            //更新登录时间
            model("member")->update([
                'login_time' => time(),
                'last_login_time' => time(),
                'can_receive_registergift' => 0,
                'login_ip' => request()->ip(),
                'login_type' => $data['app_type'] ?? '',
                'login_type_name' => $data['app_type_name'] ?? '',
            ], [['member_id', '=', $info['member_id']]]);

            //执行登录奖励
            event("MemberLogin", ['member_id' => $info['member_id'], 'site_id' => $data['site_id']], true);

            //用户第三方信息刷新
            $this->refreshAuth($info['member_id'], $data);
            return $this->success($info);
        }
    }

    /**
     * 第三方登录
     * @param array $data 必然传输auth_tag, auth_openid
     */
    public function authLogin($data)
    {
        //微信未使用完整服务返回数据不能做登录使用
        //未使用完整服务和使用了完整服务返回的数据是不一样的
        if(isset($data['nickName']) && isset($data['avatarUrl']) && $data['nickName'] == '微信用户' && $data['avatarUrl'] == 'https://thirdwx.qlogo.cn/mmopen/vi_32/Q3auHgzwzM6WH3T48eljAMnoGDv722DOL7nO15FgEz64psqp2xiaZJCP2v71dOqS03hKjytzRrh3ZHg09mKNtXg/132'){
            return $this->error(null, '微信授权使用完整服务');
        }

        $info = [];
        $auth_tag = '';
        foreach ($data as $key => $value) {
            if (in_array($key, ['wx_unionid', 'wx_openid', 'weapp_openid', 'qq_openid', 'ali_openid', 'baidu_openid', 'toutiao_openid'])) {
                $auth_tag = $key;
                if (empty($value)) return $this->error('', 'PARAMETER_ERROR');
                $info = model("member")->getFirstData(
                    [
                        [$key, '=', $value],
                        ['site_id', '=', $data['site_id']],
                        ['is_delete', '=', 0]
                    ], 'member_id,username, nickname, mobile, email, status, last_login_time, can_receive_registergift'
                );
                if (!empty($info)) break;
            }
        }
        if (empty($auth_tag)) return $this->error('', 'PARAMETER_ERROR');

        if (empty($info)) {
            // 会员不存在 第三方自动注册开启 未开启绑定手机 则进行自动注册
            $config = new Config();
            $config_info = $config->getRegisterConfig($data['site_id'], 'shop');
            if ($config_info['data']['value']['third_party'] && !$config_info['data']['value']['bind_mobile']) {
                $register = new Register();
                $register_res = $register->authRegister($data);
                if ($register_res['code'] == 0) {
                    $info = model("member")->getInfo([['member_id', '=', $register_res['data']]], 'member_id,username, nickname, mobile, email, status, last_login_time,can_receive_registergift');
                    $info['is_register'] = 1;
                }
            }
        }

        if (empty($info)) {
            return $this->error('MEMBER_NOT_EXIST', 'MEMBER_NOT_EXIST');
        } elseif ($info['status'] == 0) {
            return $this->error('', 'MEMBER_IS_LOCKED');
        } else {
            if ($info['can_receive_registergift'] == 1) {
                event("MemberReceiveRegisterGift", ['member_id' => $info['member_id'], 'site_id' => $data['site_id']]);
            }
            //更新登录时间
            model("member")->update([
                'login_time' => time(),
                'last_login_time' => time(),
                'can_receive_registergift' => 0,
                'login_ip' => request()->ip(),
                'login_type' => $data['app_type'] ?? '',
                'login_type_name' => $data['app_type_name'] ?? '',
            ], [['member_id', '=', $info['member_id']]]);

            //执行登录奖励
            event("MemberLogin", ['member_id' => $info['member_id'], 'site_id' => $data['site_id']], true);

            //用户第三方信息刷新
            if (!isset($info['is_register'])) $this->refreshAuth($info['member_id'], $data);

            return $this->success($info);
        }
    }

    /**
     * 授权登录仅登录
     * @param $data
     * @return array
     */
    public function authOnlyLogin($data)
    {
        $info = [];
        $auth_tag = '';
        foreach ($data as $key => $value) {
            if (in_array($key, ['wx_unionid', 'wx_openid', 'weapp_openid', 'qq_openid', 'ali_openid', 'baidu_openid', 'toutiao_openid'])) {
                $auth_tag = $key;
                if (empty($value)) return $this->error('', 'PARAMETER_ERROR');
                $info = model("member")->getInfo(
                    [
                        [$key, '=', $value],
                        ['site_id', '=', $data['site_id']],
                        ['is_delete', '=', 0]
                    ], 'member_id,username, nickname, mobile, email, status, last_login_time, can_receive_registergift'
                );
                if (!empty($info)) break;
            }
        }
        if (empty($auth_tag)) return $this->error('', 'PARAMETER_ERROR');

        if (empty($info)) {
            // 前端根据data值判断业务处理
            return $this->error('MEMBER_NOT_EXIST', 'MEMBER_NOT_EXIST');
        } elseif ($info['status'] == 0) {
            return $this->error('', 'MEMBER_IS_LOCKED');
        } else {
            //更新登录时间
            model("member")->update([
                'login_time' => time(),
                'last_login_time' => time(),
                'can_receive_registergift' => 0,
                'login_ip' => request()->ip(),
                'login_type' => $data['app_type'] ?? '',
                'login_type_name' => $data['app_type_name'] ?? '',
            ], [['member_id', '=', $info['member_id']]]);

            //执行登录奖励
            event("MemberLogin", ['member_id' => $info['member_id'], 'site_id' => $data['site_id']], true);
            //用户第三方信息刷新
            $this->refreshAuth($info['member_id'], $data);
            return $this->success($info);
        }
    }

    /**
     * 刷新第三方信息
     * @param $member_id
     * @param $data
     * @return array
     */
    private function refreshAuth($member_id, $data)
    {
        Member::modifyLastVisitTime($member_id);
        $data = [
            'qq_openid' => $data['qq_openid'] ?? '',
            'wx_openid' => $data['wx_openid'] ?? '',
            'weapp_openid' => $data['weapp_openid'] ?? '',
            'wx_unionid' => $data['wx_unionid'] ?? '',
            'ali_openid' => $data['ali_openid'] ?? '',
            'baidu_openid' => $data['baidu_openid'] ?? '',
            'toutiao_openid' => $data['toutiao_openid'] ?? '',
            'site_id' => $data['site_id']
        ];
        if (!empty($data['qq_openid'])) {
            model("member")->update(['qq_openid' => $data['qq_openid']], [['member_id', '=', $member_id], ['site_id', '=', $data['site_id']]]);
        }
        if (!empty($data['wx_openid'])) {
            model("member")->update(['wx_openid' => $data['wx_openid']], [['member_id', '=', $member_id], ['site_id', '=', $data['site_id']]]);
        }
        if (!empty($data['weapp_openid'])) {
            model("member")->update(['weapp_openid' => $data['weapp_openid']], [['member_id', '=', $member_id], ['site_id', '=', $data['site_id']]]);
        }
        if (!empty($data['wx_unionid'])) {
            model("member")->update(['wx_unionid' => $data['wx_unionid']], [['member_id', '=', $member_id], ['site_id', '=', $data['site_id']]]);
        }
        if (!empty($data['ali_openid'])) {
            model("member")->update(['ali_openid' => $data['ali_openid']], [['member_id', '=', $member_id], ['site_id', '=', $data['site_id']]]);
        }
        if (!empty($data['baidu_openid'])) {
            model("member")->update(['baidu_openid' => $data['baidu_openid']], [['member_id', '=', $member_id], ['site_id', '=', $data['site_id']]]);
        }
        if (!empty($data['toutiao_openid'])) {
            model("member")->update(['toutiao_openid' => $data['toutiao_openid']], [['member_id', '=', $member_id], ['site_id', '=', $data['site_id']]]);
        }
        return $this->success();
    }

    /**
     * 检测openid是否存在
     * @param array $data
     * @return array
     */
    public function openidIsExits(array $data)
    {
        if (isset($data['wx_unionid']) && !empty($data['wx_unionid'])) {
            $count = model("member")->getCount([['wx_unionid', '=', $data['wx_unionid']], ['site_id', '=', $data['site_id']], ['is_delete', '=', 0]]);
            if ($count) return $this->success($count);
        }
        if (isset($data['wx_openid']) && !empty($data['wx_openid'])) {
            $count = model("member")->getCount([['wx_openid', '=', $data['wx_openid']], ['site_id', '=', $data['site_id']], ['is_delete', '=', 0]]);
            if ($count) return $this->success($count);
        }
        if (isset($data['weapp_openid']) && !empty($data['weapp_openid'])) {
            $count = model("member")->getCount([['weapp_openid', '=', $data['weapp_openid']], ['site_id', '=', $data['site_id']], ['is_delete', '=', 0]]);
            if ($count) return $this->success($count);
        }
        if (isset($data['qq_openid']) && !empty($data['qq_openid'])) {
            $count = model("member")->getCount([['qq_openid', '=', $data['qq_openid']], ['site_id', '=', $data['site_id']], ['is_delete', '=', 0]]);
            if ($count) return $this->success($count);
        }
        if (isset($data['ali_openid']) && !empty($data['ali_openid'])) {
            $count = model("member")->getCount([['ali_openid', '=', $data['ali_openid']], ['site_id', '=', $data['site_id']], ['is_delete', '=', 0]]);
            if ($count) return $this->success($count);
        }
        if (isset($data['baidu_openid']) && !empty($data['baidu_openid'])) {
            $count = model("member")->getCount([['baidu_openid', '=', $data['baidu_openid']], ['site_id', '=', $data['site_id']], ['is_delete', '=', 0]]);
            if ($count) return $this->success($count);
        }
        if (isset($data['toutiao_openid']) && !empty($data['toutiao_openid'])) {
            $count = model("member")->getCount([['toutiao_openid', '=', $data['toutiao_openid']], ['site_id', '=', $data['site_id']], ['is_delete', '=', 0]]);
            if ($count) return $this->success($count);
        }
        return $this->success(0);
    }

    /**
     * 用户登录
     * @param $data
     * @return array
     */
    public function mobileLogin($data)
    {
        $info = model("member")->getInfo([
            ['mobile', '=', $data['mobile']],
            ['site_id', '=', $data['site_id']],
            ['is_delete', '=', 0]
        ], 'member_id,username, nickname, mobile, email, status,last_login_time, can_receive_registergift');
        if (empty($info)) {
            return $this->error('MEMBER_NOT_EXIST', 'MEMBER_NOT_EXIST');
        } elseif ($info['status'] == 0) {
            return $this->error('', 'MEMBER_IS_LOCKED');
        } else {
            if ($info['can_receive_registergift'] == 1) {
                event("MemberReceiveRegisterGift", ['member_id' => $info['member_id'], 'site_id' => $data['site_id']]);
            }
            //更新登录时间
            model("member")->update([
                'login_time' => time(),
                'last_login_time' => time(),
                'can_receive_registergift' => 0,
                'login_ip' => request()->ip(),
                'login_type' => $data['app_type'] ?? '',
                'login_type_name' => $data['app_type_name'] ?? '',
            ], [['member_id', '=', $info['member_id']]]);

            event("MemberLogin", ['member_id' => $info['member_id'], 'site_id' => $data['site_id']], true);

            //用户第三方信息刷新
            $this->refreshAuth($info['member_id'], $data);
            return $this->success($info);
        }
    }

    /**
     * 登录动态码
     * @param $data
     * @return array|mixed|null
     */
    public function loginCode($data)
    {
        //发送短信
        $sms_model = new Sms();
        $var_parse = array (
            "code" => $data["code"],
        );
        $data["sms_account"] = $data["mobile"] ?? '';//手机号
        $data["var_parse"] = $var_parse;
        $sms_result = $sms_model->sendMessage($data);
        if ($sms_result["code"] < 0)
            return $sms_result;

        return $this->success();
    }

    /**
     * 登录通知
     * @param $data
     * @return array|mixed|void
     */
    public function loginSuccess($data)
    {
        $member_model = new Member();
        $member_info_result = $member_model->getMemberInfo([["member_id", "=", $data["member_id"]]], "username,mobile,email,reg_time,wx_openid,last_login_type,login_time, nickname");
        $member_info = $member_info_result["data"];

        //发送短信
        $sms_model = new Sms();

        $name = $member_info["nickname"] == '' ? $member_info["mobile"] : $member_info["nickname"];
        $var_parse = array (
            "name" => replaceSpecialChar($name),//验证码
        );
        $data["sms_account"] = $member_info["mobile"] ?? '';//手机号
        $data["var_parse"] = $var_parse;
        $sms_result = $sms_model->sendMessage($data);
        //        if($sms_result["code"] < 0)
        //            return $sms_result;


        //发送模板消息
        $wechat_model = new WechatMessage();
        $data["openid"] = $member_info["wx_openid"];

//         if(!empty($member_info["username"])){
//            $user_account = $member_info["username"];
//         }else{
//            if(!empty($member_info["mobile"])){
//              $user_account = $member_info["mobile"];
//            }else{
//              $user_account = $member_info["email"];
//            }
//         }

        $data["template_data"] = [
            'keyword1' => !empty($member_info["nickname"]) ? $member_info["nickname"] : $member_info["mobile"],
            'keyword2' => '登录成功',
            'keyword3' => time_to_date($member_info["login_time"]),
        ];
        $data["page"] = '';
        $wechat_model->sendMessage($data);

        return $this->success();
    }
}