<?php
class System_RightController extends CustomController
{
	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('system/right/index');
	}
	
	public function indexAction()
	{
		$this->groupAction();
	}
	
	// 权限分组
	public function groupAction()
	{
		$this->_view->render('system/right/group');
	}
	
	public function groupListAction()
	{
		$page   = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows   = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$option = array(
			'where' => array(),
			'order' => array('sort' => 'ASC'),
			'col'   => 'rightgroup_id, name, icon, sort,
						by_added, by_modified, date_added, date_modified'
		);
		$rightGroupModel = $this->_loadModel('system/rightgroup');
		$total = $rightGroupModel->getTotalList($option);
		$data  = $rightGroupModel->getList($page, $rows, $option);
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}
	
	public function getGroupAction()
	{
		$rightGroupId    = isset($_GET['rightgroup_id']) ? $_GET['rightgroup_id'] : 0;
		$rightGroupModel = $this->_loadModel('system/rightgroup');
		$data = $rightGroupModel->get($rightGroupId, 'name, icon, sort');
		$this->_ajaxReturn($data);
	}
	
	public function addGroupAction()
	{
		$data['name']       = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['icon']       = isset($_POST['icon']) ? trim($_POST['icon']) : '';
		$data['sort']       = isset($_POST['sort']) ? $_POST['sort'] : 0;
		$data['by_added']   = $_SESSION['user_account'];
		$data['date_added'] = now();
		$result = array('error' => false, 'msg' => array());
		$rightGroupModel = $this->_loadModel('system/rightgroup');
		if (strlen($data['name']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '分组名称不能为空';
		} elseif ($rightGroupModel->existName($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '分组名称已存在';
		}
		if (!$result['error']) {
			if ($rightGroupModel->add($data)) {
				$result['msg'][] = '添加成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '添加失败';
			}
		}
		return $this->_ajaxReturn($result);
	}
	
	public function delGroupAction()
	{
		$rightGroupId = isset($_POST['rightgroup_id']) ? $_POST['rightgroup_id'] : 0;
		$result       = array('error' => false, 'msg' => array());
		$rightModel   = $this->_loadModel('system/right');
		if ($rightModel->getTotalList(array('where' => array('rightgroup_id' => array('eq', $rightGroupId))))) {
			$result['error'] = true;
			$result['msg'][] = '分组中存在权限数据，无法删除';
		} else {
			$rightGroupModel = $this->_loadModel('system/rightgroup');
			if ($rightGroupModel->del($rightGroupId)) {
				$result['msg'][] = '删除成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '删除失败';
			}
		}
		$this->_ajaxReturn($result);
	}
	
	public function updateGroupAction()
	{
		$rightGroupId          = isset($_GET['rightgroup_id']) ? $_GET['rightgroup_id'] : 0;
		$data['name']          = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['icon']          = isset($_POST['icon']) ? trim($_POST['icon']) : '';
		$data['sort']          = isset($_POST['sort']) ? $_POST['sort'] : 0;
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result = array('error' => false, 'msg' => array());
		$rightGroupModel = $this->_loadModel('system/rightgroup');
		if (strlen($data['name']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '分组名称不能为空';
		} elseif ($rightGroupModel->existName($data['name'], $rightGroupId)) {
			$result['error'] = true;
			$result['msg'][] = '分组名称已存在';
		}
		if (!$result['error']) {
			if ($rightGroupModel->update($data, $rightGroupId)) {
				$result['msg'][] = '修改成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '修改失败';
			}
		}
		return $this->_ajaxReturn($result);
	}
	
	// 权限管理
	public function rightAction()
	{
		$rightGroupId    = isset($_GET['rightgroup_id']) ? $_GET['rightgroup_id'] : 0;
		$rightGroupModel = $this->_loadModel('system/rightgroup');
		$rightGroup      = $rightGroupModel->get($rightGroupId, 'rightgroup_id, name');
		$this->_view->assign('rightGroup', $rightGroup);
		$this->_view->render('system/right/right');
	}
	
	public function rightListAction()
	{
		$rightGroupId = isset($_GET['rightgroup_id']) ? $_GET['rightgroup_id'] : 0;
		$page   = isset($_POST['page'])?$_POST['page']:1;
		$rows   = isset($_POST['rows'])?$_POST['rows']:25;
		$option = array(
			'where' => array('rightgroup_id' => array('eq', $rightGroupId)),
			'order' => array('sort' => 'ASC'),
			'col'   => 'right_id, name, icon, url, sort, is_menu,
						by_added, by_modified, date_added, date_modified'
		);
		$rightModel = $this->_loadModel('system/right');
		$total = $rightModel->getTotalList($option);
		$data  = $rightModel->getList($page, $rows, $option);
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}
	
	public function getRightAction()
	{
		$rightId   = isset($_GET['right_id']) ? $_GET['right_id'] : 0;
		$rightModel = $this->_loadModel('system/right');
		$data = $rightModel->get($rightId, 'name, icon, url, sort, is_menu');
		$this->_ajaxReturn($data);
	}
	
	public function addRightAction()
	{
		$data['rightgroup_id'] = isset($_GET['rightgroup_id']) ? $_GET['rightgroup_id'] : 0;
		$data['name']          = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['icon']          = isset($_POST['icon']) ? trim($_POST['icon']) : '';
		$data['url']           = isset($_POST['url']) ? trim($_POST['url']) : '';
		$data['sort']          = isset($_POST['sort']) ? $_POST['sort'] : 0;
		$data['is_menu']       = isset($_POST['is_menu']) ? $_POST['is_menu'] : 0;
		$data['by_added']      = $_SESSION['user_account'];
		$data['date_added']    = now();
		$result = array('error' => false, 'msg' => array());
		$rightGroupModel = $this->_loadModel('system/rightgroup');
		$rightModel = $this->_loadModel('system/right');
		if (!$rightGroupModel->validate($data['rightgroup_id'])) {
			$result['error'] = true;
			$result['msg'][] = '分组错误';
		}
		if (strlen($data['name']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '权限名称不能为空';
		} elseif ($rightModel->existName($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '权限名称已存在';
		}
		if (strlen($data['url']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '地址不能为空';
		}
		if (!$result['error']) {
			if ($rightModel->add($data)) {
				$rightId    = $rightModel->lastInsertId();
				$roleModel  = $this->_loadModel('system/role');
				$superRight = $roleModel->get('1', 'rights');
				$rights     = implode(',', array($superRight['rights'], $rightId));
				$data       = array('rights' => $rights);
				if ($roleModel->update($data, '1')) {
					$result['msg'][] = '添加成功';
				}
			} else {
				$result['error'] = true;
				$result['msg'][] = '添加失败';
			}
		}
		return $this->_ajaxReturn($result);
	}
	
	public function delRightAction()
	{
		$rightId = isset($_POST['right_id']) ? $_POST['right_id'] : 0;
		$result  = array('error' => false, 'msg' => array());
		$rightModel = $this->_loadModel('system/right');
		if ($rightModel->del($rightId)) {
			$result['msg'][] = '删除成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '删除失败';
		}
		$this->_ajaxReturn($result);
	}
	
	public function updateRightAction()
	{
		$rightId 		       = isset($_GET['right_id']) ? $_GET['right_id'] : 0;
		$rightgroup_id         = isset($_GET['rightgroup_id']) ? $_GET['rightgroup_id'] : 0;
		$data['name']          = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['icon']          = isset($_POST['icon']) ? trim($_POST['icon']) : '';
		$data['url']           = isset($_POST['url']) ? trim($_POST['url']) : '';
		$data['sort']          = isset($_POST['sort']) ? $_POST['sort'] : 0;
		$data['is_menu']       = isset($_POST['is_menu']) ? $_POST['is_menu'] : 0;
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result = array('error' => false, 'msg' => array());
		$rightModel = $this->_loadModel('system/right');
		$where = array(
			'name'          => array('eq', $data['name']),
			'right_id'      => array('neq', $rightId),
			'rightgroup_id' => array('eq', $rightgroup_id)
		);
		if (strlen($data['name']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '权限名称不能为空';
		} elseif ($rightModel->getTotalList(array('where' => $where))) {
			$result['error'] = true;
			$result['msg'][] = '权限名称已存在';
		}
		if (strlen($data['url']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '权限地址不能为空';
		}
		if (!$result['error']) {
			if ($rightModel->update($data, $rightId)) {
				$result['msg'][] = '修改成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '修改失败';
			}
		}
		return $this->_ajaxReturn($result);
	}
}
