<?php
class ProductModel extends CustomModel
{
    /**
     * 自定义初始化
     */
    protected function _construct()
    {
        $this->_init('product', 'product_id');
    }
}