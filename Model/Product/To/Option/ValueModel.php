<?php
class Product_To_Option_ValueModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('product/to/option/value', 'product_id');
	}
}