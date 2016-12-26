<?php
class System_UserController extends CustomController
{
	private $_data = array(
		'userList' => array(),
		'roleList' => array()
	);

	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('system/user/index');
		// 根据登录的用户ID获取该用户可以管理的角色集合和用户集合
		$userModel = $this->_loadModel('system/user');
		$user      = $userModel->get($_SESSION['user_id'], 'role_id');
		// 获取该用户的角色path
		$roleModel = $this->_loadModel('system/role');
		$role      = $roleModel->get($user['role_id'], 'path');
		if (empty($role)) {
			return false;
		}
		// 根据角色path获取可以管理的角色集合
		$option = array(
			'where' => array('path' => array('like', $role['path'] . '/', 'right')),
			'col'   => 'role_id, name'
		);
		$roleList = $roleModel->getPairs($option);
		if (empty($roleList)) {
			return false;
		}
		$roleList['0'] = '未分配';
		$this->_data['roleList'] = $roleList;
		// 根据角色集合获取可以管理的用户集合
		$where    = array('role_id' => array('in', array_keys($roleList)));
		$userList = $userModel->getCol(array('where' => $where));
		if (empty($roleList)) {
			return false;
		}
		$this->_data['userList'] = $userList;
	}

	public function indexAction()
	{
		$this->_view->assign('roleList', $this->_data['roleList']);
		$this->_view->render('system/user/index');
	}

	public function listAction()
	{
		$page   = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows   = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$option = array(
			'where' => array(),
			'order' => array(),
			'col'   => 'user_id, role_id, name, account, status,
						by_added, by_modified, date_added, date_modified'
		);
		//过滤
		if (isset($_POST['filter']) && count($_POST['filter']) > 0) {
			foreach ($_POST['filter'] as $key => $val) {
				switch ($key) {
					case 'role_id':
						if (strlen($val) > 0) {
							$option['where'][$key] = array('eq', $val);
						}
						break;
				}
			}
		}
		$userModel = $this->_loadModel('system/user');
		// 判断是否过滤
		if (empty($option['where'])) {
			$option['where'] = array(
				'role_id' => array('in', array_keys($this->_data['roleList']))
			);
		}
		$total = $userModel->getTotalList($option);
		$data  = $userModel->getList($page, $rows, $option);
		if (!empty($data)) {
			foreach ($data as $key => $val) {
				$data[$key]['role_name'] = $this->_data['roleList'][$val['role_id']];
			}
		}
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}

	public function getAction()
	{
		$user_id   = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
		$userModel = $this->_loadModel('system/user');
		$data = $userModel->get($user_id, 'account, name');
		$this->_ajaxReturn($data);
	}

	public function addAction()
	{
		$data['account']    = isset($_POST['account']) ? $_POST['account'] : '';
		$data['name']       = isset($_POST['name']) ? $_POST['name'] : '';
		$data['status']     = 1;
		$data['by_added']   = $_SESSION['user_account'];
		$data['date_added'] = now();
		$result = array('error' => false, 'msg' => array());
		$userModel = $this->_loadModel('system/user');
		if (strlen($data['account']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '用户账户不能为空';
		} elseif ($userModel->existAccount($data['account'])) {
			$result['error'] = true;
			$result['msg'][] = '用户账户已存在';
		}
		if (strlen($data['name']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '用户名称不能为空';
		}
		if (!$result['error']) {
			if ($userModel->add($data)) {
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
		$userId    = isset($_POST['user_id']) ? $_POST['user_id'] : 0;
		$result    = array('error' => false, 'msg' => array());
		$userModel = $this->_loadModel('system/user');
		$userArr   = $userModel->get($userId, 'account, name');
		if (empty($userArr)) {
			$result['error'] = true;
			$result['msg'][] = '删除失败';
			$this->_ajaxReturn($result);
		}
		if (!in_array($userId, $this->_data['userList'])) {
			$result['error'] = true;
			$result['msg'][] = '没有权限操作此用户';
		} else {
			if ($userModel->del($userId)) {
				$result['msg'][] = '删除成功';
				// 添加到系统日志
				$this->_addSystemLog(sprintf('[用户管理][删除用户][%s:%s]', $userArr['account'], $userArr['name']));
			} else {
				$result['error'] = true;
				$result['msg'][] = '删除失败';
			}
		}
		$this->_ajaxReturn($result);
	}

	public function updateAction()
	{
		$userId                = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
		$data['name']          = isset($_POST['name']) ? $_POST['name'] : '';
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result = array('error' => false, 'msg' => array());
		if (!in_array($userId, $this->_data['userList'])) {
			$result['error'] = true;
			$result['msg'][] = '没有权限操作此用户';
		} else {
			if (strlen($data['name']) < 1) {
				$result['error'] = true;
				$result['msg'][] = '用户名称不能为空';
			}
			if (!$result['error']) {
				$userModel = $this->_loadModel('system/user');
				if ($userModel->update($data, $userId)) {
					$result['msg'][] = '修改成功';

					// 添加到系统日志
					$this->_addSystemLog(sprintf('[用户管理][修改用户][%s]', $userId));
				} else {
					$result['error'] = true;
					$result['msg'][] = '修改失败';
				}
			}
		}
		return $this->_ajaxReturn($result);
	}

	public function getRoleAction()
	{
		$userId    = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
		$userModel = $this->_loadModel('system/user');
		$data = $userModel->get($userId, 'role_id');
		$this->_ajaxReturn($data);
	}

	public function updateRoleAction()
	{
		$userId                = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
		$data['role_id']       = isset($_POST['role_id']) ? $_POST['role_id'] : 0;
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result = array('error' => false, 'msg' => array());
		if (!in_array($userId, $this->_data['userList'])) {
			$result['error'] = true;
			$result['msg'][] = '没有权限操作此用户';
		} else {
			if (!isset($this->_data['roleList'][$data['role_id']])) {
				$result['error'] = true;
				$result['msg'][] = '没有权限分配此角色';
			}
			if (!$result['error']) {
				$userModel = $this->_loadModel('system/user');
				if ($userModel->update($data, $userId)) {
					$result['msg'][] = '角色分配成功';

					// 添加到系统日志
					$this->_addSystemLog(sprintf('[用户管理][%s][分配角色][%s]', $userId, $data['role_id']));
				} else {
					$result['error'] = true;
					$result['msg'][] = '角色分配失败';
				}
			}
		}
		return $this->_ajaxReturn($result);
	}

	public function resetPasswordAction()
	{
		$userId    = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
		$password  = isset($_POST['password']) ? $_POST['password'] : '';
		$rpassword = isset($_POST['rpassword']) ? $_POST['rpassword'] : '';
		$result    = array('error' => false, 'msg' => array());
		if (!in_array($userId, $this->_data['userList'])) {
			$result['error'] = true;
			$result['msg'][] = '没有权限操作此用户';
		} else {
			if (strlen($password) < 7) {
				$result['error'] = true;
				$result['msg'][] = '新密码不能少于7位';
			} elseif ($password != $rpassword) {
				$result['error'] = true;
				$result['msg'][] = '新密码不一致';
			}
			$userModel = $this->_loadModel('system/user');
			if ($userModel->contrastPassword($password, $userId)) {
				$result['error'] = true;
				$result['msg'][] = '新密码不能与最近四次的密码重复';
			}
			if (!$result['error']) {
				if ($userModel->resetPassword($password, $userId)) {
					$result['msg'][] = '重置密码成功';

					// 添加到系统日志
					$this->_addSystemLog(sprintf('[用户管理][重置密码][%s]', $userId));
				} else {
					$result['error'] = true;
					$result['msg'][] = '重置密码失败';
				}
			}
		}
		return $this->_ajaxReturn($result);
	}

	public function enableAction()
	{
		$userId                = isset($_POST['user_id'])?$_POST['user_id']:0;
		$data['status']        = '1';
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result    = array('error' => false, 'msg' => array());
		if (!in_array($userId, $this->_data['userList'])) {
			$result['error'] = true;
			$result['msg'][] = '没有权限操作此用户';
		}
		if (!$result['error']) {
			$userModel = $this->_loadModel('system/user');
			if ($userModel->update($data, $userId)) {
				$result['msg'][] = '启用成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '启用失败';
			}
		}
		$this->_ajaxReturn($result);
	}

	public function disableAction()
	{
		$userId                = isset($_POST['user_id'])?$_POST['user_id']:0;
		$data['status']        = '0';
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result    = array('error' => false, 'msg' => array());
		if (!in_array($userId, $this->_data['userList'])) {
			$result['error'] = true;
			$result['msg'][] = '没有权限操作此用户';
		}
		if (!$result['error']) {
			$userModel = $this->_loadModel('system/user');
			if ($userModel->update($data, $userId)) {
				$result['msg'][] = '停用成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '停用失败';
			}
		}
		$this->_ajaxReturn($result);
	}
}
