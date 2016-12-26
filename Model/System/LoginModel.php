<?php
class System_LoginModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('system/login', 'login_id');
	}
	
	public function addLogin($data)
	{
		if ($this->add($data)) {
			if ($data['status'] == '1') {
				$where = array(
					'ip'     => array('eq', $data['ip']),
					'status' => array('eq', '0')
				);
				$this->updateByWhere(array('status' => '2'), $where);
			}
		}
	}
}
