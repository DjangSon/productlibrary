<?php
class System_ConfigController extends CustomController
{
	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('system/config/index');
	}

	public function indexAction()
	{
		$this->_view->render('system/config/group');
	}

	public function groupListAction()
	{
		$page   = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows   = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$option = array(
			'where' => array('type' => array('eq', '系统配置分组')),
			'order' => array('sort' => 'ASC')
		);
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$total = $dictionaryModel->getTotalList($option);
		$data  = $dictionaryModel->getList($page, $rows, $option);
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}

	public function addGroupAction()
	{
		$data['type']   = '系统配置分组';
		$data['name']   = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['status'] = (isset($_POST['status']) && $_POST['status'] == '1') ? 1 : 0;
		$data['sort']   = isset($_POST['sort']) ? (int)$_POST['sort'] : 0;
		$data['by_added']   = $_SESSION['user_account'];
		$data['date_added'] = now();
		$result = array('error' => false, 'msg' => array());
		$dictionaryModel = $this->_loadModel('system/dictionary');

		$where = array(
			'type' => array('eq', $data['type']),
			'name' => array('eq', $data['name'])
		);
		if (empty($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '分组名称不能为空';
		} elseif ($dictionaryModel->getTotalList(array('where' => $where))) {
			$result['error'] = true;
			$result['msg'][] = '分组名称已存在';
		}
		if (!$result['error']) {
			if ($dictionaryModel->add($data)) {
				$result['msg'][] = '分组添加成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '分组添加失败';
			}
		}
		return $this->_ajaxReturn($result);
	}

	public function getGroupAction()
	{
		$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
		$dictionaryMode = $this->_loadModel('system/dictionary');
		$data = $dictionaryMode->get($id, 'name, status, sort');
		return $this->_ajaxReturn($data);
	}

	public function updateGroupAction()
	{
		$dictionaryId   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
		$data['name']   = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['status'] = (isset($_POST['status']) && $_POST['status'] == '1') ? 1 : 0;
		$data['sort']   = isset($_POST['sort']) ? (int)$_POST['sort'] : 0;
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result = array('error' => false, 'msg' => array());
		$dictionaryModel = $this->_loadModel('system/dictionary');

		$where = array(
			'type' => array('eq', '系统配置分组'),
			'name' => array('eq', $data['name']),
			'dictionary_id' => array('neq', $dictionaryId)
		);
		if (empty($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '分组名称不能为空';
		} elseif ($dictionaryModel->getTotalList(array('where' => $where)) > 0) {
			$result['error'] = true;
			$result['msg'][] = '分组名称已存在';
		}
		if (!$result['error']) {
			if ($dictionaryModel->update($data, $dictionaryId)) {
				$result['msg'][] = '分组更新成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '分组更新失败';
			}
		}
		return $this->_ajaxReturn($result);
	}

	public function delGroupAction()
	{
		$dictionaryId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
		$type   = isset($_POST['type']) ? trim($_POST['type']) : '';
		$result = array('error' => false, 'msg' => array());
		$where  = array('type' => array('eq', $type));
		$configModel = $this->_loadModel('system/config');
		if ($configModel->getTotalList(array('where' => $where))) {
			$result['error'] = true;
			$result['msg'][] = '分组中存在配置数据，无法删除';
		} else {
			$dictionaryModel = $this->_loadModel('system/dictionary');
			if ($dictionaryModel->del($dictionaryId)) {
				$result['msg'][] = '删除成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '删除失败';
			}
		}
		return $this->_ajaxReturn($result);
	}

	public function configAction()
	{
		$type = isset($_GET['type']) ? trim($_GET['type']) : '';
		$this->_view->assign('type', $type);
		$this->_view->render('system/config/index');
	}

	public function listAction()
	{
		$page   = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows   = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$type   = isset($_GET['type']) ? trim($_GET['type']) : '';
		$option = array(
			'where' => array('type' => array('eq', $type)),
			'order' => array('config_id' => 'ASC'),
			'col'   => 'config_id, config_title, config_key, config_value,
						by_added, by_modified, date_added, date_modified'
		);
		$configModel = $this->_loadModel('system/config');
		$total = $configModel->getTotalList($option);
		$data  = $configModel->getList($page, $rows, $option);
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}

	public function getAction()
	{
		$configId    = isset($_GET['config_id']) ? $_GET['config_id'] : 0;
		$configModel = $this->_loadModel('system/config');
		$data = $configModel->get($configId, 'config_title, config_key, config_value');
		$this->_ajaxReturn($data);
	}

	public function addAction()
	{
		$data['type']         = isset($_GET['type']) ? trim($_GET['type']) : '';
		$data['config_title'] = isset($_POST['config_title']) ? $_POST['config_title'] : '';
		$data['config_key']   = isset($_POST['config_key']) ? $_POST['config_key'] : '';
		$data['config_value'] = isset($_POST['config_value']) ? $_POST['config_value'] : '';
		$data['by_added']     = $_SESSION['user_account'];
		$data['date_added']   = now();
		$result = array('error' => false, 'msg' => array());
		$configModel = $this->_loadModel('system/config');
		if (strlen($data['config_title']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '配置标题不能为空';
		}
		if (strlen($data['config_key']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '配置键名不能为空';
		} elseif ($configModel->existConfig_key($data['config_key'])) {
			$result['error'] = true;
			$result['msg'][] = '配置键名已存在';
		}
		if (strlen($data['config_value']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '配置键值不能为空';
		}
		if (!$result['error']) {
			if ($configModel->add($data)) {
				$result['msg'][] = '添加成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '添加失败';
			}
		}
		return $this->_ajaxReturn($result);
	}

	public function delAction()
	{
		$configId = isset($_POST['config_id']) ? $_POST['config_id'] : 0;
		$result   = array('error' => false, 'msg' => array());
		$configModel = $this->_loadModel('system/config');
		if ($configModel->del($configId)) {
			$result['msg'][] = '删除成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '删除失败';
		}
		$this->_ajaxReturn($result);
	}

	public function updateAction()
	{
		$configId              = isset($_GET['config_id']) ? $_GET['config_id'] : 0;
		$data['config_title']  = isset($_POST['config_title']) ? $_POST['config_title'] : '';
		$data['config_key']    = isset($_POST['config_key']) ? $_POST['config_key'] : '';
		$data['config_value']  = isset($_POST['config_value']) ? $_POST['config_value'] : '';
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result = array('error' => false, 'msg' => array());
		$configModel = $this->_loadModel('system/config');
		if (strlen($data['config_title']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '配置标题不能为空';
		}
		if (strlen($data['config_key']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '配置键名不能为空';
		} elseif ($configModel->existConfig_key($data['config_key'], $configId)) {
			$result['error'] = true;
			$result['msg'][] = '配置键名已存在';
		}
		if (strlen($data['config_value']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '配置键值不能为空';
		}
		if (!$result['error']) {
			if ($configModel->update($data, $configId)) {
				$result['msg'][] = '修改成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '修改失败';
			}
		}
		return $this->_ajaxReturn($result);
	}
}
