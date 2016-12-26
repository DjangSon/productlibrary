<?php
class System_LogModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('system/log', 'log_id');
	}
}
