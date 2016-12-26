<?php
/************************************************
 * 自定义控制器
 */
class CustomController extends TitanController
{
	/**
	 * 自定义构造函数
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_initConfig();
	}

	/**
	 * 初始化系统配置
	 *
	 * @return void
	 */
	protected function _initConfig()
	{
		$option = array(
			'col'       => 'config_key, config_value',
			'cacheTime' => 86400
		);
		$configModel = $this->_loadModel('system/config');
		$configList  = $configModel->getAllList($option);
		if (!empty($configList)) {
			foreach ($configList as $val) {
				if(!defined(strtoupper($val['config_key']))) {
					define(strtoupper($val['config_key']), $val['config_value']);
				}
			}
		}
	}

	/**
	 * 验证当前角色是否有权限
	 *
	 * @param string $url
	 */
	protected function _validationRole($url)
	{
		//验证登录
		$userModel = $this->_loadModel('system/user');
		if (!isset($_SESSION['user_id'])
			|| !$userModel->validateUser($_SESSION['user_id'])) {
			session_destroy();
			$this->_redirectUrl($this->getUrl('index/index/login'));
		}
		//验证密码强制修改
		if ($userModel->forcePassword($_SESSION['user_id'])) {
			$this->_redirectUrl($this->getUrl('index/index/forcePassword'));
		}
		//权限验证
		$rightModel = $this->_loadModel('system/right');
		if (!$rightModel->validateRight($_SESSION['user_id'], $url)) {
			exit('对不起,您没有访问权限!');
		}
	}

	/**
	 * 添加系统日志
	 * @param $contents
	 * @return bool
	 */
	public function _addSystemLog($contents)
	{
		if (empty($contents)) {
			return false;
		}
		$data = array(
			'user_id'  => $_SESSION['user_id'],
			'contents' => $contents,
			'ip'       => $this->getIp(),
			'date_added' => now()
		);
		$logModel = $this->_loadModel('system/log');
		if ($logModel->add($data)) {
			return true;
		}
		return false;
	}

	/**
	 * 返回错误消息
	 *
	 * @param string $msg
	 * @param array $result
	 */
	protected function _returnErrorMsg($msg, $result = array())
	{
		$result['error'] = true;
		$result['msg'][] = $msg;
		return $this->_ajaxReturn($result);
	}
}

/************************************************
 * 自定义模型
 */
class CustomModel extends TitanModel
{
	/**
	 * 批量插入数据
	 *
	 * @example $data[] = array(
	'message_id'   => $messageId,
	'doctor_id'    => $_SESSION['doctor_id'],
	'recipient_id' => $val,
	'recipient_openid' => $tempOpenid,
	'type'         => '0',
	'status'       => '0'
	);
	 * @param array $data
	 * @return boolean|number
	 */
	public function batchAdd($data)
	{
		if (empty($data) || !is_array($data)) {
			return false;
		}

		// custom
		$total = 0;   // 返回总数
		$rows  = 200; // 一次性插入的条数
		$fors  = 100; // 最多循环次数

		for ( $i = 1; $i <= $fors; $i++ ) {
			// 当数据被unset清空时跳出
			if ( count($data) <= 0 ) {
				break;
			}

			$sql  = "INSERT INTO $this->_mainTable"; // sql初始化
			$fieldnames = array();                   // 字段名
			$j = 1;                                  // 已加入sql条数

			foreach ($data as $key => $val) {
				$fieldvalues = array(); // 字段值

				// $val是add添加的$data
				foreach ($val as $k => $v) {
					if ($j == 1) $fieldnames[] = $k;
					$fieldvalues[] = str_replace("'", "\'", $v);
				}

				if ($j == 1) $sql .= " (". implode(',', $fieldnames) .") VALUES ";
				$sql .= "('". implode('\',\'', $fieldvalues) ."'),";
				unset($data[$key]);
				$j++;

				if ($j > $rows) {
					break;
				}
			}// foreach end
			$sql  = trim($sql, ',');
			$stmt = $this->_db->query($sql);
			$total += $stmt->rowCount();
		}
		return $total;
	}

	/**
	 * 验证数字主键
	 * @param int $id
	 * @return bool
	 */
	public function vNumberId($id)
	{
		if (!is_numeric($id) || $id < 1 || !$this->validate($id) ) {
			return false;
		}
		return true;
	}
}