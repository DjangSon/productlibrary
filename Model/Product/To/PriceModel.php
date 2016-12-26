<?php
class Product_To_PriceModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('product/to/price', 'product_id');
	}
}