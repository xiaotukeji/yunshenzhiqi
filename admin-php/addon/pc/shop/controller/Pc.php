<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace addon\pc\shop\controller;

use app\model\goods\GoodsCategory as GoodsCategoryModel;
use addon\pc\model\Pc as PcModel;
use app\model\web\Config;
use app\shop\controller\BaseShop;
use think\App;

/**
 * Pc端 控制器
 */
class Pc extends BaseShop
{
    private $pc_model;

    public function __construct(App $app = null)
    {
        $this->replace = [
            'ADDON_PC_CSS' => __ROOT__ . '/addon/pc/shop/view/public/css',
            'ADDON_PC_JS' => __ROOT__ . '/addon/pc/shop/view/public/js',
            'ADDON_PC_IMG' => __ROOT__ . '/addon/pc/shop/view/public/img',
        ];
        $this->pc_model = new PcModel();
        parent::__construct($app);
    }

    /**
     * 获取PC端部署信息
     * @return array
     */
    public function getDeploy()
    {
        if (request()->isJson()) {
            $config_model = new Config();
            $config = $config_model->getPcDomainName($this->site_id)[ 'data' ][ 'value' ];
            if ($config[ 'deploy_way' ] == 'separate') {
                $root_url = $config[ 'domain_name_pc' ];
            } else {
                $root_url = __ROOT__;
            }

            $res = [
                'root_url' => __ROOT__,
                'roots_url' => $root_url,
                'config' => $config,
            ];
            return success('', '', $res);
        }
    }

    /**
     * 设置pc端域名
     * @return array
     */
    public function pcDomainName()
    {
        $config_model = new Config();
        $domain_name = input("domain", "");
        $deploy_way = input("deploy_way", "default");

        if ($deploy_way == 'default') $domain_name = __ROOT__ . '/web';

        $result = $config_model->setPcDomainName([
            'domain_name_pc' => $domain_name,
            'deploy_way' => $deploy_way
        ]);

        return $result;
    }

    /**
     * 默认部署：无需下载，一键刷新，API接口请求地址为当前域名，编译代码存放到web文件夹中
     */
    public function downloadCsDefault()
    {
        $this->pcDomainName();
        return $this->pc_model->downloadCsDefault();
    }

    /**
     * 独立部署：下载编译代码包，参考开发文档进行配置
     */
    public function downloadCsSeparate()
    {
        if (strstr(ROOT_URL, 'niuteam.cn') === false) {
            $domain_name = input("domain", "");
            $res = $this->pc_model->downloadCsSeparate($domain_name);
            if ($res[ 'code' ] >= 0) {
                $config_model = new Config();
                $result = $config_model->setPcDomainName([
                    'domain_name_pc' => $domain_name,
                    'deploy_way' => 'separate'
                ]);
            }
            echo $res[ 'message' ];
        }
    }

    /**
     * 源码下载：下载开源代码包，参考开发文档进行配置，结合业务需求进行二次开发
     */
    public function downloadOs()
    {
        if (strstr(ROOT_URL, 'niuteam.cn') === false) {
            $res = $this->pc_model->downloadOs();
            echo $res[ 'message' ];
        }
    }

    /**
     * 首页浮层
     * @return mixed
     */
    public function floatLayer()
    {
        if (request()->isJson()) {
            $data = [
                'title' => input("title", ""),
                'url' => input("url", ""),
                'is_show' => input("is_show", 0),
                'number' => input("number", ""),
                'img_url' => input("img_url", "")
            ];
            $res = $this->pc_model->setFloatLayer($data, $this->site_id);
            return $res;
        } else {
            $link = $this->pc_model->getLink();
            $this->assign("link", $link);
            $float_layer = $this->pc_model->getFloatLayer($this->site_id)[ 'data' ][ 'value' ];
            $this->assign("float_layer", $float_layer);
            return $this->fetch('pc/float_layer');
        }
    }

    /**
     * 导航设置
     * @return mixed
     */
    public function navList()
    {
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');

            $condition = [
                [ 'site_id', '=', $this->site_id ]
            ];
            if (!empty($search_text)) $condition[] = [ 'nav_title', 'like', '%' . $search_text . '%' ];
            $order = 'create_time desc';

            $model = new PcModel();
            return $model->getNavPageList($condition, $page, $page_size, $order);
        } else {
            return $this->fetch('pc/nav_list');
        }
    }

    /**
     * 添加导航
     * @return mixed
     */
    public function addNav()
    {
        $model = new PcModel();
        if (request()->isJson()) {
            $data = [
                'nav_title' => input('nav_title', ''),
                'nav_url' => input('nav_url', ''),
                'sort' => input('sort', ''),
                'is_blank' => input('is_blank', ''),
                'nav_icon' => input('nav_icon', ''),
                'is_show' => input('is_show', ''),
                'create_time' => time(),
                'site_id' => $this->site_id
            ];

            return $model->addNav($data);
        } else {
            $link_list = $model->getLink();
            $this->assign('link', $link_list);

            return $this->fetch('pc/add_nav');
        }
    }

    /**
     * 编辑导航
     * @return mixed
     */
    public function editNav()
    {
        $model = new PcModel();
        if (request()->isJson()) {
            $data = [
                'nav_title' => input('nav_title', ''),
                'nav_url' => input('nav_url', ''),
                'sort' => input('sort', ''),
                'is_blank' => input('is_blank', ''),
                'nav_icon' => input('nav_icon', ''),
                'is_show' => input('is_show', ''),
                'modify_time' => time(),
            ];
            $id = input('id', 0);
            $condition = [
                [ 'id', '=', $id ],
                [ 'site_id', '=', $this->site_id ]
            ];

            return $model->editNav($data, $condition);
        } else {
            $link_list = $model->getLink();
            $this->assign('link', $link_list);

            $id = input('id', 0);
            $this->assign('id', $id);

            $nav_info = $model->getNavInfo($id);
            $this->assign('nav_info', $nav_info[ 'data' ]);

            return $this->fetch('pc/edit_nav');
        }
    }

    /**
     * 删除导航
     * @return mixed
     */
    public function deleteNav()
    {
        if (request()->isJson()) {
            $id = input('id', 0);
            $model = new PcModel();
            return $model->deleteNav([ [ 'id', '=', $id ], [ 'site_id', '=', $this->site_id ] ]);
        }
    }

    /**
     * 修改排序
     */
    public function modifySort()
    {
        if (request()->isJson()) {
            $sort = input('sort', 0);
            $id = input('id', 0);
            $model = new PcModel();
            return $model->modifyNavSort($sort, $id);
        }
    }

    public function modifyNavIsShow()
    {
        if (request()->isJson()) {
            $is_show = input('is_show', 0);
            $id = input('id', 0);
            $model = new PcModel();
            return $model->editNav([ 'is_show' => $is_show ], [ [ 'id', '=', $id ] ]);
        }
    }

    /**
     * 友情链接
     * @return mixed
     */
    public function linklist()
    {
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');

            $condition = [
                [ 'site_id', '=', $this->site_id ]
            ];
            if (!empty($search_text)) $condition[] = [ 'link_title', 'like', '%' . $search_text . '%' ];

            //排序
            $link_sort = input('order', 'link_sort');
            $sort = input('sort', 'desc');
            if ($link_sort == 'link_sort') {
                $order_by = $link_sort . ' ' . $sort;
            } else {
                $order_by = $link_sort . ' ' . $sort . ',link_sort desc';
            }

            $model = new PcModel();
            return $model->getLinkPageList($condition, $page, $page_size, $order_by);
        } else {
            return $this->fetch('pc/link_list');
        }
    }

    /**
     * 添加友情链接
     * @return mixed
     */
    public function addLink()
    {
        $model = new PcModel();
        if (request()->isJson()) {
            $data = [
                'link_title' => input('link_title', ''),
                'link_url' => input('link_url', ''),
                'link_pic' => input('link_pic', ''),
                'link_sort' => input('link_sort', ''),
                'is_blank' => input('is_blank', ''),
                'is_show' => input('is_show', ''),
                'site_id' => $this->site_id
            ];

            return $model->addLink($data);
        } else {
            return $this->fetch('pc/add_link');
        }
    }

    /**
     * 编辑友情链接
     * @return mixed
     */
    public function editLink()
    {
        $model = new PcModel();
        if (request()->isJson()) {
            $data = [
                'link_title' => input('link_title', ''),
                'link_url' => input('link_url', ''),
                'link_pic' => input('link_pic', ''),
                'link_sort' => input('link_sort', ''),
                'is_blank' => input('is_blank', ''),
                'is_show' => input('is_show', ''),
            ];
            $id = input('id', 0);
            $condition = [
                [ 'id', '=', $id ],
                [ 'site_id', '=', $this->site_id ]
            ];
            return $model->editLink($data, $condition);
        } else {

            $id = input('id', 0);
            $this->assign('id', $id);

            $link_info = $model->getLinkInfo($id);
            $this->assign('link_info', $link_info[ 'data' ]);

            return $this->fetch('pc/edit_link');
        }
    }

    /**
     * 删除友情链接
     * @return mixed
     */
    public function deleteLink()
    {
        if (request()->isJson()) {
            $id = input('id', 0);
            $model = new PcModel();
            return $model->deleteLink([ [ 'id', '=', $id ], [ 'site_id', '=', $this->site_id ] ]);
        }
    }

    /**
     * 修改排序
     */
    public function modifyLinkSort()
    {
        if (request()->isJson()) {
            $sort = input('sort', 0);
            $id = input('id', 0);
            return $this->pc_model->modifyLinkSort($sort, $id);
        }
    }

    /**
     * 首页楼层
     * @return array|mixed
     */
    public function floor()
    {
        if (request()->isJson()) {
            $page = input('page', 1);
            $page_size = input('page_size', PAGE_LIST_ROWS);
            $search_text = input('search_text', '');
            $condition = [
                [ 'pf.site_id', '=', $this->site_id ]
            ];
            if (!empty($search_text)) $condition[] = [ 'pf.title', 'like', '%' . $search_text . '%' ];
            $list = $this->pc_model->getFloorPageList($condition, $page, $page_size);
            return $list;
        } else {
            return $this->fetch('pc/floor');
        }
    }

    /**
     * 修改首页楼层排序
     */
    public function modifyFloorSort()
    {
        if (request()->isJson()) {
            $sort = input('sort', 0);
            $id = input('id', 0);
            $condition = array (
                [ 'id', '=', $id ],
                [ 'site_id', '=', $this->site_id ]
            );
            $res = $this->pc_model->modifyFloorSort($sort, $condition);
            return $res;
        }
    }

    /**
     * 删除首页楼层
     * @return array
     */
    public function deleteFloor()
    {
        if (request()->isJson()) {
            $id = input('id', 0);
            $res = $this->pc_model->deleteFloor([ [ 'id', '=', $id ], [ 'site_id', '=', $this->site_id ] ]);
            return $res;
        }
    }

    /**
     * 编辑楼层
     * @return mixed
     */
    public function editFloor()
    {
        if (request()->isJson()) {
            $id = input("id", 0);
            $data = [
                'block_id' => input("block_id", 0), //楼层模板关联id
                'title' => input("title", ''), // 楼层标题
                'value' => input("value", ''),
                'state' => input("state", 0),// 状态（0：禁用，1：启用）
                'sort' => input("sort", 0), //排序号
                'site_id' => $this->site_id
            ];
            if ($id == 0) {
                $res = $this->pc_model->addFloor($data);
            } else {
                $res = $this->pc_model->editFloor($data, [ [ 'id', '=', $id ], [ 'site_id', '=', $this->site_id ] ]);
            }
            return $res;
        } else {
            $id = input("id", 0);
            $this->assign("id", $id);

            if (!empty($id)) {
                $floor_info = $this->pc_model->getFloorDetail($id, $this->site_id);
                $floor_info = $floor_info[ 'data' ];
                $this->assign("floor_info", $floor_info);
            }

            $floor_block_list = $this->pc_model->getFloorBlockList();
            $floor_block_list = $floor_block_list[ 'data' ];
            $this->assign("floor_block_list", $floor_block_list);

            $pc_link = $this->pc_model->getLink();
            $this->assign("pc_link", $pc_link);

            $goods_category_model = new GoodsCategoryModel();
            $category_list = $goods_category_model->getCategoryTree([ [ 'site_id', '=', $this->site_id ] ]);
            $category_list = $category_list[ 'data' ];
            $this->assign("category_list", $category_list);
            return $this->fetch('pc/edit_floor');
        }
    }

    /**
     * PC端首页分类设置
     * @return array|mixed
     */
    public function category()
    {
        $config_model = new Config();
        if (request()->isJson()) {
            $data = array (
                "category" => input("category", "1"),
                "img" => input("img", "0")
            );
            $res = $config_model->setCategoryConfig($data, $this->site_id, $this->app_module);
            return $res;
        } else {
            $config_info = $config_model->getCategoryConfig($this->site_id, $this->app_module);
            $this->assign('config_info', $config_info[ 'data' ][ 'value' ]);
            return $this->fetch('pc/category');
        }
    }

}
