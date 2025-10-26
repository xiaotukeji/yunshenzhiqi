<?php

namespace app\install\controller;


use app\model\shop\Shop;
use app\model\system\Addon;
use app\model\system\Api;
use app\model\system\Group;
use app\model\system\H5;
use app\model\system\Menu;
use app\model\system\Site;
use app\model\system\User;
use think\facade\Cache;
use think\facade\Event;


class Index extends BaseInstall
{

    public function __construct()
    {
        //执行父类构造函数
        parent::__construct();
    }

    /**
     *安装
     */
    public function index()
    {
        if (file_exists($this->lock_file)) {
            $this->redirect(__ROOT__);
        }

        $step = input("step", 1);

        if ($step == 1) {
            return $this->fetch('index/step-1', [], $this->replace);
        } elseif ($step == 2) {
            //系统变量
            $system_variables = [];
            $phpv = phpversion();
            $os = PHP_OS;
            $server = $_SERVER[ 'SERVER_SOFTWARE' ];

            $host = ( empty($_SERVER[ 'REMOTE_ADDR' ]) ? $_SERVER[ 'REMOTE_HOST' ] : $_SERVER[ 'REMOTE_ADDR' ] );
            $name = $_SERVER[ 'SERVER_NAME' ];

            $verison = !( version_compare(PHP_VERSION, '7.1.0') == -1 );
            //pdo
            $pdo = extension_loaded('pdo') && extension_loaded('pdo_mysql');
            $system_variables[] = [ "name" => "pdo", "need" => "开启", "status" => $pdo ];
            //curl
            $curl = extension_loaded('curl') && function_exists('curl_init');
            $system_variables[] = [ "name" => "curl", "need" => "开启", "status" => $curl ];
            //openssl
            $openssl = extension_loaded('openssl');
            $system_variables[] = [ "name" => "openssl", "need" => "开启", "status" => $openssl ];
            //gd
            $gd = extension_loaded('gd');
            $system_variables[] = [ "name" => "GD库", "need" => "开启", "status" => $gd ];
            //fileinfo
            $fileinfo = extension_loaded('fileinfo');
            $system_variables[] = [ "name" => "fileinfo", "need" => "开启", "status" => $fileinfo ];

            $root_path = str_replace("\\", DIRECTORY_SEPARATOR, dirname(__FILE__, 4));
            $root_path = str_replace("/", DIRECTORY_SEPARATOR, $root_path);
            $dirs_list = [
                [ "path" => $root_path, "path_name" => "/", "name" => "整目录" ],
                [ "path" => $root_path . DIRECTORY_SEPARATOR . "public", "path_name" => "public", "name" => "public" ],
                [ "path" => $root_path . DIRECTORY_SEPARATOR . "config", "path_name" => "config", "name" => "config" ],
                [ "path" => $root_path . DIRECTORY_SEPARATOR . 'runtime', "path_name" => "runtime", "name" => "runtime" ],
                [ "path" => $root_path . DIRECTORY_SEPARATOR . 'app/install', "path_name" => "app/install", "name" => "安装目录" ]
            ];
            //目录 可读 可写检测
            $is_dir = true;
            foreach ($dirs_list as $k => $v) {
                $is_readable = is_readable($v[ "path" ]);
                $is_write = is_write($v[ "path" ]);
                $dirs_list[ $k ][ "is_readable" ] = $is_readable;
                $dirs_list[ $k ][ "is_write" ] = $is_write;
                if ($is_readable == false || $is_write == false) {
                    $is_dir = false;
                }
            }
            $this->assign("root_path", $root_path);
            $this->assign("system_variables", $system_variables);
            $this->assign("phpv", $phpv);
            $this->assign("server", $server);
            $this->assign("host", $host);
            $this->assign("os", $os);
            $this->assign("name", $name);
            $this->assign("verison", $verison);
            $this->assign("dirs_list", $dirs_list);
            if ($verison && $pdo && $curl && $openssl && $gd && $fileinfo && $is_dir) {
                $continue = true;
            } else {
                $continue = false;
            }
            $this->assign("continue", $continue);
            return $this->fetch('index/step-2', [], $this->replace);
        } elseif ($step == 3) {
            return $this->fetch('index/step-3', [], $this->replace);
        } elseif ($step == 4) {
            set_time_limit(300);
            $source_file = "./app/install/source/database.php";//源配置文件

            $target_dir = "./config";
            $target_file = "database.php";

            $file_name = "./app/install/source/database.sql";//数据文件

            //数据库
            $dbport = input("dbport", "3306");
            $dbhost = input("dbhost", "localhost");
            $dbuser = input("dbuser", "root");
            $dbpwd = input("dbpwd", "root");
            $dbname = input("dbname", "niushop_b2c_v5");//数据库名称
            $dbprefix = input("dbprefix", "");//前缀

            //平台
            $site_name = input('site_name', "");
            $username = input('username', "");
            $password = input('password', "");
            $password2 = input('password2', "");
            $yanshi = input('yanshi', "");// 演示数据开关

            if ($dbhost == '' || $dbuser == '') {
                return $this->returnError([], '数据库链接配置信息丢失！');
            }

//			if ($dbprefix == '') {
//				return $this->returnError('数据表前缀为空！');
//			}

            //可写测试
            $write_result = is_write($target_dir);
            if (!$write_result) {
                //判断是否有可写的权限，linux操作系统要注意这一点，windows不必注意。
                return $this->returnError([], '配置文件不可写，权限不够！');
            }

            //数据库连接测试
            $conn = @mysqli_connect($dbhost, $dbuser, $dbpwd, "", $dbport);
            if (!$conn) {
                return $this->returnError([], '连接数据库失败！请检查连接参数！');
            }

            //平台
            if ($site_name == '' || $username == '' || $password == '') {
                return $this->returnError([], '平台信息不能为空！');
            }

            if ($password != $password2) {
                return $this->returnError([], '两次密码输入不一样，请重新输入');
            }

            //数据库可写和是否存在测试
            $empty_db = mysqli_select_db($conn, $dbname);
            if ($empty_db) {
                $sql = "DROP DATABASE `$dbname`";
                $retval = mysqli_query($conn, $sql);
                if (!$retval) {
                    return $this->returnError([], '删除数据库失败: ' . mysqli_error($conn));
                }
            }

            //如果数据库不存在，我们就进行创建。
            $dbsql = "CREATE DATABASE `$dbname`";
            $db_create = mysqli_query($conn, $dbsql);
            if (!$db_create) {
                return $this->returnError([], '创建数据库失败，请确认是否有足够的权限！');
            }

            //链接数据库
            @mysqli_select_db($conn, $dbname);

            //修改配置文件
            $fp = fopen($source_file, "r");
            $configStr = fread($fp, filesize($source_file));
            fclose($fp);

            $configStr = str_replace('model_hostname', $dbhost, $configStr);
            $configStr = str_replace('model_database', $dbname, $configStr);
            $configStr = str_replace("model_username", $dbuser, $configStr);
            $configStr = str_replace("model_password", $dbpwd, $configStr);
            $configStr = str_replace("model_port", $dbport, $configStr);
            $configStr = str_replace("model_prefix", $dbprefix, $configStr);

            $fp = fopen($target_dir . DIRECTORY_SEPARATOR . $target_file, "w");
            if ($fp == false) {
                return $this->returnError([], '写入配置失败，请检查' . $target_dir . '/' . $target_file . '是否可写入！');
            }

            fwrite($fp, $configStr);
            fclose($fp);

            //导入SQL并执行。
            $get_sql_data = file_get_contents($file_name);
            $sql_query = $this->getSqlQuery($get_sql_data);
            @mysqli_query($conn, "SET NAMES utf8");
            $query_count = count($sql_query);
            for ($i = 0; $i < $query_count; $i++) {
                $sql = trim($sql_query[ $i ]);
                if (strstr($sql, 'CREATE TABLE')) {
                    $match_item = preg_match('/CREATE TABLE [`]?(\\w+)[`]?/is', $sql, $match_data);
                } elseif (strstr($sql, 'ALTER TABLE')) {
                    $match_item = preg_match('/ALTER TABLE [`]?(\\w+)[`]?/is', $sql, $match_data);

                } elseif (strstr($sql, 'INSERT INTO')) {
                    $match_item = preg_match('/INSERT INTO [`]?(\\w+)[`]?/is', $sql, $match_data);
                } else {
                    $match_item = 0;
                }
                if ($match_item > 0) {
                    try {
                        $table_name = $match_data[ "1" ];
                        $new_table_name = $dbprefix . $table_name;
                        $sql_item = $this->str_replace_first($table_name, $new_table_name, $sql);
                        @mysqli_query($conn, $sql_item);
                    } catch (\Exception $e) {
                        return $this->returnError([], '数据库解析失败' . $e->getMessage());
                    }
                }
            }

            @mysqli_close($conn);
            $database_config = include $target_dir . DIRECTORY_SEPARATOR . $target_file;
            \think\facade\Config::set($database_config, "database");

            //gateway配置
            $this->initGatewayConfig();

            //安装菜单
            $menu = new Menu();

            $shop_menu_res = $menu->refreshMenu('shop', '');
            if ($shop_menu_res[ "code" ] < 0) {
                return $this->returnError([], '店铺菜单失败！');
            }

            //安装插件
            $addon_model = new Addon();
            $addon_result = $addon_model->installAllAddon();
            if ($addon_result[ "code" ] < 0) {
                return $this->returnError([], $addon_result[ "message" ]);
            }

            $this->init_data = include "./app/install/source/init.php";//源配置文件

            $initdata_result = $this->initData(input());
            if ($initdata_result[ "code" ] < 0) {
                return $this->returnError([], '默认数据添加失败！');
            }

            // H5端刷新
            $h5 = new H5();
            $h5_res = $h5->refresh();
            if ($h5_res[ 'code' ] < 0) {
                return $this->returnError([], 'h5部署失败！');
            }

            //添加店铺
            $site_data = [
                'site_type' => 'shop',
                'create_time' => time(),
                'site_name' => $site_name,
                'username' => $username
            ];
            $site_model = new Site();
            $site_result = $site_model->addSite($site_data);
            if ($site_result[ 'code' ] < 0) {
                return $this->returnError([], '默认站点添加失败！');
            }

            $site_id = $site_result[ 'data' ];

            $shop_data = [
                'site_id' => $site_id,
                'shop_status' => 1
            ];
            $shop_model = new Shop();
            $shop_result = $shop_model->addShop($shop_data);
            if ($shop_result[ 'code' ] < 0) {
                return $this->returnError([], '默认店铺添加失败！');
            }

            // 添加默认数据
            $default_result = $this->defaultData($site_id);
            if ($default_result[ 'code' ] < 0) {
                return $default_result;
            }

            //添加系统用户组
            $group_model = new Group();
            $group_data = array (
                "site_id" => $site_id,
                "app_module" => "shop",
                "group_name" => "系统管理员",
                "group_status" => 1,
                "is_system" => 1,
                "menu_array" => "",
                "desc" => "",
            );
            $group_result = $group_model->addGroup($group_data);
            if ($group_result[ "code" ] < 0) {
                return $this->returnError([], '后台管理员权限组添加失败！');
            }

            $group_id = $group_result[ "data" ];
            $user_model = new User();
            $user_data = array (
                "app_module" => "shop",
                "app_group" => 0,
                "is_admin" => 1,
                "site_id" => $site_id,
                "group_id" => $group_id,
                "username" => $username,
                "password" => $password
            );
            $user_result = $user_model->addUser($user_data);
            if ($user_result[ "code" ] < 0) {
                return $this->returnError([], '后台管理员添加失败！');
            }

            if ($yanshi) {
                // 演示数据
                $yanshi_data_result = $this->yanShiData($site_id);
                if ($yanshi_data_result[ "code" ] < 0) {
                    return $this->returnError([], '演示数据添加失败！');
                }
            }

            $fp = fopen($this->lock_file, "w");
            if ($fp == false) {
                return $this->returnError([], "写入失败，请检查目录" . dirname(__FILE__, 2) . "是否可写入！'");
            }
            fwrite($fp, '已安装');
            fclose($fp);
            return $this->returnSuccess([], "安装成功");
        }
    }

    public function installSuccess()
    {
        return $this->fetch('index/step-4', [], $this->replace);
    }

    /**
     * 测试数据库
     */
    public function testDb($dbhost = '', $dbport = '', $dbuser = '', $dbpwd = '', $dbname = '')
    {
        $dbport = input("dbport", "");
        $dbhost = input("dbhost", "");
        $dbuser = input("dbuser", "");
        $dbpwd = input("dbpwd", "");
        $dbname = input("dbname", "");
        try {

            if ($dbport != "" && $dbhost != "") {
                $dbhost = $dbport != '3306' ? $dbhost . ':' . $dbport : $dbhost;
            }

            if ($dbhost == '' || $dbuser == '') {
                return $this->returnError([
                    "status" => -1,
                    "message" => "数据库账号或密码不能为空"
                ]);
            }

            if (!function_exists("mysqli_connect")) {
                return $this->returnError([
                    "status" => -1,
                    "message" => "mysqli扩展类必须开启"
                ]);
            }

            $conn = @mysqli_connect($dbhost, $dbuser, $dbpwd);
            if ($conn) {
                if (empty($dbname)) {
                    $result = [
                        "status" => 1,
                        "message" => "数据库连接成功"
                    ];
                } else {
                    if (@mysqli_select_db($conn, $dbname)) {
                        $result = [
                            "status" => 2,
                            "message" => "数据库存在，系统将覆盖数据库"
                        ];
                    } else {
                        $result = [
                            "status" => 1,
                            "message" => "数据库不存在,系统将自动创建"
                        ];
                    }
                }
            } else {
                $result = [
                    "status" => -1,
                    "message" => "数据库连接失败！"
                ];

            }
            @mysqli_close($conn);
            return $this->returnSuccess($result);
        } catch (\Exception $e) {
            $result = [
                "status" => -1,
                "message" => $e->getMessage()
            ];
            return $this->returnSuccess($result);
        }
    }

    /**
     * @param sql文件 $sql_data
     * @return array
     */
    public function getSqlQuery($sql_data)
    {
        $sql_data = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql_data);

        $sql_data = str_replace("\r", "\n", $sql_data);
        $sql_query = [];
        $num = 0;
        $sql_arr = explode(";\n", trim($sql_data));
        unset($sql);
        foreach ($sql_arr as $sql) {
            $sql_query[ $num ] = '';
            $sqls = explode("\n", trim($sql));
            $sqls = array_filter($sqls);
            foreach ($sqls as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-')
                    $sql_query[ $num ] .= $query;
            }
            $num++;
        }
        return $sql_query;
    }

    /**
     * 初始化平台数据
     */
    private function initData($param)
    {
        $init_event_result = $this->initEvent();
        if ($init_event_result[ 'code' ] < 0) {
            return $init_event_result;
        }

        // 初始化自定义组件、链接
        $diyview_result = $this->initDiyView();
        if ($diyview_result[ 'code' ] < 0) {
            return $this->returnError([], '自定义组件初始化失败！');
        }

        $api_model = new Api();
        $data = array (
            "public_key" => $this->init_data[ 'api' ][ 'public_key' ],
            "private_key" => $this->init_data[ 'api' ][ 'private_key' ],
        );
        $api_result = $api_model->setApiConfig($data, 1);
        if ($api_result[ 'code' ] < 0) {
            return $this->returnError([], 'api秘钥配置失败！');
        }

        return $this->returnSuccess();
    }

    /**
     * 添加店铺默认数据
     */
    private function defaultData($site_id)
    {
        // 添加店铺相册默认分组
        model("album")->add([ 'site_id' => $site_id, 'type' => 'video', 'album_name' => "默认分组", 'update_time' => time(), 'is_default' => 1, 'level' => 1 ]);
        $result = model("album")->add([ 'site_id' => $site_id, 'type' => 'img', 'album_name' => "默认分组", 'update_time' => time(), 'is_default' => 1, 'level' => 1 ]);
        if ($result === false) {
            return $this->returnError([], '默认相册创建失败！');
        }

        //执行事件
        $add_site_result = event("AddSite", [ 'site_id' => $site_id ]);
        if (!empty($add_site_result)) {
            foreach ($add_site_result as $site_item) {
                if (!empty($site_item) && $site_item[ 'code' ] < 0) {
                    return $this->returnError([], $site_item[ 'message' ]);
                }
            }
        }
        return $this->returnSuccess();
    }

    /**
     * 演示数据
     * @param $sys_uid
     * @return array
     */
    private function yanShiData($site_id)
    {
        $result_array = event("AddYanshiData", [ 'site_id' => $site_id ]);
        if (!empty($result_array)) {
            foreach ($result_array as $item) {
                if (!empty($item) && $item[ 'code' ] < 0) {
                    return $this->returnError([], $item[ 'message' ]);
                }
            }
        }
        return $this->returnSuccess();
    }

    /**
     * 初始化自定义组件、链接
     * @return array
     */
    private function initDiyView()
    {
        $addon = new Addon();
        $menu = new Menu();

        $menu->truncateDiyView();
        $res = $addon->refreshDiyView('');

        $addon_list = $addon->getAddonList([], 'name')[ 'data' ];
        foreach ($addon_list as $k => $v) {
            $addon->refreshDiyView($v[ 'name' ]);
        }
        return $res;
    }

    /**
     * 初始化插件
     */
    private function initEvent()
    {
        try {
            $cache = Cache::get("addon_event_list");

            if (empty($cache)) {
                $addon_model = new Addon();
                $addon_data = $addon_model->getAddonList([], 'name');

                $listen_array = [];
                foreach ($addon_data[ 'data' ] as $k => $v) {
                    $addon_event = require_once 'addon/' . $v[ 'name' ] . '/config/event.php';

                    $listen = $addon_event[ 'listen' ] ?? [];
                    if (!empty($listen)) {
                        $listen_array[] = $listen;
                    }
                }
                Cache::tag("addon")->set("addon_event_list", $listen_array);
            } else {
                $listen_array = $cache;
            }

            if (!empty($listen_array)) {
                foreach ($listen_array as $k => $listen) {
                    if (!empty($listen)) {
                        Event::listenEvents($listen);
                    }

                }
            }
            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError('', $e->getMessage());
        }
    }

    function str_replace_first($search, $replace, $subject)
    {
        return implode($replace, explode($search, $subject, 2));
    }

    /**
     * 初始化gateway配置
     */
    protected function initGatewayConfig()
    {
        $source_file = './app/install/source/gateway.php';
        $target_file  = './config/gateway.php';
        // 读取配置文件
        $fp     = fopen($source_file, 'r');
        $config = fread($fp, filesize($source_file));
        fclose($fp);
        // 替换内容
        $database_config = config('database');
        $config          = str_replace('model_hostname', $database_config['connections']['mysql']['hostname'], $config);
        $config          = str_replace('model_database', $database_config['connections']['mysql']['database'], $config);
        $config          = str_replace('model_username', $database_config['connections']['mysql']['username'], $config);
        $config          = str_replace('model_password', $database_config['connections']['mysql']['password'], $config);
        $config          = str_replace('model_port', $database_config['connections']['mysql']['hostport'], $config);
        $config          = str_replace('model_prefix', $database_config['connections']['mysql']['prefix'], $config);
        // 检测文件是否可写
        $fp = fopen($target_file, 'w');
        if (!$fp) {
            return error(-1, '写入配置失败，请检查' . $target_file . '是否可写入！');
        }
        fwrite($fp, $config);
        fclose($fp);
    }

}