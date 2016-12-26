<?php
/************************************************
 * Titan系统入口
 */
class Titan
{
	/**
	 * 注册表集合
	 *
	 * @var array
	 */
	static private $_registry = array();

	/**
	 * 总进程对象
	 *
	 * @var TitanProcess
	 */
	public static $process;

	/**
	 * 语言环境对象
	 *
	 * @var TitanLocale
	 */
	public static $locale;

	/**
	 * 日志对象
	 * @var TitanLog
	 */
	public static $log;

	/**
	 * 缓存对象
	 *
	 * @var TitanCache
	 */
	public static $cache;

	/**
	 * 数据库对象
	 *
	 * @var TitanDB
	 */
	public static $db;

	/**
	 * 运行
	 *
	 * @return void
	 */
	public static function run()
	{
		global $_CONFIG;
		ini_set('magic_quotes_runtime', false);
		ini_set('session.use_cookies', 'On');
		ini_set('session.use_trans_sid', 'Off');
		ini_set('session.gc_maxlifetime', $_CONFIG['SessionExpire'] < 900 ? $_CONFIG['SessionExpire'] + 900 : $_CONFIG['SessionExpire']);
		date_default_timezone_set('PRC');
		($_CONFIG['DebugPhp'] && error_reporting(E_ALL)) || error_reporting(0);
		ini_set('error_log', VAR_PATH . 'log/php_errors.log');
		($_CONFIG['SessionStart'] && session_save_path(VAR_PATH . 'session/') && session_start() && setcookie(session_name(), session_id(), time() + ($_CONFIG['SessionExpire'] < 900 ? $_CONFIG['SessionExpire'] + 900 : $_CONFIG['SessionExpire']), '/'));
		header('Content-type: text/html;charset=utf-8');
		self::$locale = new TitanLocale();
		if ($_CONFIG['LocaleAuto']
			&& isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$locale = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			self::$locale->setLocale($locale[0]);
		} else {
			self::$locale->setLocale($_CONFIG['Locale']);
		}
		self::$db      = new TitanDB($_CONFIG['DB']);
		self::$cache   = new TitanCache();
		self::$log     = new TitanLog();
		self::$process = new TitanProcess();
		self::$process->dispatch();
	}

	/**
	 * 过滤方法
	 *
	 * @param $array
	 * @param $function
	 * @return array
	 */
	public static function filter(&$array, $function)
	{
		if (!is_array($array)) return $array = $function($array);
		foreach ($array as $key => $value) {
			$array[$key] = is_array($value) ? self::filter($value, $function) : $function($value);
		}
		return $array;
	}

	/**
	 * 注册一个新变量
	 *
	 * @param string $key
	 * @param mixed $value
	 * @throws Mage_Core_Exception
	 */
	public static function register($key, $value)
	{
		if (!isset(self::$_registry[$key])) {
			self::$_registry[$key] = $value;
		}
	}

	/**
	 * 注销一个新变量
	 *
	 * @param string $key
	 */
	public static function unregister($key)
	{
		if (isset(self::$_registry[$key])) {
			if (is_object(self::$_registry[$key]) && (method_exists(self::$_registry[$key], '__destruct'))) {
				self::$_registry[$key]->__destruct();
			}
			self::$_registry[$key] = null;
			unset(self::$_registry[$key]);
		}
	}

	/**
	 * 获取一个变量
	 *
	 * @param string $key
	 * @return mixed
	 */
	public static function registry($key)
	{
		if (isset(self::$_registry[$key])) {
			return self::$_registry[$key];
		}
		return null;
	}

	/**
	 * 首字母大写
	 *
	 * @param string $str
	 * @param string $destSep
	 * @param string $srcSep
	 * @return string
	 */
	public static function ucwords($str, $destSep = '_', $srcSep = '/')
	{
		return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
	}

	/**
	 * 创建目录
	 *
	 * @param string $pathName
	 * @param int $chmod
	 * @return bool
	 */
	public static function mkdir($pathName, $chmod = 0777)
	{
		return is_dir($pathName) || (self::mkdir(dirname($pathName), $chmod) && mkdir($pathName, $chmod));
	}

	/**
	 * 获取URL
	 *
	 * @param  string $path
	 * @param  boolean $sep
	 * @return string
	 */
	public static function getUrl($path = '', $sep = false)
	{
		global $_CONFIG;
		$path = trim($path, '/');
		$url = '';
		if ($_CONFIG['HttpPath']) {
			$url = APP_HTTP . $path . ($sep ? '?' : '');
		} else {
			$param = explode('/', $path);
			$count = count($param);
			$url = APP_HTTP . 'index.php';
			$start = 2;
			if ($_CONFIG['GroupStart']) {
				$start = 3;
				$url .= '?' . $_CONFIG['UrlGroupName'] . '=' . (isset($param[0]) ? $param[0] : 'index');
				$url .= '&' . $_CONFIG['UrlControllerName'] . '=' . (isset($param[1]) ? $param[1] : 'index');
				$url .= '&' . $_CONFIG['UrlActionName'] . '=' . (isset($param[2]) ? $param[2] : 'index');
			} else {
				$url .= '?' . $_CONFIG['UrlControllerName'] . '=' . (isset($param[0]) ? $param[0] : 'index');
				$url .= '&' . $_CONFIG['UrlActionName'] . '=' . (isset($param[1]) ? $param[1] : 'index');
			}
			if ($count > $start) {
				for ($i = $start; $i < $count; $i++) {
					$url .= '&' . (isset($param[$i]) ? $param[$i] : '');
					$url .= '=' . (isset($param[++$i]) ? $param[$i] : '');
					$i++;
				}
			}
			$url .= $sep ? '&' : '';
		}
		return $url;
	}

	/**
	 * 获取排除之后所有GET的URL表达式
	 *
	 * @param string $excludeArr
	 * @return string
	 */
	public static function getAllGetParams($excludeArr = '')
	{
		if (!is_array($excludeArr)) $excludeArr = array();
		$excludeArr = array_merge($excludeArr, array('x', 'y'));
		$url = '';
		if (is_array($_GET) && (count($_GET) > 0)) {
			reset($_GET);
			foreach ($_GET as $key => $val) {
				if (is_array($val) || in_array($key, $excludeArr)) continue;
				if (strlen($val) > 0) {
					$url .= $key . '=' . rawurlencode($val) . '&';
				}
			}

		}
		while (strstr($url, '&&')) $url = str_replace('&&', '&', $url);
		while (strstr($url, '&amp;&amp;')) $url = str_replace('&amp;&amp;', '&amp;', $url);
		return $url;
	}

	/**
	 * 获取客户端IP
	 *
	 * @return string
	 */
	public static function getIp()
	{
		static $realip = '';
		if (!empty($realip)) return $realip;
		if (isset($_SERVER)) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$realip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				if (isset($_SERVER['REMOTE_ADDR'])) {
					$realip = $_SERVER['REMOTE_ADDR'];
				} else {
					$realip = '0.0.0.0';
				}
			}
		} else {
			if (getenv('HTTP_X_FORWARDED_FOR')) {
				$realip = getenv('HTTP_X_FORWARDED_FOR');
			} elseif (getenv('HTTP_CLIENT_IP')) {
				$realip = getenv('HTTP_CLIENT_IP');
			} else {
				$realip = getenv('REMOTE_ADDR');
			}
		}

		return $realip;
	}
}

/************************************************
 * 总进程
 */
class TitanProcess
{
	/**
	 * 控制器对象
	 *
	 * @var TitanController
	 */
	private $_controller;

	/**
	 * 分组名称
	 *
	 * @var string
	 */
	private $_groupName;

	/**
	 * 控制器名称
	 *
	 * @var string
	 */
	private $_controllerName;

	/**
	 * 动作名称
	 *
	 * @var string
	 */
	private $_actionName;

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		global $_CONFIG;
		if ($_CONFIG['HttpPath']) {
			if (isset($_SERVER['PATH_INFO'])) {
				$pathInfo = trim($_SERVER['PATH_INFO'], '/');
			} else {
				$requestUri = (APP_CATALOG == '/' ? trim($_SERVER['REQUEST_URI'], '/') : trim(str_ireplace(APP_CATALOG, '', $_SERVER['REQUEST_URI']), '/'));
				$pos = strpos($requestUri, '?');
				if ($pos) {
					$requestUri = substr($requestUri, 0, $pos);
				}
				$pathInfo = $requestUri;
			}
			$param = explode('/', $pathInfo);
			$count = count($param);
			$start = 2;
			if ($_CONFIG['GroupStart']) $start = 3;
			if ($count > $start) {
				for ($i = $start; $i < $count; ++$i) {
					$_GET[$param[$i]] = isset($param[++$i]) ? $param[$i] : '';
				}
			}
		}
		// 禁用魔法引用
		$magicQuotes = function_exists('get_magic_quotes_gpc') ? get_magic_quotes_gpc() : false;
		if ($magicQuotes) {
			Titan::filter($_GET, 'stripslashes');
			Titan::filter($_POST, 'stripslashes');
			Titan::filter($_COOKIE, 'stripslashes');
			Titan::filter($_FILES, 'stripslashes');
		}
		if ($_CONFIG['GroupStart']) {
			$this->_groupName = (isset($_GET[$_CONFIG['UrlGroupName']]) && !empty($_GET[$_CONFIG['UrlGroupName']])) ? $_GET[$_CONFIG['UrlGroupName']] : ((isset($param[0]) && !empty($param[0])) ? $param[0] : 'index');
			$this->_controllerName = (isset($_GET[$_CONFIG['UrlControllerName']]) && !empty($_GET[$_CONFIG['UrlControllerName']])) ? $_GET[$_CONFIG['UrlControllerName']] : ((isset($param[1]) && !empty($param[1])) ? $param[1] : 'index');
			$this->_actionName = (isset($_GET[$_CONFIG['UrlActionName']]) && !empty($_GET[$_CONFIG['UrlActionName']])) ? $_GET[$_CONFIG['UrlActionName']] : ((isset($param[2]) && !empty($param[2])) ? $param[2] : 'index');
		} else {
			$this->_controllerName = (isset($_GET[$_CONFIG['UrlControllerName']]) && !empty($_GET[$_CONFIG['UrlControllerName']])) ? $_GET[$_CONFIG['UrlControllerName']] : ((isset($param[0]) && !empty($param[0])) ? $param[0] : 'index');
			$this->_actionName = (isset($_GET[$_CONFIG['UrlActionName']]) && !empty($_GET[$_CONFIG['UrlActionName']])) ? $_GET[$_CONFIG['UrlActionName']] : ((isset($param[1]) && !empty($param[1])) ? $param[1] : 'index');
		}
	}

	/**
	 * 获取控制器对象
	 *
	 * @return TitanController
	 */
	public function getController()
	{
		return $this->_controller;
	}

	/**
	 * 获取控制器名称
	 *
	 * @return string
	 */
	public function getControllerName()
	{
		return $this->_controllerName;
	}

	/**
	 * 获取path
	 *
	 * @param string $sep
	 * @return string
	 */
	public function getPath($sep = '/')
	{
		global $_CONFIG;
		return ($_CONFIG['GroupStart'] ? $this->_groupName . $sep : '') . $this->_controllerName . $sep . $this->_actionName;
	}

	/**
	 * 调度
	 *
	 * @return void|boolean
	 */
	public function dispatch()
	{
		global $_CONFIG;
		$found = false;
		$controllerClassName = $this->_validateControllerClassName($this->_groupName, $this->_controllerName);
		if ($controllerClassName) {
			$controllerInstance = new $controllerClassName();
			if ($controllerInstance->hasAction($this->_actionName)) {
				$found = true;
			} else {
				Titan::$log->notice(($_CONFIG['GroupStart'] ? $this->_groupName . '/' : '') . $this->_controllerName . '/' . $this->_actionName . ' 该动作不存在');
			}
		}

		if (!$found) {
			if ($_CONFIG['GroupStart']) $this->_groupName = 'index';
			$this->_controllerName = 'index';
			$this->_actionName = 'noRoute';

			$controllerClassName = $this->_validateControllerClassName($this->_groupName, $this->_controllerName);
			if (!$controllerClassName) {
				return false;
			}

			$controllerInstance = new $controllerClassName();
			if (!$controllerInstance->hasAction($this->_actionName)) {
				return false;
			}
		}
		$this->_controller = $controllerInstance;
		$this->_controller->dispatch($this->_actionName);
	}

	/**
	 * 验证控制器
	 *
	 * @param  string $group
	 * @param  string $controller
	 * @return string|boolean
	 */
	protected function _validateControllerClassName($group, $controller)
	{
		global $_CONFIG;
		$controllerFileName = CONTROLLER_PATH . ($_CONFIG['GroupStart'] ? Titan::ucwords($group, '/') . '/' : '') . Titan::ucwords($controller, '/') . 'Controller.php';
		$controllerClassName = ($_CONFIG['GroupStart'] ? Titan::ucwords($group) . '_' : '') . Titan::ucwords($controller) . 'Controller';

		if (!class_exists($controllerClassName, false)) {
			if (!is_file($controllerFileName)) {
				Titan::$log->notice($controllerFileName . ' 控制器文件不存在');
				return false;
			}

			include($controllerFileName);

			if (!class_exists($controllerClassName, false)) {
				Titan::$log->notice($controllerClassName . ' 控制器不存在');
				return false;
			}
		}

		return $controllerClassName;
	}
}

/************************************************
 * 模型
 */
class TitanModel
{
	/**
	 * 数据库对象
	 *
	 * @var TitanDB
	 */
	protected $_db;

	/**
	 * 表名
	 *
	 * @var string
	 */
	protected $_mainTable;

	/**
	 * 主键字段
	 *
	 * @var string
	 */
	protected $_idFieldName;

	/**
	 * 构造函数
	 *
	 */
	public function __construct()
	{
		$this->_db = Titan::$db;
		$this->_construct();
	}

	/**
	 * 自定义构造函数
	 */
	protected function _construct()
	{}

	/**
	 * 初始化模型
	 *
	 * @param  string      $mainTable
	 * @param  string|null $idFieldName
	 * @return void
	 */
	protected function _init($mainTable, $idFieldName = null)
	{
		$mainTableArr = explode('/', $mainTable);
		$mainTable = str_replace('/', '_', $mainTable);
		$this->_mainTable = $mainTable;

		if (is_null($idFieldName)) {
			$idFieldName = end($mainTableArr) . '_id';
		}
		$this->_idFieldName = $idFieldName;
	}

	/**
	 * 获取所有数据行
	 *
	 * @param  array $option
	 * @return array|false
	 */
	public function getAllList($option = array())
	{
		$this->_parseOption($option);
		$sql = "SELECT {$option['col']} FROM {$this->_mainTable}"
			. (($option['where']) ? " WHERE {$option['where']}" : '')
			. (($option['group']) ? " GROUP BY {$option['group']}" : '')
			. (($option['order']) ? " ORDER BY {$option['order']}" : '');
		return $this->_db->fetchCache($sql, $option['cacheTime'], 'fetchAll');
	}

	/**
	 * 根据过滤获取数据行
	 *
	 * @param  int   $page
	 * @param  int   $rows
	 * @param  array $option
	 * @return array|false
	 */
	public function getList($page, $rows, $option = array())
	{
		$this->_parseOption($option);
		$sql = "SELECT {$option['col']} FROM {$this->_mainTable}"
			. (($option['where']) ? " WHERE {$option['where']}" : '')
			. (($option['group']) ? " GROUP BY {$option['group']}" : '')
			. (($option['order']) ? " ORDER BY {$option['order']}" : '');
		$sql = $this->_db->limitPage($sql, $page, $rows);
		return $this->_db->fetchAll($sql);
	}

	/**
	 * 根据过滤获取数据行(大数量表)
	 *
	 * @param  int   $page
	 * @param  int   $rows
	 * @param  array $option
	 * @return array|false
	 */
	public function getListByLarge($page, $rows, $option = array())
	{
		$this->_parseOption($option);
		$sql = "SELECT {$this->_idFieldName} FROM {$this->_mainTable}"
			. (($option['where']) ? " WHERE {$option['where']}" : '')
			. (($option['group']) ? " GROUP BY {$option['group']}" : '')
			. (($option['order']) ? " ORDER BY {$option['order']}" : '');
		$sql = $this->_db->limitPage($sql, $page, $rows);
		$ids = $this->_db->fetchCol($sql);
		$data = array();
		if (!empty($ids) && is_array($ids)) {
			$where = $this->_db->parseWhere(array($this->_idFieldName => array('in', $ids)));
			$ids   = "'" . implode("','", $ids) . "'";
			$order = "FIELD({$this->_idFieldName},$ids)";
			$sql = "SELECT {$option['col']} FROM {$this->_mainTable}"
				. (($where) ? " WHERE {$where}" : '')
				. (($order) ? " ORDER BY {$order}" : '');
			$data = $this->_db->fetchAll($sql);
		}
		return $data;
	}

	/**
	 * 根据过滤获取所有行数
	 *
	 * @param  array $option
	 * @return int
	 */
	public function getTotalList($option = array())
	{
		$this->_parseOption($option);
		$sql = "SELECT COUNT({$this->_idFieldName}) total FROM {$this->_mainTable}"
			. (($option['where']) ? " WHERE {$option['where']}" : '')
			. (($option['group']) ? " GROUP BY {$option['group']}" : '');
		return $this->_db->fetchOne($sql);
	}

	/**
	 * 获取对应主键结果集
	 *
	 * @param  array $option
	 * @return array|false
	 */
	public function getCol($option = array())
	{
		$this->_parseOption($option);
		if ($option['col'] == '*') $option['col'] = $this->_idFieldName;
		$sql = "SELECT {$option['col']} FROM {$this->_mainTable}"
			. (($option['where']) ? " WHERE {$option['where']}" : '')
			. (($option['group']) ? " GROUP BY {$option['group']}" : '')
			. (($option['order']) ? " ORDER BY {$option['order']}" : '');
		return $this->_db->fetchCache($sql, $option['cacheTime'], 'fetchCol');
	}

	/**
	 * 获取对应结果集的第一列为"键"第二列为"值"的数据
	 *
	 * @param  array $option
	 * @return array|false
	 */
	public function getPairs($option = array())
	{
		$this->_parseOption($option);
		$sql = "SELECT {$option['col']} FROM {$this->_mainTable}"
			. (($option['where']) ? " WHERE {$option['where']}" : '')
			. (($option['group']) ? " GROUP BY {$option['group']}" : '')
			. (($option['order']) ? " ORDER BY {$option['order']}" : '');
		return $this->_db->fetchCache($sql, $option['cacheTime'], 'fetchPairs');
	}

	/**
	 * 获取对应结果集的第一列为"键"其他列为"值"的数据
	 *
	 * @param array $option
	 * @return array
	 */
	public function getPairs2($option = array())
	{
		$this->_parseOption($option);
		$sql = "SELECT {$option['col']} FROM {$this->_mainTable}"
			. (($option['where']) ? " WHERE {$option['where']}" : '')
			. (($option['group']) ? " GROUP BY {$option['group']}" : '')
			. (($option['order']) ? " ORDER BY {$option['order']}" : '');
		$rows  = $this->_db->fetchCache($sql, $option['cacheTime'], 'fetchAll');
		$result = array();
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$result[current($row)] = $row;
				//$result[array_shift($row)] = $row;
			}
		}
		return $result;
	}

	/**
	 * 根据主键字段获取数据行
	 *
	 * @param  int $id
	 * @param  array|string $col
	 * @param  int $cacheTime
	 * @return array|false
	 */
	public function get($id, $col = '*', $cacheTime = 0)
	{
		$option = array(
			'col'       => $col,
			'where'     => array($this->_idFieldName => array('eq', $id)),
			'cacheTime' => $cacheTime
		);
		return $this->getRow($option);
	}

	/**
	 * 根据条件获取一行数据
	 *
	 * @param  array $option
	 * @return array|false
	 */
	public function getRow($option = array())
	{
		$this->_parseOption($option);
		$sql = "SELECT {$option['col']} FROM {$this->_mainTable}"
			. (($option['where']) ? " WHERE {$option['where']}" : '')
			. (($option['group']) ? " GROUP BY {$option['group']}" : '')
			. (($option['order']) ? " ORDER BY {$option['order']}" : '');
		return $this->_db->fetchCache($sql, $option['cacheTime'], 'fetchRow');
	}

	/**
	 * 根据获取主键字段验证ID是否存在该数据
	 *
	 * @param  int $id
	 * @return boolean
	 */
	public function validate($id)
	{
		$sql = "SELECT COUNT({$this->_idFieldName}) total
				FROM   {$this->_mainTable}
				WHERE  {$this->_idFieldName} = :id";
		$bind = array(':id' => $id);
		if ($this->_db->fetchOne($sql, $bind)) {
			return true;
		}
		return false;
	}

	/**
	 * 解析option数组
	 *
	 * @param array $option
	 */
	protected function _parseOption(&$option)
	{
		// 解析字段
		if (isset($option['col'])
			&& !empty($option['col'])) {
			if (is_array($option['col'])
				&& count($option['col']) > 0) {
				$option['col'] = implode(',', $option['col']);
			}
		} else {
			$option['col'] = '*';
		}
		// 解析条件
		if (isset($option['where'])
			&& !empty($option['where'])) {
			$option['where'] = $this->_db->parseWhere($option['where']);
		} else {
			$option['where'] = '';
		}
		// 解析分组
		if (isset($option['group'])
			&& !empty($option['group'])) {
			$option['group'] = $this->_db->parseGroup($option['group']);
		} else {
			$option['group'] = '';
		}
		// 解析排序
		if (isset($option['order'])
			&& !empty($option['order'])) {
			$option['order'] = $this->_db->parseOrder($option['order']);
		} else {
			$option['order'] = '';
		}
		// 解析缓存
		if (isset($option['cacheTime'])
			&& !empty($option['cacheTime'])
			&& is_numeric($option['cacheTime'])) {
			$option['cacheTime'] = $option['cacheTime'] > 100 ? $option['cacheTime'] : 100;
		} else {
			$option['cacheTime'] = 0;
		}
	}

	/**
	 * 新增数据
	 *
	 * @param  array $data
	 * @return int
	 */
	public function add($data)
	{
		if (empty($data) || !is_array($data)) return 0;
		return $this->_db->insert($this->_mainTable, $data);
	}

	/**
	 * 获取最后一次插入数据的ID
	 *
	 * @return int
	 */
	public function lastInsertId()
	{
		return $this->_db->lastInsertId();
	}

	/**
	 * 删除数据
	 *
	 * @param  array|string $ids
	 * @return int
	 */
	public function del($ids)
	{
		$where[$this->_idFieldName] = array('in', $ids);
		return $this->_db->delete($this->_mainTable, $where);
	}

	/**
	 * 修改数据
	 *
	 * @param  array $data
	 * @param  int $id
	 * @return int
	 */
	public function update($data, $id)
	{
		$where[$this->_idFieldName] = array('eq', $id);
		return $this->_db->update($this->_mainTable, $data, $where);
	}

	/**
	 * 根据条件删除数据
	 *
	 * @param  array $where
	 * @return int
	 */
	public function delByWhere($where)
	{
		if (empty($where) || !is_array($where)) return 0;
		return $this->_db->delete($this->_mainTable, $where);
	}

	/**
	 * 根据条件修改数据
	 *
	 * @param  array $data
	 * @param  array $where
	 * @return int
	 */
	public function updateByWhere($data, $where)
	{
		if (empty($where) || !is_array($where)) return 0;
		return $this->_db->update($this->_mainTable, $data, $where);
	}

	/**
	 * 根据字段名和字段值判断是否存在
	 *
	 * @param  string $field
	 * @param  string|int $value
	 * @param  int $id
	 * @return boolean
	 */
	protected function _exist($field, $value, $id = 0)
	{
		$sql = "SELECT COUNT({$this->_idFieldName}) total
				FROM   {$this->_mainTable}
				WHERE  {$field} = :field
				AND	   {$this->_idFieldName} <> :id";
		$bind = array(
			':field' => $value,
			':id' => $id
		);
		if ($this->_db->fetchOne($sql, $bind)) {
			return true;
		}
		return false;
	}

	/**
	 * 根据字段名和字段值获取ID
	 *
	 * @param  string $field
	 * @param  string|int $value
	 * @return int
	 */
	protected function _getID($field, $value)
	{
		$sql = "SELECT {$this->_idFieldName}
				FROM   {$this->_mainTable}
				WHERE  {$field} = :field";
		$bind = array(':field' => $value);
		return $this->_db->fetchOne($sql, $bind);
	}

	/**
	 * 利用__call方法实现一些特殊的Model方法
	 *
	 * @param  string $method
	 * @param  array  $args
	 * @return mixed
	 * @throws Exception
	 */
	public function __call($method, array $args)
	{
		$matches = array();

		if (preg_match('/^exist([a-zA-Z0-9_]*?)$/', $method, $matches)) {
			$field = strtolower($matches[1]);
			array_unshift($args, $field);
			return call_user_func_array(array($this, '_exist'), $args);
		} elseif (preg_match('/^getIDBy([a-zA-Z0-9_]*?)$/', $method, $matches)) {
			$field = strtolower($matches[1]);
			array_unshift($args, $field);
			return call_user_func_array(array($this, '_getID'), $args);
		}

		throw new Exception("$method:您所请求的方法不存在！");
	}
}

/************************************************
 * 视图
 */
class TitanView
{
	/**
	 * 模板数据
	 *
	 * @var array
	 */
	protected $_viewVars = array();

	/**
	 * 构造函数
	 */
	public function __construct()
	{
	}

	/**
	 * 模板变量赋值
	 *
	 * @param $key
	 * @param null $value
	 */
	public function assign($key, $value = null)
	{
		if (is_array($key)) {
			foreach ($key as $k=>$v) {
				$this->assign($k, $v);
			}
		} else {
			$this->_viewVars[$key] = $value;
		}
	}

	/**
	 * 获取URL
	 *
	 * @param  string  $path
	 * @param  boolean $sep
	 * @return string
	 */
	public function getUrl($path = '', $sep = false)
	{
		return Titan::getUrl($path, $sep);
	}

	/**
	 * 获取path
	 *
	 * @param string $sep
	 * @return string
	 */
	public function getPath($sep = '/')
	{
		return Titan::$process->getPath($sep);
	}

	/**
	 * 获取客户端IP
	 *
	 * @return string
	 */
	public function getIp()
	{
		return Titan::getIp();
	}

	/**
	 * 设置语言环境代码
	 *
	 * @param string $locale
	 */
	public function setLocale($locale)
	{
		Titan::$locale->setLocale($locale);
	}

	public function getLocale()
	{
		return Titan::$locale->getLocale();
	}

	public function getLang()
	{
		return substr(Titan::$locale->getLocale(), 0, 2);
	}

	/**
	 * 翻译
	 *
	 * @return string
	 */
	public function __()
	{
		$args = func_get_args();
		return Titan::$locale->translate($args);
	}

	/**
	 * 直接输出字符串
	 *
	 * @param string $content
	 * @return void
	 */
	public function display($content)
	{
		global $_CONFIG;
		if ($_CONFIG['XSS']) {
			Titan::filter($this->_viewVars, 'htmlspecialchars');
			Titan::filter($_GET, 'htmlspecialchars');
			Titan::filter($_POST, 'htmlspecialchars');
			Titan::filter($_COOKIE, 'htmlspecialchars');
		}
		@extract($this->_viewVars, EXTR_SKIP);
		ob_start();
		eval('?>' . $content);
		$content = ob_get_clean();
		echo $content;
	}

	/**
	 * 使用模版输出视图
	 *
	 * @param  string $file
	 * @return void
	 */
	public function render($file = null)
	{
		global $_CONFIG;
		if ($_CONFIG['XSS']) {
			Titan::filter($this->_viewVars, 'htmlspecialchars');
			Titan::filter($_GET, 'htmlspecialchars');
			Titan::filter($_POST, 'htmlspecialchars');
			Titan::filter($_COOKIE, 'htmlspecialchars');
		}
		@extract($this->_viewVars, EXTR_SKIP);
		$file = VIEW_PATH . Titan::ucwords($file, '/') . '.php';
		if (is_file($file)) {
			ob_start();
			include($file);
			$content = ob_get_clean();
			echo $content;
		} else {
			Titan::$log->notice($file . ' 模板文件不存在');
		}
	}

	/**
	 * 获取当前控制器对象
	 *
	 * @return TitanController
	 */
	public function getController()
	{
		return Titan::$process->getController();
	}

	/**
	 * 获取当前控制器名称
	 */
	public function getControllerName()
	{
		return Titan::$process->getControllerName();
	}
}

/************************************************
 * 控制器
 */
class TitanController
{
	/**
	 * 数据库对象
	 *
	 * @var TitanDB
	 */
	protected $_db;

	/**
	 * 模板对象
	 *
	 * @var TitanView
	 */
	protected $_view;

	/**
	 * 构造函数
	 *
	 */
	public function __construct()
	{
		global $_CONFIG;
		$this->_db   =  Titan::$db;
		$this->_view = new TitanView();
		$this->_construct();
	}

	/**
	 * 自定义构造函数
	 *
	 * @return void
	 */
	protected function _construct()
	{}

	/**
	 * 判断动作方法是否存在
	 *
	 * @param  string $action
	 * @return boolean
	 */
	public function hasAction($action)
	{
		return is_callable(array($this, $this->getActionMethodName($action)));
	}

	/**
	 * 格式化动作方法名称
	 *
	 * @param  string $action
	 * @return string
	 */
	public function getActionMethodName($action)
	{
		$method = $action . 'Action';
		return $method;
	}

	/**
	 * 开始动作
	 *
	 * @param  string $action
	 * @return void
	 */
	public function dispatch($action)
	{
		$actionMethodName = $this->getActionMethodName($action);

		if (!is_callable(array($this, $actionMethodName))) {
			$actionMethodName = 'noRouteAction';
		}
		$this->beforeDispatch();
		$this->$actionMethodName();
		$this->afterDispatch();
	}

	/**
	 * 开始动作前
	 *
	 * @return void
	 */
	public function beforeDispatch()
	{}

	/**
	 * 开始动作后
	 *
	 * @return void
	 */
	public function afterDispatch()
	{}

	/**
	 * 页面不存在
	 *
	 * @return void
	 */
	public function noRouteAction()
	{
		header('HTTP/1.1 404 Not Found');
		$this->_view->display('<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-Edge,chrome">
    <title>很抱歉，此页面暂时找不到！</title>
    <script type="text/javascript">if(top.location!==self.location){top.location=self.location;}</script>
    <style type="text/css">
        body {margin: 0px; padding:0px; font-family:"微软雅黑", Arial, "Trebuchet MS", Verdana, Georgia,Baskerville,Palatino,Times; font-size:16px;}
        div{margin-left:auto; margin-right:auto;}
        a {text-decoration: none; color: #1064A0;}
        a:hover {color: #0078D2;}
        h1,h2 {color:#0188DE; font-weight: normal; margin: 0;}
        #page{width:910px; padding:20px 20px 40px 20px; margin-top:80px;}
        .button, .button a {width:180px; height:28px;}
        .button a:hover{ background:#5BBFFF;}
    </style>
</head>
<body>
    <div id="page" style="border-style:dashed;border-color:#e4e4e4;line-height:30px; min-width: 560px;">
        <h1 style="font-size:44px; padding:20px 0px 10px 0px;">抱歉，找不到此页面~</h1>
        <h2 style="font-size:16px; padding:10px 0px 40px 0px;">Sorry, the site now can not be accessed. </h2>
        <p style="color:#666666; margin: 0;">你请求访问的页面，暂时找不到，我们建议你返回首页进行浏览，谢谢！</p><br /><br />
        <div class="button" style="margin-left:0px; margin-top:10px; background:#009CFF; border-bottom:4px solid #0188DE; text-align:center;">
            <a href="' . $this->getUrl() . '" style="display:block; font-size:14px; color:#fff; ">返回首页</a>
        </div>
    </div>
</body>
</html>');
	}

	/**
	 * 获取URL
	 *
	 * @param  string  $path
	 * @param  boolean $sep
	 * @return string
	 */
	public function getUrl($path = '', $sep = false)
	{
		return Titan::getUrl($path, $sep);
	}

	/**
	 * 获取path
	 *
	 * @param string $sep
	 * @return string
	 */
	public function getPath($sep = '/')
	{
		return Titan::$process->getPath($sep);
	}

	/**
	 * 获取客户端IP
	 *
	 * @return string
	 */
	public function getIp()
	{
		return Titan::getIp();
	}

	/**
	 * 翻译
	 *
	 * @return string
	 */
	public function __()
	{
		$args = func_get_args();
		return Titan::$locale->translate($args);
	}

	/**
	 * 加载模型文件
	 *
	 * @param string $file
	 * @param string $className
	 * @return TitanModel
	 */
	protected function _loadModel($file, $className = null)
	{
		$className = (($className == null) ? Titan::ucwords($file) : $className) . 'Model';
		$file = MODEL_PATH . Titan::ucwords($file, '/') . 'Model.php';
		$modelInstance = Titan::registry($file);
		if ($modelInstance) return $modelInstance;
		if (is_file($file)) {
			include_once($file);
			if (!class_exists($className)) Titan::$log->notice($className . ' 模型对象不存在');
			$modelInstance = new $className();
			Titan::register($file, $modelInstance);
			return $modelInstance;
		}

		Titan::$log->notice($file . ' 模型文件不存在');
	}

	/**
	 * 加载自定义类文件
	 *
	 * @param string $file
	 * @param string $className
	 * @param boolean $new
	 * @return object|true
	 */
	protected function _loadClass($file, $className = null, $new = true)
	{
		$className = ($className == null) ? str_replace('/', '_', $file) : $className;
		$file = CLASS_PATH . (!strstr($file, '.php') ? $file . '.php' : $file);
		if (true === $new) {
			$classInstance = Titan::registry($file);
			if ($classInstance) return $classInstance;
		}
		if (is_file($file)) {
			include_once($file);
			if (!class_exists($className)) Titan::$log->notice($className . ' 类对象不存在');
			if (false === $new) return true;
			$classInstance = new $className();
			Titan::register($file, $classInstance);
			return $classInstance;
		}

		Titan::$log->notice($file . ' 类文件不存在');
	}

	/**
	 * 网址跳转
	 *
	 * @param string $url
	 * @param int    $code
	 * @return void
	 */
	protected function _redirectUrl($url, $code = 302)
	{
		header('Status: ' . $code);
		header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url));
		exit();
	}

	/**
	 * AJAX返回
	 *
	 * @param array|string  $data
	 * @param string $type
	 * @return void
	 */
	protected function _ajaxReturn($data, $type = 'JSON')
	{
		global $_CONFIG;
		if ($_CONFIG['XSS']) {
			Titan::filter($data, 'htmlspecialchars');
		}
		switch (strtoupper($type)) {
			case 'XML':
				header('Content-Type:text/xml; charset=utf-8');
				exit($this->_xmlEncode($data));
			case 'JSON':
			default:
				//header('Content-Type:application/json; charset=utf-8');
				header('Content-Type:text/html; charset=utf-8');
				exit(json_encode($data));
		}
	}

	/**
	 * XML编码
	 *
	 * @param array|string $data
	 * @param string $root
	 * @param string $item
	 * @param string $attr
	 * @param string $id
	 * @param string $encoding
	 * @return string
	 */
	protected function _xmlEncode($data, $root = 'titan', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8')
	{
		if (is_array($attr)) {
			$_attr = array();
			foreach ($attr as $key => $value) {
				$_attr[] = "{$key}=\"{$value}\"";
			}
			$attr = implode(' ', $_attr);
		}
		$attr = trim($attr);
		$attr = empty($attr) ? '' : " {$attr}";
		$xml  = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
		$xml .= "<{$root}{$attr}>";
		$xml .= $this->_dataToXml($data, $item, $id);
		$xml .= "</{$root}>";
		return $xml;
	}

	/**
	 * 数据XML编码
	 * @param mixed  $data 数据
	 * @param string $item 数字索引时的节点名称
	 * @param string $id   数字索引key转换为的属性名
	 * @return string
	 */
	protected function _dataToXml($data, $item = 'item', $id = 'id')
	{
		$xml = $attr = '';
		foreach ($data as $key => $val) {
			if (is_numeric($key)) {
				$id && $attr = " {$id}=\"{$key}\"";
				$key  = $item;
			}
			$xml .=  "<{$key}{$attr}>";
			$xml .=  (is_array($val) || is_object($val)) ? $this->_dataToXml($val, $item, $id) : $val;
			$xml .=  "</{$key}>";
		}
		return $xml;
	}
}

/************************************************
 * 日志
 */
class TitanLog
{
	// 日志级别 从上到下，由低到高
	const SQL    = 1; // SQL：SQL语句 注意只在调试模式开启时有效
	const DEBUG  = 2; // 调试: 调试信息
	const INFO   = 3; // 信息: 程序输出信息
	const NOTICE = 4; // 通知: 程序可以运行但是还不够完美的错误
	const WARN   = 5; // 警告性错误: 需要发出警告的错误
	const ERR    = 6; // 一般错误: 一般性错误
	const CRIT   = 7; // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
	const ALERT  = 8; // 警戒性错误: 必须被立即修改的错误
	const EMERG  = 9; // 严重错误: 导致系统崩溃无法使用

	private $_tags = array(
		1 => 'SQL',
		2 => 'DEBUG',
		3 => 'INFO',
		4 => 'NOTICE',
		5 => 'WARN',
		6 => 'ERR',
		7 => 'CRIT',
		8 => 'ALERT',
		9 => 'EMERG'
	);

	// 默认LogLevel
	private $_defaultLevel;

	// 日志文件
	private $_logFile;

	public function __construct($logName = '', $defaultLevel = self::INFO)
	{
		$logName = trim($logName);

		if (empty($logName)) {
			$logName = 'Log';
		} else {
			$logName = str_replace('_', '/', $logName);
		}

		$logFile = VAR_PATH . 'log/' . date('Ymd-') . $logName . '.log';
		$this->_logFile = $logFile;

		Titan::mkdir(VAR_PATH . 'log/');

		$defaultLevel = (int) $defaultLevel;
		if ($defaultLevel > 9 || $defaultLevel < 1) {
			$defaultLevel = self::INFO;
		}

		$this->_defaultLevel = $defaultLevel;
	}

	/**
	 * 记录Log信息及其日志信息
	 *
	 * @param string $message
	 */
	public function log($message)
	{
		error_log($message, 3, $this->_logFile);
	}

	/**
	 * 记录Log信息及其日志信息
	 *
	 * @param string $message
	 * @param int $level
	 */
	public function write($message, $level)
	{
		$clientIp = Titan::getIp();
		$levelTag = $this->_tags[$level];

		if (!is_string($message)) {
			$message = "\r\n" . print_r($message, true);
		}

		if ($level >= $this->_defaultLevel) {
			$this->log("[". date('Y-m-d H:i:s') ."] [{$clientIp}] [{$levelTag}] : {$message}\r\n");
		}
	}

	/**
	 * 判断想要记得log级别是否高于默认log级别。
	 * 这是为了避免默认log级别设置的太高，白准备log数据了，毕竟组装要记录的log也是需要时间的嘛
	 *
	 * @param integer $level 想要记录的log级别
	 * @return bool
	 */
	public function isEnableLogLevel($level)
	{
		return $level >= $this->_defaultLevel;
	}

	/**
	 * 最高级别的错误
	 *
	 * @param string $message
	 */
	public function emerg($message)
	{
		$this->write($message, self::EMERG);
	}

	/**
	 * 记录错误级别的log
	 *
	 * @param string $message
	 */
	public function error($message)
	{
		$this->write($message, self::ERR);
	}

	/**
	 * 记录警告信息
	 *
	 * @param string $message
	 */
	public function warn($message)
	{
		$this->write($message, self::WARN);
	}

	/**
	 * 记录notice级别的log
	 *
	 * @param string $message
	 */
	public function notice($message)
	{
		$this->write($message, self::NOTICE);
	}

	/**
	 * 记录普通信息
	 *
	 * @param string $message
	 */
	public function info($message)
	{
		$this->write($message, self::INFO);
	}

	/**
	 * 记录调试信息，只在开发、测试环境开启，禁止在正式环境输出Debug级别的log
	 *
	 * @param string $message
	 */
	public function debug($message)
	{
		$this->write($message, self::DEBUG);
	}
}

/************************************************
 * 语言环境
 */
class TitanLocale
{
	/**
	 * 语言环境集合
	 *
	 * @var array $_localeData
	 */
	protected $_localeData = array(
		'aa_DJ' , 'aa_ER' , 'aa_ET' , 'aa'    , 'af_NA' , 'af_ZA' , 'af'    , 'ak_GH' , 'ak'    , 'am_ET' ,
		'am'    , 'ar_AE' , 'ar_BH' , 'ar_DZ' , 'ar_EG' , 'ar_IQ' , 'ar_JO' , 'ar_KW' , 'ar_LB' , 'ar_LY' ,
		'ar_MA' , 'ar_OM' , 'ar_QA' , 'ar_SA' , 'ar_SD' , 'ar_SY' , 'ar_TN' , 'ar_YE' , 'ar'    , 'as_IN' ,
		'as'    , 'az_AZ' , 'az'    , 'be_BY' , 'be'    , 'bg_BG' , 'bg'    , 'bn_BD' , 'bn_IN' , 'bn'    ,
		'bo_CN' , 'bo_IN' , 'bo'    , 'bs_BA' , 'bs'    , 'byn_ER', 'byn'   , 'ca_ES' , 'ca'    , 'cch_NG',
		'cch'   , 'cop'   , 'cs_CZ' , 'cs'    , 'cy_GB' , 'cy'    , 'da_DK' , 'da'    , 'de_AT' , 'de_BE' ,
		'de_CH' , 'de_DE' , 'de_LI' , 'de_LU' , 'de'    , 'dv_MV' , 'dv'    , 'dz_BT' , 'dz'    , 'ee_GH' ,
		'ee_TG' , 'ee'    , 'el_CY' , 'el_GR' , 'el'    , 'en_AS' , 'en_AU' , 'en_BE' , 'en_BW' , 'en_BZ' ,
		'en_CA' , 'en_GB' , 'en_GU' , 'en_HK' , 'en_IE' , 'en_IN' , 'en_JM' , 'en_MH' , 'en_MP' , 'en_MT' ,
		'en_NA' , 'en_NZ' , 'en_PH' , 'en_PK' , 'en_SG' , 'en_TT' , 'en_UM' , 'en_US' , 'en_VI' , 'en_ZA' ,
		'en_ZW' , 'en'    , 'eo'    , 'es_AR' , 'es_BO' , 'es_CL' , 'es_CO' , 'es_CR' , 'es_DO' , 'es_EC' ,
		'es_ES' , 'es_GT' , 'es_HN' , 'es_MX' , 'es_NI' , 'es_PA' , 'es_PE' , 'es_PR' , 'es_PY' , 'es_SV' ,
		'es_US' , 'es_UY' , 'es_VE' , 'es'    , 'et_EE' , 'et'    , 'eu_ES' , 'eu'    , 'fa_AF' , 'fa_IR' ,
		'fa'    , 'fi_FI' , 'fi'    , 'fil_PH', 'fil'   , 'fo_FO' , 'fo'    , 'fr_BE' , 'fr_CA' , 'fr_CH' ,
		'fr_FR' , 'fr_LU' , 'fr_MC' , 'fr_SN' , 'fr'    , 'fur_IT', 'fur'   , 'ga_IE' , 'ga'    , 'gaa_GH',
		'gaa'   , 'gez_ER', 'gez_ET', 'gez'   , 'gl_ES' , 'gl'    , 'gsw_CH', 'gsw'   , 'gu_IN' , 'gu'    ,
		'gv_GB' , 'gv'    , 'ha_GH' , 'ha_NE' , 'ha_NG' , 'ha_SD' , 'ha'    , 'haw_US', 'haw'   , 'he_IL' ,
		'he'    , 'hi_IN' , 'hi'    , 'hr_HR' , 'hr'    , 'hu_HU' , 'hu'    , 'hy_AM' , 'hy'    , 'ia'    ,
		'id_ID' , 'id'    , 'ig_NG' , 'ig'    , 'ii_CN' , 'ii'    , 'in'    , 'is_IS' , 'is'    , 'it_CH' ,
		'it_IT' , 'it'    , 'iu'    , 'iw'    , 'ja_JP' , 'ja'    , 'ka_GE' , 'ka'    , 'kaj_NG', 'kaj'   ,
		'kam_KE', 'kam'   , 'kcg_NG', 'kcg'   , 'kfo_CI', 'kfo'   , 'kk_KZ' , 'kk'    , 'kl_GL' , 'kl'    ,
		'km_KH' , 'km'    , 'kn_IN' , 'kn'    , 'ko_KR' , 'ko'    , 'kok_IN', 'kok'   , 'kpe_GN', 'kpe_LR',
		'kpe'   , 'ku_IQ' , 'ku_IR' , 'ku_SY' , 'ku_TR' , 'ku'    , 'kw_GB' , 'kw'    , 'ky_KG' , 'ky'    ,
		'ln_CD' , 'ln_CG' , 'ln'    , 'lo_LA' , 'lo'    , 'lt_LT' , 'lt'    , 'lv_LV' , 'lv'    , 'mk_MK' ,
		'mk'    , 'ml_IN' , 'ml'    , 'mn_CN' , 'mn_MN' , 'mn'    , 'mo'    , 'mr_IN' , 'mr'    , 'ms_BN' ,
		'ms_MY' , 'ms'    , 'mt_MT' , 'mt'    , 'my_MM' , 'my'    , 'nb_NO' , 'nb'    , 'nds_DE', 'nds'   ,
		'ne_IN' , 'ne_NP' , 'ne'    , 'nl_BE' , 'nl_NL' , 'nl'    , 'nn_NO' , 'nn'    , 'no'    , 'nr_ZA' ,
		'nr'    , 'nso_ZA', 'nso'   , 'ny_MW' , 'ny'    , 'oc_FR' , 'oc'    , 'om_ET' , 'om_KE' , 'om'    ,
		'or_IN' , 'or'    , 'pa_IN' , 'pa_PK' , 'pa'    , 'pl_PL' , 'pl'    , 'ps_AF' , 'ps'    , 'pt_BR' ,
		'pt_PT' , 'pt'    , 'ro_MD' , 'ro_RO' , 'ro'    , 'ru_RU' , 'ru_UA' , 'ru'    , 'rw_RW' , 'rw'    ,
		'sa_IN' , 'sa'    , 'se_FI' , 'se_NO' , 'se'    , 'sh_BA' , 'sh_CS' , 'sh_YU' , 'sh'    , 'si_LK' ,
		'si'    , 'sid_ET', 'sid'   , 'sk_SK' , 'sk'    , 'sl_SI' , 'sl'    , 'so_DJ' , 'so_ET' , 'so_KE' ,
		'so_SO' , 'so'    , 'sq_AL' , 'sq'    , 'sr_BA' , 'sr_CS' , 'sr_ME' , 'sr_RS' , 'sr_YU' , 'sr'    ,
		'ss_SZ' , 'ss_ZA' , 'ss'    , 'st_LS' , 'st_ZA' , 'st'    , 'sv_FI' , 'sv_SE' , 'sv'    , 'sw_KE' ,
		'sw_TZ' , 'sw'    , 'syr_SY', 'syr'   , 'ta_IN' , 'ta'    , 'te_IN' , 'te'    , 'tg_TJ' , 'tg'    ,
		'th_TH' , 'th'    , 'ti_ER' , 'ti_ET' , 'ti'    , 'tig_ER', 'tig'   , 'tl'    , 'tn_ZA' , 'tn'    ,
		'to_TO' , 'to'    , 'tr_TR' , 'tr'    , 'trv_TW', 'trv'   , 'ts_ZA' , 'ts'    , 'tt_RU' , 'tt'    ,
		'ug_CN' , 'ug'    , 'uk_UA' , 'uk'    , 'ur_IN' , 'ur_PK' , 'ur'    , 'uz_AF' , 'uz_UZ' , 'uz'    ,
		've_ZA' , 've'    , 'vi_VN' , 'vi'    , 'wal_ET', 'wal'   , 'wo_SN' , 'wo'    , 'xh_ZA' , 'xh'    ,
		'yo_NG' , 'yo'    , 'zh_CN' , 'zh_HK' , 'zh_MO' , 'zh_SG' , 'zh_TW' , 'zh'    , 'zu_ZA' , 'zu'
	);

	/**
	 * 语言环境
	 *
	 * @var string
	 */
	protected $_locale = 'zh_CN';

	/**
	 * 翻译数据
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		$this->_initTranslante();
	}

	/**
	 * 设置语言环境代码
	 *
	 * @param string $locale
	 * @return void
	 */
	public function setLocale($locale)
	{
		if (in_array($locale, $this->_localeData)) {
			$this->_locale = $locale;
			$this->_initTranslante();
		}
	}

	/**
	 * 获取语言环境代码
	 *
	 * @return string
	 */
	public function getLocale()
	{
		return $this->_locale;
	}

	/**
	 * 获取语言代码
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		$locale = explode('_', $this->_locale);
		return $locale[0];
	}

	/**
	 * 翻译
	 *
	 * @param array $args
	 * @return string
	 */
	public function translate($args)
	{
		$text = array_shift($args);
		if (is_string($text) && ''==$text
			|| is_null($text)
			|| is_bool($text) && false===$text
			|| is_object($text)) {
			return '';
		}
		if (array_key_exists($text, $this->_data)) {
			$translated = $this->_data[$text];
		} else {
			$translated = $text;
		}
		$result = @vsprintf($translated, $args);
		if ($result === false) {
			$result = $translated;
		}

		return $result;
	}

	/**
	 * 初始化翻译数据
	 *
	 * @return void
	 */
	protected function _initTranslante()
	{
		$file = VAR_PATH . 'locale/' . $this->getLanguage() . '.csv';
		if (file_exists($file)) {
			$fh = fopen($file, 'r');
			while ($rowData = fgetcsv($fh, 0, ',', '"')) {
				if (isset($rowData[0])) {
					$this->_data[$rowData[0]] = isset($rowData[1]) ? $rowData[1] : null;
				}
			}
			fclose($fh);
		}
	}
}

/************************************************
 * 数据库对象
 */
class TitanDB
{
	/**
	 * 配置
	 *
	 * @var array
	 */
	protected $_config = array();

	/**
	 * 数据库连接
	 *
	 * @var object|resource|null
	 */
	protected $_connection = null;

	/**
	 * 获取模式集合
	 *
	 * @var array
	 */
	protected $_fetchModes = array(
		PDO::FETCH_LAZY  => PDO::FETCH_LAZY,
		PDO::FETCH_ASSOC => PDO::FETCH_ASSOC,
		PDO::FETCH_NUM   => PDO::FETCH_NUM,
		PDO::FETCH_BOTH  => PDO::FETCH_BOTH,
		PDO::FETCH_NAMED => PDO::FETCH_NAMED,
		PDO::FETCH_OBJ   => PDO::FETCH_OBJ
	);

	/**
	 * 默认获取模式
	 *
	 * @var int
	 */
	protected $_fetchMode = PDO::FETCH_ASSOC;

	/**
	 * 表达式
	 *
	 * @var array
	 */
	protected $_comparison = array('eq' => '=', 'neq' => '<>', 'gt'	=> '>', 'egt' => '>=', 'lt' => '<', 'elt' => '<=', 'notlike' => 'NOT LIKE', 'like' => 'LIKE', 'in' => 'IN', 'notin' => 'NOT IN');

	/**
	 * 统计查询次数
	 * @var integer
	 */
	public static $countSelect = 0;

	/**
	 * 构造函数
	 *
	 * @param array $config
	 * @throws Exception
	 */
	public function __construct($config)
	{
		if (!extension_loaded('pdo') || !in_array('mysql', PDO::getAvailableDrivers())) {
			throw new Exception('需要加载PDO适配器扩展');
		}

		if (!is_array($config)) {
			throw new Exception('数据库配置文件格式不对');
		}

		$this->_config = $config;
		$this->_config['driver_options'][PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8'";

		if (true === $this->_config['persistent']) {
			$this->_config['driver_options'][PDO::ATTR_PERSISTENT] = true;
		}
	}

	/**
	 * 创建PDO的DSN
	 *
	 * @return string
	 */
	protected function _dsn()
	{
		$dsn = $this->_config;

		unset($dsn['username']);
		unset($dsn['password']);
		unset($dsn['options']);
		unset($dsn['charset']);
		unset($dsn['persistent']);
		unset($dsn['driver_options']);

		foreach ($dsn as $key => $val) {
			$dsn[$key] = "$key=$val";
		}
		$dsn = 'mysql:' . implode(';', $dsn);
		return $dsn;
	}

	/**
	 * 创建PDO对象并连接到数据库
	 *
	 * @throws Exception
	 */
	protected function _connect()
	{
		if ($this->_connection) {
			return;
		}

		$dsn = $this->_dsn();

		try {
			$this->_connection = new PDO(
				$dsn,
				$this->_config['username'],
				$this->_config['password'],
				$this->_config['driver_options']
			);

			$this->_connection->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
			$this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		} catch (PDOException $e) {
			throw new Exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取数据库连接对象
	 *
	 * @return object|resource|null
	 */
	public function getConnection()
	{
		$this->_connect();
		return $this->_connection;
	}

	/**
	 * 关闭数据库连接对象
	 *
	 * @return void
	 */
	public function closeConnection()
	{
		$this->_connection = null;
	}

	/**
	 * 执行SQL语句绑定数据
	 *
	 * @param string $sql
	 * @param array  $bind
	 * @return PDOStatement
	 */
	public function query($sql, $bind = array())
	{
		if (!is_array($bind)) {
			$bind = array($bind);
		}

		if (is_array($bind)) {
			foreach ($bind as $name => $value) {
				if (!is_int($name) && !preg_match('/^:/', $name)) {
					$newName = ":$name";
					unset($bind[$name]);
					$bind[$newName] = $value;
				}
			}
		}

		$stmt = $this->getConnection()->prepare($sql);
		$stmt->execute($bind);

		$stmt->setFetchMode($this->_fetchMode);
		return $stmt;
	}

	/**
	 * 开始事务
	 *
	 * @return void
	 */
	public function beginTransaction()
	{
		$this->getConnection()->beginTransaction();
	}

	/**
	 * 提交事务
	 *
	 * @return void
	 */
	public function commit()
	{
		$this->getConnection()->commit();
	}

	/**
	 * 回滚事务
	 *
	 * @return void
	 */
	public function rollBack()
	{
		$this->getConnection()->rollBack();
	}

	/**
	 * 插入数据
	 *
	 * @param string $table
	 * @param array  $bind
	 * @return int
	 */
	public function insert($table, array $bind)
	{
		$cols = array();
		$vals = array();
		$i = 0;
		foreach ($bind as $col => $val) {
			$cols[] = $col;
			unset($bind[$col]);
			$bind[':col'.$i] = $val;
			$vals[] = ':col'.$i;
			$i++;
		}

		$sql = "INSERT INTO "
			. $table
			. ' (' . implode(', ', $cols) . ') '
			. 'VALUES (' . implode(', ', $vals) . ')';

		$stmt = $this->query($sql, $bind);
		$result = $stmt->rowCount();
		return $result;
	}

	/**
	 * 更新数据
	 *
	 * @param string $table
	 * @param array  $bind
	 * @param string $where
	 * @return int
	 */
	public function update($table, array $bind, $where = '')
	{
		$set = array();
		$i = 0;
		foreach ($bind as $col => $val) {
			unset($bind[$col]);
			$bind[':col'.$i] = $val;
			$val = ':col'.$i;
			$i++;
			$set[] = $col . ' = ' . $val;
		}

		$where = $this->parseWhere($where);

		$sql = "UPDATE "
			. $table
			. ' SET ' . implode(', ', $set)
			. (($where) ? " WHERE $where" : '');
		$stmt = $this->query($sql, $bind);
		$result = $stmt->rowCount();
		return $result;
	}

	/**
	 * 删除数据
	 *
	 * @param string $table
	 * @param string $where
	 * @return int
	 */
	public function delete($table, $where = '')
	{
		$where = $this->parseWhere($where);

		$sql = "DELETE FROM "
			. $table
			. (($where) ? " WHERE $where" : '');

		$stmt = $this->query($sql);
		$result = $stmt->rowCount();
		return $result;
	}

	/**
	 * 解析条件表达式
	 *
	 * @param array $where
	 * @return string
	 */
	public function parseWhere($where)
	{
		$whereStr = '';
		$operate = ' AND ';
		if (is_string($where)) {
			$whereStr = $where;
		} elseif (is_array($where)) {
			foreach ($where as $key => $val) {
				$whereStr .= '(';
				if (strpos($key, '|')) {
					$array = explode('|', $key);
					$str   = array();
					foreach ($array as $m => $k){
						$v = $val[$m];
						$str[] = '(' . $this->_parseWhereItem($k, $v) . ')';
					}
					$whereStr .= implode(' OR ', $str);
				} elseif (strpos($key, '&')) {
					$array = explode('&', $key);
					$str   = array();
					foreach ($array as $m => $k){
						$v = $val[$m];
						$str[] = '(' . $this->_parseWhereItem($k, $v) . ')';
					}
					$whereStr .= implode(' AND ', $str);
				} else {
					$whereStr .= $this->_parseWhereItem($key, $val);
				}
				$whereStr .= ')' . $operate;
			}
			$whereStr = substr($whereStr, 0, -strlen($operate));
		}
		return $whereStr;
	}

	protected function _parseWhereItem($key, $val)
	{
		$whereStr = '';
		if (is_array($val)) {
			if (preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT)$/i', $val[0])) {
				$whereStr .= $key . ' ' . $this->_comparison[strtolower($val[0])] . ' ' . $this->_quote($val[1]);
			} elseif (preg_match('/^(NOTLIKE|LIKE)$/i', $val[0])) {
				if (isset($val[2]) && 'left'==$val[2]) {
					$val[1] = '%' . $val[1];
				} elseif (isset($val[2]) && 'right'==$val[2]) {
					$val[1] = $val[1] . '%';
				} else {
					$val[1] = '%' . $val[1] . '%';
				}
				$whereStr .= $key . ' ' . $this->_comparison[strtolower($val[0])] . ' ' . $this->_quote($val[1]);
			} elseif ('exp' == strtolower($val[0])) {
				$whereStr .= ' ('. $key . ' ' . $val[1] . ') ';
			} elseif (preg_match('/IN/i', $val[0])) {
				if (isset($val[2]) && 'exp'==$val[2]) {
					$whereStr .= $key . ' ' . $this->_comparison[strtolower($val[0])] . ' ' . $val[1];
				} else {
					if (is_string($val[1])) {
						$val[1] = explode(',', $val[1]);
					}
					$whereStr .= $key . ' ' . $this->_comparison[strtolower($val[0])] . ' ('. $this->_quote($val[1]) . ')';
				}
			}
		}
		return $whereStr;
	}

	/**
	 * 解析分组表达式
	 *
	 * @param array|string $group
	 * @return string
	 */
	public function parseGroup($group)
	{
		$groupStr = '';
		if (is_string($group)) {
			$groupStr = $group;
		} elseif (is_array($group)) {
			$groupStr = implode(',', $group);
		}
		return $groupStr;
	}

	/**
	 * 解析排序表达式
	 *
	 * @param array $order
	 * @return string
	 */
	public function parseOrder($order)
	{
		$orderStr = '';
		if (is_array($order)) {
			$array = array();
			foreach ($order as $key => $val) {
				if (is_numeric($key)) {
					$array[] = $val;
				} else {
					$array[] = $key . ' ' . $val;
				}
			}
			$orderStr = implode(',', $array);
		}
		return $orderStr;
	}

	/**
	 * 根据sql语句在缓存或者数据库中获取数据
	 *
	 * @param string $sql
	 * @param int    $cacheTime
	 * @param string $fetchType
	 * @return array|bool
	 */
	public function fetchCache($sql, $cacheTime, $fetchType = 'fetchAll')
	{
		// 使用不存在的方法默认使用fetchAll
		if (!is_callable(array($this, $fetchType))) {
			$fetchType = 'fetchAll';
		}

		if ($cacheTime > 0
			&& Titan::$cache->isExists($fetchType . $sql, $cacheTime)) {
			// 开启缓存,存在有效缓存时使用缓存数据
			$data = Titan::$cache->get($fetchType . $sql);
			return $data;
		} elseif ($cacheTime > 0) {
			// 开启缓存,不存在有效缓存时使用数据库数据并缓存它
			$data = $this->$fetchType($sql);
			Titan::$cache->del($fetchType . $sql);
			// 有效数据写入缓存
			if (!empty($data)) Titan::$cache->set($fetchType . $sql, $data);
			return $data;
		}
		// 未开启缓存直接返回数据库数据
		return $this->$fetchType($sql);
	}

	/**
	 * 获取对应结果集的所有结果。
	 *
	 * @param string $sql
	 * @param array  $bind
	 * @param int    $fetchMode
	 * @return array|false
	 */
	public function fetchAll($sql, $bind = array(), $fetchMode = null)
	{
		if (null === $fetchMode) {
			$fetchMode = $this->_fetchMode;
		}
		$stmt = $this->query($sql, $bind);
		$result = $stmt->fetchAll($fetchMode);
		self::$countSelect++;
		return $result;
	}

	/**
	 * 获取对应结果集的所有结果。
	 * 每一行作为一个由列名索引的数组
	 *
	 * @param string $sql
	 * @param array  $bind
	 * @return array|false
	 */
	public function fetchAssoc($sql, $bind = array())
	{
		$result = $this->fetchAll($sql, $bind, PDO::FETCH_ASSOC);
		self::$countSelect++;
		return $result;
	}

	/**
	 * 获取对应结果集的第一行。
	 *
	 * @param string $sql
	 * @param array  $bind
	 * @param int    $fetchMode
	 * @return array|false
	 */
	public function fetchRow($sql, $bind = array(), $fetchMode = null)
	{
		if (null === $fetchMode) {
			$fetchMode = $this->_fetchMode;
		}
		$stmt = $this->query($sql, $bind);
		$result = $stmt->fetch($fetchMode);
		self::$countSelect++;
		return $result;
	}

	/**
	 * 获取对应结果集的第一列
	 *
	 * @param string $sql
	 * @param array  $bind
	 * @return array|false
	 */
	public function fetchCol($sql, $bind = array())
	{
		$stmt = $this->query($sql, $bind);
		$result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		self::$countSelect++;
		return $result;
	}

	/**
	 * 获取对应结果集的第一列为"键"第二列为"值"的数据
	 *
	 * @param string $sql
	 * @param array  $bind
	 * @return array|false
	 */
	public function fetchPairs($sql, $bind = array())
	{
		$stmt = $this->query($sql, $bind);
		$data = array();
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$data[$row[0]] = $row[1];
		}
		self::$countSelect++;
		return $data;
	}

	/**
	 * 获取对应结果集的第一行第一列的数据
	 *
	 * @param string $sql
	 * @param array  $bind
	 * @return string|false
	 */
	public function fetchOne($sql, $bind = array())
	{
		$stmt = $this->query($sql, $bind);
		$result = $stmt->fetchColumn(0);
		self::$countSelect++;
		return $result;
	}

	/**
	 * 返回安全的字符串
	 *
	 * @param string $value
	 * @return string
	 */
	protected function _quote($value)
	{
		if (is_array($value)) {
			foreach ($value as &$val) {
				$val = $this->_quote($val);
			}
			return implode(', ', $value);
		} elseif (is_int($value)) {
			return $value;
		} elseif (is_float($value)) {
			return sprintf('%F', $value);
		}
		return "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
	}

	/**
	 * 获取数据库中的表集合
	 *
	 * @return array
	 */
	public function listTables()
	{
		return $this->fetchCol('SHOW TABLES');
	}

	/**
	 * 获取数据表里的字段结构
	 *
	 * @param string $tableName
	 * @return array
	 */
	public function describeTable($tableName)
	{
		$stmt   = $this->query("DESCRIBE $tableName");
		$result = $stmt->fetchAll(PDO::FETCH_NUM);

		$field   = 0;
		$type    = 1;
		$null    = 2;
		$key     = 3;
		$default = 4;
		$extra   = 5;

		$desc = array();
		$i = 1;
		$p = 1;
		foreach ($result as $row) {
			list($length, $scale, $precision, $unsigned, $primary, $primaryPosition, $identity)
				= array(null, null, null, null, false, null, false);
			if (preg_match('/unsigned/', $row[$type])) {
				$unsigned = true;
			}
			if (preg_match('/^((?:var)?char)\((\d+)\)/', $row[$type], $matches)) {
				$row[$type] = $matches[1];
				$length = $matches[2];
			} else if (preg_match('/^decimal\((\d+),(\d+)\)/', $row[$type], $matches)) {
				$row[$type] = 'decimal';
				$precision = $matches[1];
				$scale = $matches[2];
			} else if (preg_match('/^float\((\d+),(\d+)\)/', $row[$type], $matches)) {
				$row[$type] = 'float';
				$precision = $matches[1];
				$scale = $matches[2];
			} else if (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row[$type], $matches)) {
				$row[$type] = $matches[1];
			}
			if (strtoupper($row[$key]) == 'PRI') {
				$primary = true;
				$primaryPosition = $p;
				if ($row[$extra] == 'auto_increment') {
					$identity = true;
				} else {
					$identity = false;
				}
				++$p;
			}
			$desc[$row[$field]] = array(
				'TABLE_NAME'       => $tableName,
				'COLUMN_NAME'      => $row[$field],
				'COLUMN_POSITION'  => $i,
				'DATA_TYPE'        => $row[$type],
				'DEFAULT'          => $row[$default],
				'NULLABLE'         => (bool) ($row[$null] == 'YES'),
				'LENGTH'           => $length,
				'SCALE'            => $scale,
				'PRECISION'        => $precision,
				'UNSIGNED'         => $unsigned,
				'PRIMARY'          => $primary,
				'PRIMARY_POSITION' => $primaryPosition,
				'IDENTITY'         => $identity
			);
			++$i;
		}
		return $desc;
	}

	/**
	 * 生成数量语句
	 *
	 * @param string $sql
	 * @param int $count
	 * @return string
	 */
	public function limit($sql, $count)
	{
		$count = ($count > 0) ? $count : 1;
		$sql .= " LIMIT $count";
		return $sql;
	}

	/**
	 * 生成分页语句
	 *
	 * @param string $sql
	 * @param int $page
	 * @param int $rowCount
	 * @return string
	 */
	public function limitPage($sql, $page, $rowCount)
	{
		$page     = ($page > 0)     ? $page     : 1;
		$rowCount = ($rowCount > 0) ? $rowCount : 1;
		$offset   = $rowCount * ($page - 1);
		$sql .= " LIMIT $rowCount OFFSET $offset";
		return $sql;
	}

	/**
	 * 获取最后一次插入数据的ID
	 *
	 * @return int
	 */
	public function lastInsertId()
	{
		return $this->getConnection()->lastInsertId();
	}

	/**
	 * 获取PDO的版本
	 *
	 * @return string
	 */
	public function getServerVersion()
	{
		try {
			$version = $this->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION);
		} catch (PDOException $e) {
			return null;
		}
		$matches = null;
		if (preg_match('/((?:[0-9]{1,2}\.){1,3}[0-9]{1,2})/', $version, $matches)) {
			return $matches[1];
		} else {
			return null;
		}
	}
}

/************************************************
 * 缓存
 */
class TitanCache
{
	/**
	 * 缓存模式
	 * @var string
	 */
	private $_method = 'file';

	/**
	 * 数据库对象
	 * @var TitanDB
	 */
	private $_db;

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		global $_CONFIG;
		$this->_db = Titan::$db;
	}

	/**
	 * 是否存在可用的缓存数据
	 *
	 * @param string $sql
	 * @param int $time
	 * @return bool
	 */
	public function isExists($sql, $time)
	{
		$cacheName = $this->_cacheName($sql);
		switch ($this->_method) {
			case 'file':
				if (file_exists(VAR_PATH . 'cache/sql/' . $cacheName . '.sql')
					&& !$this->isExpired($sql, $time)) {
					return true;
				} else {
					return false;
				}
				break;
			case 'database':
				return false;
				break;
			case 'memcache':
				return false;
				break;
			case 'none':
			default:
				return false;
				break;
		}
	}

	/**
	 * 缓存数据是否过期
	 *
	 * @param string $sql
	 * @param int $time
	 * @return bool
	 */
	public function isExpired($sql, $time)
	{
		$cacheName = $this->_cacheName($sql);
		switch ($this->_method) {
			case 'file':
				if (@filemtime(VAR_PATH . 'cache/sql/' . $cacheName . '.sql') > (time() - $time)) {
					return false;
				} else {
					return true;
				}
				break;
			case 'database':
				return true;
				break;
			case 'memcache':
				return true;
				break;
			case 'none':
			default:
				return true;
				break;
		}
	}

	/**
	 * 删除指定缓存数据
	 *
	 * @param string $sql
	 * @return bool
	 */
	public function del($sql)
	{
		$cacheName = $this->_cacheName($sql);
		switch ($this->_method) {
			case 'file':
				@unlink(VAR_PATH . 'cache/sql/' . $cacheName . '.sql');
				return true;
				break;
			case 'database':
				return true;
				break;
			case 'memcache':
				return true;
				break;
			case 'none':
			default:
				return true;
				break;
		}
	}

	/**
	 * 写入缓存数据
	 *
	 * @param string $sql
	 * @param $data
	 * @return bool
	 */
	public function set($sql, $data)
	{
		$cacheName = $this->_cacheName($sql);
		switch ($this->_method) {
			case 'file':
				$data = serialize($data);
				$fp = fopen(VAR_PATH . 'cache/sql/' . $cacheName . '.sql', 'w');
				fputs($fp, $data);
				fclose($fp);
				return true;
				break;
			case 'database':
				return true;
				break;
			case 'memcache':
				return true;
				break;
			case 'none':
			default:
				return true;
				break;
		}
	}

	/**
	 * 读取缓存数据
	 *
	 * @param $sql
	 * @return bool|mixed
	 */
	public function get($sql)
	{
		$cacheName = $this->_cacheName($sql);
		switch ($this->_method) {
			case 'file':
				$cache = file(VAR_PATH . 'cache/sql/' . $cacheName . '.sql');
				$data = unserialize(implode('', $cache));
				return $data;
				break;
			case 'database':
				return true;
				break;
			case 'memcache':
				return true;
				break;
			case 'none':
			default:
				return true;
				break;
		}
	}

	/**
	 * 清空缓存数据
	 *
	 * @return bool
	 */
	public function flush()
	{
		switch ($this->_method) {
			case 'file':
				if ($dir = @dir(VAR_PATH . 'cache/sql/')) {
					while ($file = $dir->read()) {
						if (strstr($file, '.sql')) {
							@unlink(VAR_PATH . 'cache/sql/' . $file);
						}
					}
					$dir->close();
				}
				return true;
				break;
			case 'database':
				return true;
				break;
			case 'memcache':
				return true;
				break;
			case 'none':
			default:
				return true;
				break;
		}
	}

	/**
	 * 生成缓存名称
	 *
	 * @param $sql
	 * @return bool|string
	 */
	private function _cacheName($sql)
	{
		switch ($this->_method) {
			case 'file':
				return md5($sql);
				break;
			case 'database':
				return md5($sql);
				break;
			case 'memcache':
				return md5($sql);
				break;
			case 'none':
			default:
				return true;
				break;
		}
	}
}
