<?php

/**
 * Class Product_PriceController
 * User: 王天贵
 * Date: 2016-08-12
 */
class Product_PriceController extends CustomController
{
	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('product/price/index');
	}
	
	public function indexAction()
	{
		$this->_view->render('product/price/index');
	}

	/**
	 * Describe      价格列表
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function listAction()
	{
		$page   = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows   = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$option = array(
			'where' => array('type' => array('eq', '产品价格')),
			'order' => array('sort' => 'ASC')
		);
		$priceModel = $this->_loadModel('system/dictionary');
		$total = $priceModel->getTotalList($option);
		$data  = $priceModel->getListByLarge($page, $rows, $option);
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}

	/**
	 * Describe      获取价格信息
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function getAction()
	{
		$dictionaryId    = isset($_GET['dictionary_id']) ? $_GET['dictionary_id'] : '';
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$data            = $dictionaryModel->get($dictionaryId, 'name, sort');
		$this->_ajaxReturn($data);
	}

	/**
	 * Describe      添加价格
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function addAction()
	{
		$data['name']       = isset($_POST['name']) ? $_POST['name'] : '';
		$data['sort']       = isset($_POST['sort']) ? (int)$_POST['sort'] : 0;
		$data['status']		= 1;
		$data['type']		= '产品价格';
		$data['by_added']   = $_SESSION['user_account'];
		$data['date_added'] = now();
		$result = array('error' => false, 'msg' => array());

		// 判断价格是否合法
		$where = array(
			'type' => array('eq', $data['type']),
			'name' => array('eq', $data['name'])
		);
		$dictionaryModel = $this->_loadModel('system/dictionary');
		if (empty($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '价格不能为空';
		} elseif ($dictionaryModel->getTotalList(array('where' => $where))) {
			$result['error'] = true;
			$result['msg'][] = '此价格已存在';
		}

		if (!$result['error']) {
			if ($dictionaryModel->add($data)) {
				$result['msg'][] = '添加成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '添加失败';
			}
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      修改价格
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function updateAction()
	{
		$dictionaryId          = isset($_GET['dictionary_id']) ? $_GET['dictionary_id'] : 0;
		$data['sort']          = isset($_POST['sort']) ? (int)$_POST['sort'] : 0;
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result                = array('error' => false, 'msg' => array());

		// 验证数据
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$price           = $dictionaryModel->get($dictionaryId, 'type, name');
		if (empty($price) || $price['type'] != '产品价格') {
			$result['error'] = true;
			$result['msg'][] = '非法操作';
			$this->_ajaxReturn($result);
		}

		// 修改数据
		if (!$result['error']) {
			if ($dictionaryModel->update($data, $dictionaryId)) {
				$result['msg'][] = '修改成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '修改失败';
			}
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      启用价格
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function enabledAction()
	{
		$ids = isset($_POST['ids']) ? $_POST['ids'] : 0;
		$this->_updateStatus(1, '启用', $ids);
	}

	/**
	 * Describe      停用价格
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function disableAction()
	{
		$ids = isset($_POST['ids']) ? $_POST['ids'] : 0;
		$this->_updateStatus(0, '停用', $ids);
	}

	/**
	 * Describe      更新价格状态
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 * @param $status
	 * @param $name
	 * @param $ids
	 */
	private function _updateStatus($status, $name, $ids)
	{
		$result          = array('error' => false, 'msg' => array());
		$dictionaryModel = $this->_loadModel('system/dictionary');

		$where = array(
			'dictionary_id' => array('in', $ids),
			'type'          => array('eq', '产品价格')
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
