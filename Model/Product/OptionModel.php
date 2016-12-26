<?php
class Product_OptionModel extends CustomModel
{
    /**
     * 自定义初始化
     */
    protected function _construct()
    {
        $this->_init('product/option', 'option_id');
    }
}