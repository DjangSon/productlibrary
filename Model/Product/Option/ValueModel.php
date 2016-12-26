<?php
class Product_Option_ValueModel extends CustomModel
{
    /**
     * 自定义初始化
     */
    protected function _construct()
    {
        $this->_init('product/option/value', 'option_value_id');
    }
}