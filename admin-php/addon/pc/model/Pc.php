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

namespace addon\pc\model;


use app\model\BaseModel;
use app\model\goods\Goods as GoodsModel;
use app\model\goods\GoodsCategory as GoodsCategoryModel;
use app\model\system\Api;
use app\model\system\Config as ConfigModel;
use app\model\web\Config as WebConfig;
use think\db\Raw;
use think\facade\Cache;
use think\facade\Db;

/**
 * PC端
 * @author Administrator
 *
 */
class Pc extends BaseModel
{

    private $link = [
        [
            'title' => '首页',
            'url' => '/'
        ],
        [
            'title' => '登录',
            'url' => '/auth/login'
        ],
        [
            'title' => '注册',
            'url' => '/auth/register'
        ],
        [
            'title' => '找回密码',
            'url' => '/auth/find'
        ],
        [
            'title' => '公告列表',
            'url' => '/cms/notice/list'
        ],
        [
            'title' => '文章列表',
            'url' => '/cms/article/list'
        ],
        [
            'title' => '帮助中心',
            'url' => '/cms/help/list'
        ],
        [
            'title' => '购物车',
            'url' => '/goods/cart'
        ],
        [
            'title' => '领券中心',
            'url' => '/goods/coupon'
        ],
        [
            'title' => '商品分类',
            'url' => '/goods/category'
        ],
        [
            'title' => '商品列表',
            'url' => '/goods/list'
        ],
        [
            'title' => '品牌专区',
            'url' => '/goods/brand'
        ],
        [
            'title' => '团购专区',
            'url' => '/promotion/groupbuy'
        ],
        [
            'title' => '秒杀专区',
            'url' => '/promotion/seckill'
        ],
        [
            'title' => '会员中心',
            'url' => '/member'
        ]

    ];

    private $web_demain = __ROOT__ . '/web';

    private $not_found_file_error = "未找到源码包，请检查目录文件";

    /*************************************************网站部署******************************************/

    /**
     * 默认部署：无需下载，一键刷新，API接口请求地址为当前域名，编译代码存放到web文件夹中
     * @return array
     */
    public function downloadCsDefault()
    {
        try {
            $path = 'addon/pc/source/cs_default';
            $web_path = 'web'; // web端生成目录
            if (!is_dir($path) || count(scandir($path)) <= 3) {
                return $this->error('', $this->not_found_file_error);
            }

            if (is_dir($web_path)) {
                // 先将之前的文件删除
                if (count(scandir($web_path)) > 1) deleteDir($web_path);
            } else {
                // 创建web目录
                mkdir($web_path, intval('0777', 8), true);
            }

            // 将原代码包拷贝到web目录下
            recurseCopy($path, $web_path);
            $this->dealWithParamReplace($web_path);
            file_put_contents($web_path . '/refresh.log', time());
            return $this->success();
        } catch (\Exception $e) {
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 独立部署：下载编译代码包，参考开发文档进行配置
     * @param $domain
     * @return array
     */
    public function downloadCsSeparate($domain)
    {
        $this->web_demain = $domain;
        try {
            $path = 'addon/pc/source/cs_separate';
            $source_file_path = 'upload/web/cs_separate'; // web端生成目录
            if (!is_dir($path) || count(scandir($path)) <= 3) {
                return $this->error('', $this->not_found_file_error);
            }

            if (is_dir($source_file_path)) {
                // 先将之前的文件删除
                if (count(scandir($source_file_path)) > 2) deleteDir($source_file_path);
            } else {
                // 创建web目录
                mkdir($source_file_path, intval('0777', 8), true);
            }

            // 将原代码包拷贝到web目录下
            recurseCopy($path, $source_file_path);
            $this->dealWithParamReplace($source_file_path);

            // 生成压缩包
            $file_arr = getFileMap($source_file_path);

            if (!empty($file_arr)) {
                $zipname = 'web_cs_separate_' . date('YmdHi') . '.zip';
                $zip = new \ZipArchive();
                $res = $zip->open($zipname, \ZipArchive::CREATE);
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

                    header("Content-Type: application/zip");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($zipname));
                    header("Content-Disposition: attachment; filename=\"" . basename($zipname) . "\"");
                    readfile($zipname);
                    @unlink($zipname);
                }
            }
            return $this->success();
        } catch (\Exception $e) {
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 源码下载：下载开源代码包，参考开发文档进行配置，结合业务需求进行二次开发
     * @return array
     */
    public function downloadOs()
    {
        try {
            $source_file_path = 'addon/pc/source/os';
            if (!is_dir($source_file_path) || count(scandir($source_file_path)) <= 3) {
                return $this->error('', $this->not_found_file_error);
            }
            $file_arr = getFileMap($source_file_path);

            if (!empty($file_arr)) {
                $zipname = 'web_os_' . date('YmdHi') . '.zip';
                $zip = new \ZipArchive();
                $res = $zip->open($zipname, \ZipArchive::CREATE);
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

                    header("Content-Type: application/zip");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($zipname));
                    header("Content-Disposition: attachment; filename=\"" . basename($zipname) . "\"");
                    readfile($zipname);
                    @unlink($zipname);
                }
            }
            return $this->success();
        } catch (\Exception $e) {
            return $this->error('', $e->getMessage());
        }
    }

    /**
     * 替换配置信息，API请求域名地址、图片、密钥等
     * @param $source_path
     * @param string $domain
     */
    private function dealWithParamReplace($source_path)
    {
        //处理js文件中的变量替换
        $js_path = $source_path . '/_nuxt';
        $files = scandir($js_path);
        foreach ($files as $path) {
            if ($path != '.' && $path != '..') {
                $temp_path = $js_path . '/' . $path;
                if (file_exists($temp_path)) {
                    if (preg_match("/(\w{7})(.js)$/", $temp_path)) {
                        $content = file_get_contents($temp_path);
                        $content = $this->paramReplace($content);
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
    private function paramReplace($string)
    {
        $api_model = new Api();
        $api_config = $api_model->getApiConfig()[ 'data' ];

        $web_config_model = new WebConfig();
        $web_config = $web_config_model->getMapConfig()[ 'data' ][ 'value' ];

        $web_socket = ( strstr(__ROOT__, 'https://') === false ? str_replace('http', 'ws', __ROOT__) : str_replace('https', 'wss', __ROOT__) ) . '/wss';

        $patterns = [
            '/\{\{\$baseUrl\}\}/',
            '/\{\{\$imgDomain\}\}/',
            '/\{\{\$webDomain\}\}/',
            '/\{\{\$mpKey\}\}/',
            '/\{\{\$apiSecurity\}\}/',
            '/\{\{\$publicKey\}\}/',
            '/\{\{\$webSocket\}\}/'
        ];
        $replacements = [
            __ROOT__,
            __ROOT__,
            $this->web_demain,
            $web_config[ 'tencent_map_key' ] ?? '',
            $api_config[ 'is_use' ] ?? 0,
            $api_config[ 'value' ][ 'public_key' ] ?? '',
            $web_socket
        ];
        $string = preg_replace($patterns, $replacements, $string);
        return $string;
    }

    /**
     * 获取PC端固定跳转链接
     * @return array
     */
    public function getLink()
    {
        foreach ($this->link as $k => $v) {
            if ($v[ 'url' ] == '/auth/register' && addon_is_exit('memberregister') == 0) {
                unset($this->link[ $k ]);
            } elseif ($v[ 'url' ] == '/goods/coupon' && addon_is_exit('coupon') == 0) {
                unset($this->link[ $k ]);
            } elseif ($v[ 'url' ] == '/promotion/seckill' && addon_is_exit('seckill') == 0) {
                unset($this->link[ $k ]);
            } elseif ($v[ 'url' ] == '/promotion/groupbuy' && addon_is_exit('groupbuy') == 0) {
                unset($this->link[ $k ]);
            }
        }
        $this->link = array_values($this->link);
        return $this->link;
    }

    /**
     * 设置首页浮层
     * @param $data
     * @return array
     */
    public function setFloatLayer($data, $site_id, $app_module = 'shop')
    {
        $config = new ConfigModel();
        $res = $config->setConfig($data, 'PC端首页浮层', 1, [ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', 'PC_INDEX_FLOAT_LAYER_CONFIG' ] ]);
        return $res;
    }

    /**
     * 获取首页浮层
     * @param $data
     * @return array
     */
    public function getFloatLayer($site_id, $app_module = 'shop')
    {
        $config = new ConfigModel();
        $res = $config->getConfig([ [ 'site_id', '=', $site_id ], [ 'app_module', '=', $app_module ], [ 'config_key', '=', 'PC_INDEX_FLOAT_LAYER_CONFIG' ] ]);
        if (empty($res[ 'data' ][ 'value' ])) {
            $res[ 'data' ][ 'value' ] = [
                'title' => '首页浮层',
                'url' => '{"title":"\u81ea\u5b9a\u4e49","url":"https:\/\/www.niushop.com"}',
                'is_show' => 1,
                'number' => '3',
                'img_url' => 'public/static/img/pc/index_float_layer.png'
            ];
        } else {
            if (is_null(json_decode($res[ 'data' ][ 'value' ][ 'url' ]))) {
                $res[ 'data' ][ 'value' ][ 'url' ] = '{"title":"\u81ea\u5b9a\u4e49","url":"https:\/\/www.niushop.com"}';
            }
        }

        return $res;
    }

    /*****************************************导航*****************************************/
    /**
     * 添加导航
     * @param array $data
     */
    public function addNav($data)
    {
        $id = model('pc_nav')->add($data);
        Cache::tag("pc_nav")->clear();
        return $this->success($id);
    }

    /**
     * 修改导航
     * @param array $data
     */
    public function editNav($data, $condition)
    {
        $res = model('pc_nav')->update($data, $condition);
        Cache::tag("pc_nav")->clear();
        return $this->success($res);
    }

    /**
     * 删除导航
     * @param unknown $coupon_type_id
     */
    public function deleteNav($condition)
    {
        $res = model('pc_nav')->delete($condition);
        Cache::tag("pc_nav")->clear();
        return $this->success($res);
    }

    /**
     * 获取导航详情
     * @param int $id
     * @return multitype:string mixed
     */
    public function getNavInfo($id)
    {
        $res = model('pc_nav')->getInfo([ [ 'id', '=', $id ] ], 'id, nav_title, nav_url, sort, is_blank, create_time, modify_time, nav_icon, is_show');
        return $this->success($res);
    }

    /**
     * 获取导航详情
     * @param int $id
     * @return multitype:string mixed
     */
    public function getNavInfoByCondition($condition, $field = 'id, nav_title, nav_url, sort, is_blank, create_time, modify_time, nav_icon, is_show')
    {
        $res = model('pc_nav')->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 获取导航分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getNavPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'create_time desc', $field = 'id, nav_title, nav_url, sort, is_blank, create_time, modify_time, nav_icon, is_show')
    {
        $list = model('pc_nav')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 获取导航列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getNavList($condition = [], $field = 'id,nav_title,nav_url,sort,is_blank,create_time,modify_time,nav_icon,is_show', $order = 'create_time desc')
    {
        $list = model('pc_nav')->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 修改排序
     * @param int $sort
     * @param int $id
     */
    public function modifyNavSort($sort, $id)
    {
        $res = model('pc_nav')->update([ 'sort' => $sort ], [ [ 'id', '=', $id ] ]);
        Cache::tag('pc_nav')->clear();
        return $this->success($res);
    }

    /*****************************************友情链接*****************************************/

    /**
     * 添加友情链接
     * @param array $data
     */
    public function addLink($data)
    {
        $id = model('pc_friendly_link')->add($data);
        Cache::tag("pc_friendly_link")->clear();
        return $this->success($id);
    }

    /**
     * 修改友情链接
     * @param array $data
     */
    public function editLink($data, $condition)
    {
        $res = model('pc_friendly_link')->update($data, $condition);
        Cache::tag("pc_friendly_link")->clear();
        return $this->success($res);
    }

    /**
     * 删除友情链接
     * @param unknown $coupon_type_id
     */
    public function deleteLink($condition)
    {
        $res = model('pc_friendly_link')->delete($condition);
        Cache::tag("pc_friendly_link")->clear();
        return $this->success($res);
    }

    /**
     * 获取友情链接详情
     * @param int $id
     * @return multitype:string mixed
     */
    public function getLinkInfo($id)
    {
        $res = model('pc_friendly_link')->getInfo([ [ 'id', '=', $id ] ], 'id, link_title, link_url, link_pic, link_sort, is_blank, is_show');
        return $this->success($res);
    }

    /**
     * 获取导航分页列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getLinkPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'link_sort desc', $field = 'id, link_title, link_url, link_pic, link_sort, is_blank, is_show')
    {
        $list = model('pc_friendly_link')->pageList($condition, $field, $order, $page, $page_size);
        return $this->success($list);
    }

    /**
     * 获取导航列表
     * @param array $condition
     * @param number $page
     * @param string $page_size
     * @param string $order
     * @param string $field
     */
    public function getLinkList($condition = [], $field = true, $order = '')
    {
        $list = model('pc_friendly_link')->getList($condition, $field, $order);
        return $this->success($list);
    }

    /**
     * 修改排序
     * @param $sort
     * @param $id
     * @return array
     */
    public function modifyLinkSort($sort, $id)
    {
        $res = model('pc_friendly_link')->update([ 'link_sort' => $sort ], [ [ 'id', '=', $id ] ]);
        Cache::tag('pc_friendly_link')->clear();
        return $this->success($res);
    }

    /*****************************************首页楼层*****************************************/

    /**
     * 添加楼层模板
     * @param $data
     * @return array
     */
    public function addFloorBlockList($data)
    {
        $res = model("pc_floor_block")->addList($data);
        return $this->success($res);
    }

    /**
     * 获取PC端首页楼层模板
     * @return array
     */
    public function getFloorBlockList()
    {
        $list = model('pc_floor_block')->getList([], 'id,name,title,value,sort');
        return $this->success($list);
    }

    /**
     * 添加楼层
     * @param $data
     * @return array
     */
    public function addFloor($data)
    {
        $data[ 'create_time' ] = time();
        $res = model("pc_floor")->add($data);
        return $this->success($res);
    }

    /**
     * 编辑楼层
     * @param $data
     * @param $condition
     * @return array
     */
    public function editFloor($data, $condition)
    {
        $res = model("pc_floor")->update($data, $condition);
        return $this->success($res);
    }

    /**
     * 修改首页楼层排序
     * @param $sort
     * @param $condition
     * @return array
     */
    public function modifyFloorSort($sort, $condition)
    {
        $res = model('pc_floor')->update([ 'sort' => $sort ], $condition);
        return $this->success($res);
    }

    /**
     * 删除首页楼层
     * @param $condition
     * @return array
     */
    public function deleteFloor($condition)
    {
        $res = model('pc_floor')->delete($condition);
        return $this->success($res);
    }

    /**
     * 获取首页楼层信息
     * @param $condition
     * @param $field
     * @return array
     */
    public function getFloorInfo($condition, $field = 'id,block_id,title,value,state,create_time,sort')
    {
        $res = model("pc_floor")->getInfo($condition, $field);
        return $this->success($res);
    }

    /**
     * 获取首页楼层详情
     * @param $id
     * @return array
     */
    public function getFloorDetail($id, $site_id)
    {
        $goods_model = new GoodsModel();
        $goods_category_model = new GoodsCategoryModel();
        $floor_info = model("pc_floor")->getInfo([ [ 'id', '=', $id ], [ 'site_id', '=', $site_id ] ], 'id,block_id,title,value,state,sort');
        if (!empty($floor_info)) {
            $value = $floor_info[ 'value' ];
            if (!empty($value)) {
                $value = json_decode($value, true);
                foreach ($value as $k => $v) {
                    if (!empty($v[ 'type' ])) {
                        if ($v[ 'type' ] == 'goods') {
                            // 商品
                            $field = 'goods_id,goods_name,goods_image,price,create_time,sku_id,introduction,market_price';
//                            $order = 'sort desc,create_time desc';
                            $order = '';
                            $list = $goods_model->getGoodsList([ [ 'goods_id', 'in', $v[ 'value' ][ 'goods_ids' ] ] ], $field, $order)[ 'data' ];
                            $value[ $k ][ 'value' ][ 'list' ] = $list;
                        } elseif ($v[ 'type' ] == 'category') {
                            // 商品分类
                            $condition = [
                                [ 'category_id', 'in', $v[ 'value' ][ 'category_ids' ] ],
                                [ 'site_id', '=', $site_id ]
                            ];
                            $list = $goods_category_model->getCategoryList($condition, 'category_id,category_name,short_name,image,image_adv',Db::raw("FIELD(category_id, '{$v[ 'value' ][ 'category_ids' ]}')"))[ 'data' ];
                            $value[ $k ][ 'value' ][ 'list' ] = $list;
                        }
                    }
                }
                $floor_info[ 'value' ] = json_encode($value);
            }
        }
        return $this->success($floor_info);
    }

    /**
     * 获取PC端首页楼层列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getFloorList($condition = [], $field = 'pf.id,pf.block_id,pf.title,pf.value,pf.state,pf.create_time,pf.sort,fb.name as block_name,fb.title as block_title', $order = 'pf.sort desc,pf.create_time desc')
    {
        $alias = 'pf';
        $join = [
            [ 'pc_floor_block fb', 'pf.block_id = fb.id', 'inner' ]
        ];

        $list = model('pc_floor')->getList($condition, $field, $order, $alias, $join);
        return $this->success($list);
    }

    /**
     * 获取PC端首页楼层分页列表
     * @param array $condition
     * @param int $page
     * @param int $page_size
     * @param string $order
     * @param string $field
     * @return array
     */
    public function getFloorPageList($condition = [], $page = 1, $page_size = PAGE_LIST_ROWS, $order = 'pf.create_time desc', $field = 'pf.id,pf.block_id,pf.title,pf.value,pf.state,pf.create_time,pf.sort,fb.name as block_name,fb.title as block_title')
    {
        $alias = 'pf';
        $join = [
            [ 'pc_floor_block fb', 'pf.block_id = fb.id', 'inner' ]
        ];

        $list = model('pc_floor')->pageList($condition, $field, $order, $page, $page_size, $alias, $join);
        return $this->success($list);
    }

}