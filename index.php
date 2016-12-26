<?php
/************************************************
 * 系统目录定义
 */
define('APP_PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('TITAN_PATH', APP_PATH . 'Titan/');
define('CLASS_PATH', APP_PATH . 'Class/');
define('VAR_PATH', APP_PATH . 'Var/');
define('CONTROLLER_PATH', APP_PATH . 'Controller/');
define('MODEL_PATH', APP_PATH . 'Model/');
define('VIEW_PATH', APP_PATH . 'View/');
/************************************************
 * 图片路径
 */
define('IMG_PATH', APP_PATH);
/************************************************
 * 系统网址定义
 */
define('APP_HOST', (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http://' : 'https://') . $_SERVER['HTTP_HOST']);
define('APP_CATALOG', str_ireplace('/index.php', '', $_SERVER['SCRIPT_NAME']) . '/');
define('APP_HTTP', APP_HOST . APP_CATALOG);
define('APP_VAR_URL', APP_HTTP . 'Var/');
define('APP_CSS_URL', APP_HTTP . 'View/css/');
define('APP_IMAGES_URL', APP_HTTP . 'View/images/');
define('APP_JS_URL', APP_HTTP . 'View/js/');
/************************************************
 * 加载系统文件
 */
include(TITAN_PATH . 'Config.php');
include(TITAN_PATH . 'Function.php');
include(TITAN_PATH . 'Titan.php');
include(TITAN_PATH . 'Custom.php');
Titan::run();
