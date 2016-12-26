<?php

/**
 * Class Product_OptionController
 * User: 王天贵
 * Date: 2016-08-12
 */
class Product_OptionController extends CustomController
{
	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('product/option/index');
	}

	public function indexAction()
	{
		$this->optionAction();
	}

	/**
	 * Describe		 获取选项页面
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function optionAction()
	{
		$this->_view->render('product/option/option');
	}

	/**
	 * Describe		 添加选项
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function optionAddAction()
	{
		$data['name']       = isset($_POST['name']) ? $_POST['name'] : '';
		$data['type']       = isset($_POST['type']) ? $_POST['type'] : '';
		$data['sort']       = isset($_POST['sort']) ? $_POST['sort'] : 0;
		$data['by_added']   = $_SESSION['user_account'];
		$data['date_added'] = now();
		$result             = array('error' => false, 'msg' => array());
		$optionModel        = $this->_loadModel('product/option');
		if (!strlen($data['type'])) {
			$result['error'] = true;
			$result['msg'][] = '选项类型不能为空';
		}
		if (empty($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '选项名称不能为空';
		} elseif ($optionModel->existName($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '选项名称已存在';
		}
		if (!$result['error']) {
			if ($optionModel->add($data)) {
				$result['msg'][] = '添加成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '添加失败';
			}
		}
		return $this->_ajaxReturn($result);
	}

	/**
	 * Describe		 删除选项
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function optionDelAction()
	{
		$optionId    = isset($_POST['option_id']) ? $_POST['option_id'] : 0;
		$result      = array('error' => false, 'msg' => array());
		$optionModel = $this->_loadModel('product/option');
		if ($optionModel->del($optionId)) {
			$result['msg'][] = '删除成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '删除失败';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe		 选项列表
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function optionListAction()
	{
		$page   = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows   = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$option = array(
			'order' => array('sort' => 'ASC'),
			'col'   => 'option_id, type, name, sort,
						by_added, by_modified, date_added, date_modified'
		);
		$optionModel = $this->_loadModel('product/option');
		$total       = $optionModel->getTotalList($option);
		$data        = $optionModel->getList($page, $rows, $option);
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));

	}

	/**
	 * Describe		 获取选项
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function optionSelectAction()
	{
		$optionId    = isset($_GET['option_id']) ? $_GET['option_id'] : 0;
		$optionModel = $this->_loadModel('product/option');
		$data        = $optionModel->get($optionId, 'name, type, sort');
		$this->_ajaxReturn($data);
	}

	/**
	 * Describe		 更新选项
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function optionUpdateAction()
	{
		$optionId              = isset($_GET['option_id']) ? $_GET['option_id'] : 0;
		$data['name']          = isset($_POST['name']) ? $_POST['name'] : '';
		$data['type']          = isset($_POST['type']) && !empty($_POST['type']) ? 1 : 0;
		$data['sort']          = isset($_POST['sort']) ? $_POST['sort'] : 0;
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result                = array('error' => false, 'msg' => array());
		$optionModel           = $this->_loadModel('product/option');
		if (strlen($data['name']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '选项名称不能为空';
		} elseif ($optionModel->existName($data['name'], $optionId)) {
			$result['error'] = true;
			$result['msg'][] = '选项名称已存在';
		}
		if (!$result['error']) {
			if ($optionModel->update($data, $optionId)) {
				$result['msg'][] = '修改成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '修改失败';
			}
		}
		return $this->_ajaxReturn($result);
	}

	/**
	 * Describe		 选项值管理
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function valueAction()
	{
		$optionId    = isset($_GET['option_id']) ? $_GET['option_id'] : 0;
		$optionModel = $this->_loadModel('product/option');
		$option      = $optionModel->get($optionId, 'option_id, name');
		$this->_view->assign('option', $option);
		$this->_view->render('product/option/value');
	}

	/**
	 * Describe		 添加选项值
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function valueAddAction()
	{
		$data['option_id']  = isset($_GET['option_id']) ? $_GET['option_id'] : 0;
		$data['name']       = isset($_POST['name']) ? $_POST['name'] : '';
		$data['sort']       = isset($_POST['sort']) ? $_POST['sort'] : 0;
		$data['by_added']   = $_SESSION['user_account'];
		$data['date_added'] = now();
		$result             = array('error' => false, 'msg' => array());

		// 数据验证
		$valueModel = $this->_loadModel('product/option/value');
		$where      = array(
			'option_id' => array('eq', $data['option_id']),
			'name'      => array('eq', $data['name'])
		);
		if (!strlen($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '选项值名称不能为空';
		} elseif ($valueModel->getTotalList(array('where' => $where))) {
			$result['error'] = true;
			$result['msg'][] = '选项值名称已存在';
		}
		if (!$result['error']) {
			if ($valueModel->add($data)) {
				$result['msg'][] = '添加成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '添加失败';
			}
		}
		return $this->_ajaxReturn($result);
	}

	/**
	 * Describe		 删除选项值
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function valueDelAction()
	{
		$optionValueId = isset($_POST['option_value_id']) ? $_POST['option_value_id'] : 0;
		$result        = array('error' => false, 'msg' => array());
		$valueModel    = $this->_loadModel('product/option/value');
		if ($valueModel->del($optionValueId)) {
			$result['msg'][] = '删除成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '删除失败';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe		 选项值列表
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function valueListAction()
	{
		$optionId = isset($_GET['option_id']) ? $_GET['option_id'] : 0;
		$page     = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows     = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$option   = array(
			'where' => array('option_id' => array('eq', $optionId)),
			'order' => array('sort' => 'ASC'),
			'col'   => 'option_value_id, option_id, name, sort,
						by_added, by_modified, date_added, date_modified'
		);
		$valueModel = $this->_loadModel('product/option/value');
		$total = $valueModel->getTotalList($option);
		$data  = $valueModel->getList($page, $rows, $option);
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}

	/**
	 * Describe		 导入选项值
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function valueOptionImportAction()
	{
		$optionId = isset($_GET['option_id']) ? $_GET['option_id'] : 0;
		$result   = array('error' => false, 'msg' => array());
		$optionValueStat = array('total' => 0, 'success' => 0, 'fail' => 0);
		if (!isset($_FILES['optionValueUpload-fl']['error']) || $_FILES['optionValueUpload-fl']['error'] == 4) {
			$result['error'] = true;
			$result['msg'][] = '上传的数据不能为空';
			$this->_ajaxReturn($result);
		}
		$fileLocation = $_FILES['optionValueUpload-fl']['tmp_name'];
		if (!file_exists($fileLocation)) {
			$result['error'] = true;
			$result['msg'][] = '文件不存在。';
			$this->_ajaxReturn($result);
		} elseif (!($handle = fopen($fileLocation, 'r'))) {
			$result['error'] = true;
			$result['msg'][] = '文件无法读取。';
			$this->_ajaxReturn($result);
		}

		// 加载模型
		$optionValueModel = $this->_loadModel('product/option/value');

		// 超时
		set_time_limit(0);

		// 丢弃第一行数据
		fgetcsv($handle);
		$i = 1;

		// 生成CSV表
		while ($row = fgetcsv($handle)) {
			$i++;
			$row = array_map('trim', $row);

			// 加载数据
			$name = $row[0];
			$sort = $row[1];

			// 验证数据
			$where = array(
				'option_id' => array('eq', $optionId),
				'name'      => array('eq', $name)
			);
			if (strlen($name) < 1) {
				$result['msg'][] = sprintf('第%s行选项值不能为空', $i);
				continue;
			} elseif ($optionValueModel->getTotalList(array('where' => $where))) {
				$result['msg'][] = sprintf('第%s行选项值名称:%s已存在', $i, $name);
				continue;
			}

			// 添加数据
			$data = array(
				'option_id'  => $optionId,
				'name'       => $name,
				'sort'       => $sort,
				'by_added'   => $_SESSION['user_account'],
				'date_added' => now()
			);
			if ($optionValueModel->add($data)) {
				$optionValueStat['success']++;
			} else {
				$result['msg'][] = sprintf('第%s行选项值:%s添加失败', $i, $name);
				$optionValueStat['fail']++;
			}
			$optionValueStat['total']++;
		}
		$result['msg'][] = sprintf('选项值插入总计：%s条，成功%s条，失败%s条', $optionValueStat['total'], $optionValueStat['success'], $optionValueStat['fail']);
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe		 获取选项值
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function valueSelectAction()
	{
		$optionValueId = isset($_GET['option_value_id']) ? $_GET['option_value_id'] : 0;
		$valueModel    = $this->_loadModel('product/option/value');
		$data          = $valueModel->get($optionValueId, 'name, option_id, sort');
		$this->_ajaxReturn($data);
	}

	/**
	 * Describe		 修改选项值
	 * User          王天贵
	 * DateAdded     2016-08-12
	 * DateModified
	 */
	public function valueUpdateAction()
	{
		$optionValueId         = isset($_GET['option_value_id']) ? $_GET['option_value_id'] : 0;
		$data['name']          = isset($_POST['name']) ? $_POST['name'] : '';
		$data['sort']          = isset($_POST['sort']) ? $_POST['sort'] : 0;
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result = array('error' => false, 'msg' => array());
		$valueModel = $this->_loadModel('product/option/value');
		if (!strlen($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '选项值名称不能为空';
		} elseif ($valueModel->existName($data['name'], $optionValueId)) {
			$result['error'] = true;
			$result['msg'][] = '选项值名称已存在';
		}

		if (!$result['error']) {
			if ($valueModel->update($data, $optionValueId)) {
				$result['msg'][] = '修改成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '修改失败';
			}
		}
		return $this->_ajaxReturn($result);
	}
}
