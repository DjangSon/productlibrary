<?php
class System_RoleController extends CustomController
{
	private $_data = array();

	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('system/role/index');
		// 获取该用户的角色权限,路径和下级角色
		$userModel = $this->_loadModel('system/user');
		$user      = $userModel->get($_SESSION['user_id'], 'role_id');
		$this->_data['roleId'] = $user['role_id'];
		// 获取该用户的角色权限和路径
		$roleModel = $this->_loadModel('system/role');
		$role      = $roleModel->get($user['role_id'], 'rights, path');
		$this->_data['rolePath']   = $role['path'];
		$this->_data['roleRights'] = explode(',', $role['rights']);
		// 获取该用户角色的下级角色
		$option = array(
			'where' => array('path' => array('like', $role['path'] . '/', 'right')),
			'col'   => 'role_id, rights'

		);
		$roleList = $roleModel->getAllList($option);
		foreach ($roleList as $val) {
			$this->_data['roleList'][$val['role_id']] = explode(',', $val['rights']);
		}
	}

	public function indexAction()
	{
		$roleId = isset($_GET['role_id']) ? $_GET['role_id'] : 0;

		// 获取数据
		$roleModel = $this->_loadModel('system/role');
		if (!isset($this->_data['roleList'][$roleId])) {
			$where        = array('parent_id' => array('eq', $this->_data['roleId']));
			$childrenRole = $roleModel->getRow(array('where' => $where));
			$roleId       = $childrenRole['role_id'];
		}
		$roleData = $roleModel->get($roleId, 'role_id, rights, parent_id, name');
		if (isset($this->_data['roleList'][$roleData['parent_id']])) {
			$parentRights = $this->_data['roleList'][$roleData['parent_id']];
		} else {
			$tempRights   = $roleModel->get($roleData['parent_id'], 'rights');
			$parentRights = explode(',', $tempRights['rights']);
		}

		$option = array(
			'where' => array(),
			'order' => array('sort' => 'ASC'),
			'col'   => 'rightgroup_id, name'
		);
		$rightGroupModel = $this->_loadModel('system/rightgroup');
		$rightGroupList  = $rightGroupModel->getAllList($option);

		$option = array(
			'where' => array(
				'right_id' => array('in', $parentRights)
			),
			'order' => array('sort' => 'ASC'),
			'col'   => 'right_id, rightgroup_id, name'
		);
		$rightModel    = $this->_loadModel('system/right');
		$tempRightList = $rightModel->getAllList($option);
		$rightList     = array();
		if (!empty($tempRightList)) {
			foreach ($tempRightList as $val) {
				$rightList[$val['rightgroup_id']][] = $val;
			}
			unset($tempRightList);
		}
		foreach ($rightGroupList as $key => $rightGroup) {
			$rightGroupList[$key]['right'] = array();
			if (isset($rightList[$rightGroup['rightgroup_id']])) {
				$rightGroupList[$key]['right'] = $rightList[$rightGroup['rightgroup_id']];
			}
		}

		$where     = array('path' => array('like', $this->_data['rolePath'] . '/', 'right'));
		$roleTree  = $roleModel->getTree($where);
		$right     = $roleModel->get($roleId, 'rights');
		$roleRight = explode(',', $right['rights']);

		$this->_view->assign('roleTree', $roleTree);
		$this->_view->assign('roleRight', $roleRight);
		$this->_view->assign('roleData', $roleData);
		$this->_view->assign('rightGroupList', $rightGroupList);
		$this->_view->render('system/role/index');
	}

	public function addAction()
	{
		$data['name']       = isset($_POST['name']) ? $_POST['name'] : '';
		$data['by_added']   = $_SESSION['user_account'];
		$data['date_added'] = now();
		$result             = array('error' => false, 'msg' => array());
		$roleModel          = $this->_loadModel('system/role');
		if (strlen($data['name']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '角色名称不能为空';
		} elseif ($roleModel->existName($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '角色名称已存在';
		}

		// 出错返回
		if ($result['error']) {
			$this->_ajaxReturn($result);
		}

		$data['parent_id'] = $this->_data['roleId'];
		if ($roleModel->add($data)) {
			$roleId = $roleModel->lastInsertId();
			$path   = implode('/', array($this->_data['rolePath'], $roleId));
			$update = array('path' => $path);
			$roleModel->update($update, $roleId);
			$result['msg'][] = '添加成功';

			// 添加到系统日志
			$this->_addSystemLog(sprintf('[角色管理][添加角色][%s]', $data['name']));
		} else {
			$result['error'] = true;
			$result['msg'][] = '添加失败';
		}
		$this->_ajaxReturn($result);
	}

	public function delAction()
	{
		$roleId    = isset($_GET['role_id']) ? $_GET['role_id'] : 0;
		$result    = array('error' => false, 'msg' => array());
		$roleModel = $this->_loadModel('system/role');
		if (!isset($this->_data['roleList'][$roleId])) {
			$result['error'] = true;
			$result['msg'][] = '没有权限操作此角色';
			$this->_ajaxReturn($result);
		}

		$path  = implode('/', array($this->_data['rolePath'], $roleId));
		$where = array(
			'role_id|path' => array(
				array('eq', $roleId),
				array('like', $path . '/', 'right')
			)
		);
		if ($roleModel->delByWhere($where)) {
			$result['msg'][] = '删除成功';

			// 将使用该角色的用户的role_id修改为0
			$where     = array('role_id' => array('in', array_keys($this->_data['roleList'])));
			$userModel = $this->_loadModel('system/user');
			$userModel->updateByWhere(array('role_id' => '0'), $where);

			// 添加到系统日志
			$role = $roleModel->get($roleId, 'name');
			$this->_addSystemLog(sprintf('[角色管理][删除角色][%s]', $role['name']));
		} else {
			$result['error'] = true;
			$result['msg'][] = '删除失败';
		}
		$this->_ajaxReturn($result);
	}

	public function updateRightsAction()
	{
		$roleId    = isset($_GET['role_id']) ? $_GET['role_id'] : 0;
		$rights    = isset($_POST['rights']) ? $_POST['rights'] : array();
		$result    = array('error' => false, 'msg' => array());
		$roleModel = $this->_loadModel('system/role');
		if (!isset($this->_data['roleList'][$roleId])) {
			$result['error'] = true;
			$result['msg'][] = '没有权限操作此角色';
			$this->_ajaxReturn($result);
		}
		$role = $roleModel->get($roleId, 'parent_id, path');

		// 有效权限集合
		$rightList = array();

		$ruleRight = $role['parent_id'] == $this->_data['roleId'] ? $this->_data['roleRights'] : (isset($this->_data['roleList'][$role['parent_id']]) ? $this->_data['roleList'][$role['parent_id']] : array(0));
		foreach ($rights as $val) {
			if (in_array($val, $ruleRight)) {
				$rightList[] = $val;
			}
		}
		$data = array(
			'rights'        => implode(',', $rightList),
			'by_modified'   => $_SESSION['user_account'],
			'date_modified' => now()
		);
		if ($roleModel->update($data, $roleId)) {
			$option = array(
				'where' => array('path' => array('like', $role['path'] . '/', 'right')),
				'col'   => 'role_id, name, rights'
			);
			$childrenList = $roleModel->getAllList($option);
			foreach ($childrenList as $val) {
				$childrenRights = explode(',', $val['rights']);
				$childrenRights = array_intersect($childrenRights, $rightList);
				$childrenData   = array(
					'rights'        => implode(',', $childrenRights),
					'by_modified'   => $_SESSION['user_account'],
					'date_modified' => now()
				);
				if ($roleModel->update($childrenData, $val['role_id'])) {
					$result['msg'][] = sprintf('%s权限修改成功', $val['name']);
				}
			}
			$result['msg'][] = '权限分配成功';

			// 添加到系统日志
			$this->_addSystemLog(sprintf('[角色管理][%s][分配权限][%s]', $roleId, implode(', ', $rightList)));
		} else {
			$result['error'] = true;
			$result['msg'][] = '权限分配失败';
		}

		return $this->_ajaxReturn($result);
	}
}
