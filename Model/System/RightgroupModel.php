<?php
class System_RightgroupModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('system/rightgroup', 'rightgroup_id');
	}
}