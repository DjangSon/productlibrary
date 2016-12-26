<?php

/**
 * Class Product_AttributeController
 * User  黄力军
 * Date  2016-8-12
 */
class Product_AttributeController extends CustomController
{
	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('product/attribute/index');
	}

	/**
	 * Describe      属性管理主页
	 * User          黄力军
	 * DateAdded     ${DATE}
	 * DateModified
	 */
	public function indexAction()
	{
		$this->_view->render('product/attribute/index');
	}

	/**
	 * Describe      获取产品属性
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function listAction()
	{
		$page   = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows   = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$option = array(
			'where' => array('type' => array('eq', '产品属性')),
			'order' => array('sort' => 'ASC'),
		);
		$brandModel = $this->_loadModel('system/dictionary');
		$total = $brandModel->getTotalList($option);
		$data  = $brandModel->getListByLarge($page, $rows, $option);
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}

	/**
	 * Describe      获取指定产品属性
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function getAction()
	{
		$dictionaryId    = isset($_GET['dictionary_id']) ? $_GET['dictionary_id'] : '';
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$data = $dictionaryModel->get($dictionaryId, 'name, sort');
		$this->_ajaxReturn($data);
	}

	/**
	 * Describe      添加产品属性
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function addAction()
	{
		$data['name']       = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['sort']       = (isset($_POST['sort']) && $_POST['sort'] > 0) ? (int)$_POST['sort'] : 0;
		$data['status']		= 1;
		$data['type']		= '产品属性';
		$data['by_added']   = $_SESSION['user_account'];
		$data['date_added'] = now();
		$result = array('error' => false, 'msg' => array());

		// 判断属性是否合法
		$where = array(
			'type' => array('eq', $data['type']),
			'name' => array('eq', $data['name'])
		);
		$dictionaryModel = $this->_loadModel('system/dictionary');
		if (strlen($data['name']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '属性名称不能为空';
		} elseif ($dictionaryModel->getTotalList(array('where' => $where))) {
			$result['error'] = true;
			$result['msg'][] = '属性名称已存在';
		}

		// 出错返回
		if ($result['error']) {
			$this->_ajaxReturn($result);
		}

		if ($dictionaryModel->add($data)) {
			$result['msg'][] = '添加成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '添加失败';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      修改产品属性
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function updateAction()
	{
		$dictionaryId          = isset($_GET['dictionary_id']) ? (int)$_GET['dictionary_id'] : 0;
		$data['sort']          = (isset($_POST['sort']) && $_POST['sort'] > 0) ? (int)$_POST['sort'] : 0;
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result                = array('error' => false, 'msg' => array());

		// 数据验证
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$attribute       = $dictionaryModel->get($dictionaryId, 'type');
		if (empty($attribute) || $attribute['type'] != '产品属性') {
			$result['error'] = true;
			$result['msg'][] = '非法操作';
			$this->_ajaxReturn($result);
		}

		// 更新数据
		if ($dictionaryModel->update($data, $dictionaryId)) {
			$result['msg'][] = '修改成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '修改失败';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      启用指定产品属性
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function enabledAction()
	{
		$ids = isset($_POST['ids']) ? $_POST['ids'] : 0;
		$this->_updateStatus(1, '启用', $ids);
	}

	/**
	 * Describe      停用指定产品属性
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function disableAction()
	{
		$ids = isset($_POST['ids']) ? $_POST['ids'] : 0;
		$this->_updateStatus(0, '停用', $ids);
	}

	/**
	 * Describe      更新状态
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * @param integer $status
	 * @param string  $name
	 * @param array   $ids
	 */
	private function _updateStatus($status, $name, $ids)
	{
		$result          = array('error' => false, 'msg' => array());
		$dictionaryModel = $this->_loadModel('system/dictionary');

		$where = array(
			'dictionary_id' => array('in', $ids),
			'type'          => array('eq', '产品属性')
		);
		$data = array(
			'status'        => $status,
			'by_modified'   => $_SESSION['user_account'],
			'date_modified' => now()
		);
		if ($dictionaryModel->updateByWhere($data, $where)) {
			$result['msg'][] = $name . '成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = $name . '失败';
		}
		$this->_ajaxReturn($result);
	}
}
