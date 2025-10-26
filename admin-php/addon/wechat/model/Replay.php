<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\wechat\model;

use app\model\BaseModel;

/**
 * 微信回复
 */
class Replay extends BaseModel
{

    /*******************************************************************************微信回复开始*****************************************************/
    /**
     * 添加微信关键词回复
     * @param $data
     * @return array
     */
    public function addRule($data)
    {
        $res = model('wechat_replay_rule')->add($data);
        if ($res === false) {
            return $this->error($res, 'SAVE_FAIL');
        }
        return $this->success($res, 'SAVE_SUCCESS');
    }

    /**
     * 修改微信关键词回复
     * @param $data
     * @param $condition
     * @return array
     */
    public function editRule($data, $condition)
    {
        $res = model('wechat_replay_rule')->update($data, $condition);
        if ($res === false) {
            return $this->error($res, 'SAVE_FAIL');
        }
        return $this->success($res, 'SAVE_SUCCESS');
    }

    /**
     * 删除微信关键词回复
     * @param $condition
     * @return array
     */
    public function deleteRule($condition)
    {
        $res = model('wechat_replay_rule')->delete($condition);
        if ($res === false) {
            return $this->error($res, 'DELETE_FAIL');
        }
        return $this->success($res, 'DELETE_SUCCESS');
    }

    /**
     * 获取微信关键词回复信息
     * @param $condition
     * @param string $field
     * @return array
     */
    public function getRuleInfo($condition, $field = '*')
    {
        $info = model('wechat_replay_rule')->getInfo($condition, $field);
        return $this->success($info);
    }

    /**
     * 获取回复列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getReplayPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = '', $field = '*')
    {
        $list = model('wechat_replay_rule')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }


    /**
     * 获取站点关键字回复
     * @param $keywords
     * @param $site_id
     * @return array
     */
    public function getSiteWechatKeywordsReplay($keywords, $site_id)
    {

        $list = model('wechat_replay_rule')->getList([['rule_type', '=', 'KEYWORDS'], ['site_id', '=', $site_id]]);
        $default_list = model('wechat_replay_rule')->getList([['rule_type', '=', 'DEFAULT'], ['site_id', '=', $site_id]]);
        $rule_list = array();
        foreach ($list as $k => $v) {
            $kewords_array = json_decode($v['keywords_json'], true);
            $replay_array = json_decode($v['replay_json'], true);
            if (!empty($kewords_array) && !empty($replay_array)) {
                foreach ($kewords_array as $k_key => $v_key) {
                    if (($v_key['keywords_type'] == 0 && $v_key['keywords_name'] == $keywords) || ($v_key['keywords_type'] == 1 && (strpos($keywords, $v_key['keywords_name']) !== false))) {
                        $num = rand(0, count($replay_array) - 1);
                        $rule_list[] = $replay_array[$num];
                    }
                }
            }
        }
        if (empty($rule_list)) {
            $default_rule_list = array();
            foreach ($default_list as $kk => $vv) {
                $default_replay_array = json_decode($vv['replay_json'], true);
                if (!empty($default_replay_array)) {
                    $default_rule_list[] = $default_replay_array[0];
                }
            }
            if (!empty($default_rule_list)) {
                $rule = $default_rule_list[0];
            } else {
                $rule = [];
            }
        } else {
            $rule = $rule_list[0];
        }
        return $this->success($rule);
    }


    /**
     * 获取微信关注回复
     * @param $site_id
     * @return array
     */
    function getWechatFollowReplay($site_id)
    {
        $follow_info = model('wechat_replay_rule')->getInfo([['rule_type', '=', 'AFTER'], ['site_id', '=', $site_id]]);
        $replay_content = '';
        if (!empty($follow_info['replay_json'])) {
            $replay_info = json_decode($follow_info['replay_json'], true);
            switch ($replay_info[0]['type']) {
                case 'text' :
                    $replay_content = $replay_info[0]['reply_content'];
                    break;
                case 'articles' :
                    $replay_content = '';
                    break;
            }
        }
        return $this->success($replay_content);
    }

    /*******************************************************************************微信回复结束*****************************************************/
}