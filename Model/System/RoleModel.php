<?php
class System_RoleModel extends CustomModel
{
	private $_data = array();
	/**
	 * 自定义初始化
	 */
	public function _construct()
	{
		$this->_init('system/role', 'role_id');
	}

	public function getTree($where)
	{
		$where = $this->_db->parseWhere($where);
		$sql = "SELECT $this->_idFieldName, name, parent_id FROM $this->_mainTable"
			. (($where) ? " WHERE $where" : '');
		$list = $this->_db->fetchAll($sql);
		if (!empty($list)) {
			foreach ($list as $val) {
				$this->_data[$val['parent_id']][] = $val;
			}
		}
		return $this->_buildBranch(key($this->_data));
	}

	/**
	 * Describe      生成角色树
	 * User          黄力军
	 * DateAdded     2016-9-6
	 * DateModified
	 *
	 * @param $parentId
	 * @return array
	 */
	private function _buildBranch($parentId)
	{
		$result = array();
		if (isset($this->_data[$parentId])) {
			foreach ($this->_data[$parentId] as $val) {
				$result[] = array(
					'id'       => $val['role_id'],
					'state'	   => isset($this->_data[$val['role_id']]) ? 'closed' : '',
					'text'     => $val['name'],
					'children' => isset($this->_data[$val['role_id']]) ? $this->_buildBranch($val['role_id']) : array()
				);
			}
		}
		return $result;
	}
}
