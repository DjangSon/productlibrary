<?php
class System_UserModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('system/user', 'user_id');
	}

	/**
	 * 登录验证
	 *
	 * @param string $account 用户名
	 * @param string $password 密码
	 * @return boolean
	 */
	public function validateLogin($account, $password)
	{
		$sql = "SELECT $this->_idFieldName, account,
					   name, password
				FROM   $this->_mainTable
				WHERE  account = :account
				AND    status = 1";
		$bind = array(':account' => $account);
		$data = $this->_db->fetchRow($sql, $bind);
		if (count($data) > 0) {
			if (v_password($password, $data['password'])) {
				return $data;
			}
		}
		return false;
	}

	/**
	 * 验证用户
	 *
	 * @param int $id 用户ID
	 * @return boolean
	 */
	public function validateUser($id)
	{
		$sql = "SELECT COUNT($this->_idFieldName) AS total
				FROM   $this->_mainTable
				WHERE  $this->_idFieldName = :ID
				AND    status = 1";
		$bind = array(':ID' => $id);
		if ($this->_db->fetchOne($sql, $bind)) {
			return true;
		}
		return false;
	}

	/**
	 * 验证密码
	 *
	 * @param string $password 密码
	 * @param int $id 用户ID
	 * @return boolean
	 */
	public function validatePassword($password, $id)
	{
		$sql = "SELECT password
				FROM   $this->_mainTable
				WHERE  $this->_idFieldName = :ID
				AND    status = 1";
		$bind = array(':ID' => $id);
		$pwd = $this->_db->fetchOne($sql, $bind);
		if (v_password($password, $pwd)) {
			return true;
		}
		return false;
	}

	/**
	 * 是否强制修改密码
	 *
	 * @param int $id 用户ID
	 * @return boolean
	 */
	public function forcePassword($id)
	{
		$sql = "SELECT COUNT($this->_idFieldName) AS total
				FROM   $this->_mainTable
				WHERE  $this->_idFieldName = :ID
				AND    (password1 IS NULL OR date_password IS NULL OR DATEDIFF(CURRENT_DATE(), date_password) > 90)";
		$bind = array(':ID' => $id);
		if ($this->_db->fetchOne($sql, $bind)) {
			return true;
		}
		return false;
	}

	/**
	 * 对比最近4次密码
	 *
	 * @param string $password 密码
	 * @param int $id 用户ID
	 * @return boolean
	 */
	public function contrastPassword($password, $id)
	{
		$sql = "SELECT password, password1,
					   password2, password3
				FROM   $this->_mainTable
				WHERE  $this->_idFieldName = :ID";
		$bind = array(':ID' => $id);
		$data = $this->_db->fetchRow($sql, $bind);
		foreach ($data as $pwd) {
			if (v_password($password, $pwd)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 修改密码
	 *
	 * @param string $password 密码
	 * @param int $id 用户ID
	 * @return int
	 */
	public function changePassword($password, $id)
	{
		$sql = "UPDATE $this->_mainTable
				SET    password3 = password2,
				       password2 = password1,
				       password1 = password,
				       password = :password,
				       by_modified = :modifiedUser,
				       date_password = NOW(),
				       date_modified = NOW()
				WHERE  $this->_idFieldName = :ID";
		$bind = array(
			':password'     => encrypt_password($password),
			':modifiedUser' => $_SESSION['user_account'],
			':ID'           => $id
		);
		$stmt = $this->_db->query($sql, $bind);
		$result = $stmt->rowCount();
		return $result;
	}

	/**
	 * 重置密码
	 *
	 * @param string $password 密码
	 * @param int $id 用户ID
	 * @return int
	 */
	public function resetPassword($password, $id)
	{
		$sql = "UPDATE $this->_mainTable
				SET    password3 = NULL,
				       password2 = NULL,
				       password1 = NULL,
				       password = :password,
				       by_modified = :modifiedUser,
				       date_password = NOW(),
				       date_modified = NOW()
				WHERE  $this->_idFieldName = :ID";
		$bind = array(
			':password'     => encrypt_password($password),
			':modifiedUser' => $_SESSION['user_account'],
			':ID'           => $id
		);
		$stmt = $this->_db->query($sql, $bind);
		$result = $stmt->rowCount();
		return $result;
	}
}
