<?php
class Product_To_AttributeModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('product/to/attribute', 'product_id');
	}
}