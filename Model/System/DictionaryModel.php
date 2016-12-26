<?php
class System_DictionaryModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('system/dictionary', 'dictionary_id');
	}
	
	/**
	 * 获取字典类型
	 * 
	 * @param array $where
	 * @return Ambigous <multitype:, false, mixed, boolean>
	 */
	public function getTypeList($where = '')
	{
		$where = $this->_db->parseWhere($where);
		$sql   = "SELECT DISTINCT type FROM $this->_mainTable" . (($where) ? " WHERE $where" : '');
		return $this->_db->fetchCol($sql);
	}
	
	/**
	 * 根据类型获取字典数据集合
	 * 
	 * @param string  $type
	 * @param inactive boolean
	 * @return array
	 */
	public function getDictionaryList($type, $include_inactive = false)
	{
		if (empty($type)) {
			return array();
		}
		$statusSql = '';
		if (false === $include_inactive) {
			$statusSql = "AND status = '1'";
		}
		$sql = "SELECT name FROM $this->_mainTable
				WHERE  type = :type
				$statusSql
				ORDER BY sort";
		$bind = array(':type' => $type);
		return $this->_db->fetchCol($sql, $bind);
	}
	
	/**
	 * 根据类型、名称验证字典数据是否存在
	 *
	 * @param string $type
	 * @param string $name
	 * @return boolean
	 */
	public function existDictionary($type, $name)
	{
		if (empty($type) || empty($name)) {
			return false;
		}
		$sql = "SELECT COUNT($this->_idFieldName) AS total
				FROM   $this->_mainTable
				WHERE  status = 1
				AND    type = :type
				AND    name = :name";
		$bind = array(
			':type' => $type,
			':name' => $name
		);
		if ($this->_db->fetchOne($sql, $bind)) {
			return true;
		}
		return false;
	}
}