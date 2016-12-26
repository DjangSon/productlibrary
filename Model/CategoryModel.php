<?php
class CategoryModel extends CustomModel
{
	/**
	 * 自定义初始化
	 */
	protected function _construct()
	{
		$this->_init('category', 'category_id');
	}

	/**
	 * 分类后修改父级子类数
	 *
	 * @param $categoryIds
	 * @param $count_prefix
	 * @param $count
	 * @return bool|int
	 */
	public function updateParentChildrenCount($categoryIds, $count_prefix = '+', $count = 1)
	{
		if (empty($categoryIds)
			|| !is_array($categoryIds)
			|| $count < 1) {
			return false;
		}
		$where = array('category_id' => array('in', $categoryIds));
		$where = $this->_db->parseWhere($where);
		$sql   = "UPDATE $this->_mainTable
			  	  SET children_count = (children_count $count_prefix $count)
			  	  WHERE $where";
		return $this->_db->query($sql)->rowCount();
	}
}