<?php
class Product_GroupModel extends CustomModel
{
    /**
     * 自定义初始化
     */
    protected function _construct()
    {
        $this->_init('product/group', 'group_id');
    }
}