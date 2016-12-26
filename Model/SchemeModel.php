<?php
class SchemeModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('scheme', 'scheme_id');
	}
}