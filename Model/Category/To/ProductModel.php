<?php
class Category_To_ProductModel extends CustomModel
{
    /**
     * 自定义初始化
     */
    protected function _construct()
    {
        $this->_init('category/to/product', 'product_id');
    }
}