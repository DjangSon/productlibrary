<?php
// 系统基本配置 **********************************************
$_CONFIG['HttpPath']          = true;           // 是否开启 Controller/Action/name/value 模式
$_CONFIG['XSS']               = true;           // 是否开启 XSS防范
$_CONFIG['SessionStart']      = true;           // 是否开启 SESSION
$_CONFIG['SessionExpire']     = 1440;			// SESSION过期时间
$_CONFIG['DebugPhp']          = true;           // 是否开启PHP运行报错信息
$_CONFIG['DebugSql']          = true;           // 是否开启源码调试Sql语句
$_CONFIG['GroupStart']        = true;           // 是否开启 分组模式
$_CONFIG['UrlGroupName']      = 'g';            // 自定义分组名称 例如: index.php?g=index
$_CONFIG['UrlControllerName'] = 'c';            // 自定义控制器名称 例如: index.php?g=index&c=index
$_CONFIG['UrlActionName']     = 'a';            // 自定义方法名称 例如: index.php?g=index&c=index&a=index
$_CONFIG['Locale']            = 'zh_CN';        // 自定义语言环境 例如：zh_CN, en_US, en_GB
$_CONFIG['LocaleAuto']        = false;          // 是否自动获取浏览器语言环境
$_CONFIG['IsMobile']          = true;           // 是否是用M端

// 默认使用数据库配置 *****************************************
$_CONFIG['DB'] = array(
    'host'       => '192.168.1.40',   // Mysql主机地址
    'username'   => 'hlj-admin',      // Mysql用户
    'password'   => '123456',         // Mysql密码
    'dbname'     => 'productlibrary', // 数据库名称
    'persistent' => false             // 使用长连接
);
