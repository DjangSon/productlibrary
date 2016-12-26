<?php
class System_NoticeModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('system/notice', 'notice_id');
	}
	
	/**
	 * 关闭除了当前id以外的弹出状态
	 * @param integer $id
	 * @return number
	 */
	public function updatePopup($id){
		$where[$this->_idFieldName] = array('neq', $id);
		$where['is_popup'] = array('eq', '1');
		$data['is_popup']  = '0';
		return $this->_db->update($this->_mainTable, $data, $where);
	}
	
	/**
	 * 获取1篇顶置文章
	 * @return Ambigous <string, false>
	 */
	public function getPopup()
	{
		$sql = "SELECT $this->_idFieldName
				FROM   $this->_mainTable
				WHERE  is_popup = 1
				LIMIT  1";
		return $this->_db->fetchOne($sql);
	}
	
	/**
	 * 获取最大的id
	 * @return Ambigous <string, false>
	 */
	public function getBigId()
	{
		$sql = "SELECT $this->_idFieldName
				FROM   $this->_mainTable
				ORDER BY $this->_idFieldName DESC
				LIMIT 1";
		return $this->_db->fetchOne($sql);
	}
}
