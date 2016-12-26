<?php
class Product_To_OptionModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('product/to/option', 'product_id');
	}
}