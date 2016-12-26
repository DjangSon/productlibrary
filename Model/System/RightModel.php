<?php
class System_RightModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('system/right', 'right_id');
	}
	
	public function getMenu($user_id)
	{
		$data = array();
		$sql = "SELECT role_id FROM system_user WHERE user_id = :userID";
		$bind = array(':userID' => $user_id);
		if ($role_id = $this->_db->fetchOne($sql, $bind)) {
			$sql = "SELECT rights FROM system_role WHERE role_id = :roleID";
			$bind = array(':roleID' => $role_id);
			if ($rights = $this->_db->fetchOne($sql, $bind)) {
				$where = array('right_id' => array('in', $rights));
				$where = $this->_db->parseWhere($where);
				$sql = "SELECT rightgroup_id, name,
							   icon, url
						FROM   $this->_mainTable
						WHERE  $where
						AND    is_menu = 1
						ORDER BY sort ASC";
				$data = $this->_db->fetchAll($sql);
			}
		}
		return $data;
	}
	
	public function validateRight($user_id, $url)
	{
		$sql = "SELECT role_id FROM system_user WHERE user_id = :userID";
		$bind = array(':userID' => $user_id);
		if ($role_id = $this->_db->fetchOne($sql, $bind)) {
			$sql = "SELECT rights FROM system_role WHERE role_id = :roleID";
			$bind = array(':roleID' => $role_id);
			if ($rights = $this->_db->fetchOne($sql, $bind)) {
				$where = array(
					'right_id' => array('in', $rights),
					'url'      => array('eq', $url)
				);
				$where = $this->_db->parseWhere($where);
				$sql = "SELECT COUNT($this->_idFieldName)
						FROM   $this->_mainTable
						WHERE  $where";
				if ($this->_db->fetchOne($sql) > 0) {
					return true;
				}
			}
		}
		return false;
	}
}
