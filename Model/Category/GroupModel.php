<?php
class Category_GroupModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('category/group', 'group_id');
	}
}