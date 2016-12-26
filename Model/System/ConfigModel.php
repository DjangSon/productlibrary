<?php
class System_ConfigModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('system/config', 'config_id');
	}
}
