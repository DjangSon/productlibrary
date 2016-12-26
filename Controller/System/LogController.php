<?php
class System_LogController extends CustomController
{
	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('system/log/index');
	}
	
	public function indexAction()
	{
		$this->_view->render('system/log/index');
	}
	
	public function listAction()
	{
		$pageStr   = isset($_POST['page'])?$_POST['page'] : 1;
		$rowsStr   = isset($_POST['rows'])?$_POST['rows'] : 25;
		$optionArr = array(
			'where' => array(),
			'order' => array('log_id' => 'DESC'),
			'col'   => '*'
		);
		//过滤
		if (isset($_POST['filter']) && count($_POST['filter']) > 0) {
			foreach ($_POST['filter'] as $key => $val) {
				switch ($key) {
					case 'contents':
					case 'ip':
						if (!empty($val)) {
							$optionArr['where'][$key] = array('like', $val);
						}
					break;
					case 'date':
						if (v_date($val['start']) && v_date($val['end'])) {
							$optionArr['where']['date_added&date_added'] = array(
								array('egt', $val['start'] . ' 00:00:00'),
								array('elt', $val['end'] . ' 23:59:59')
							);
						} elseif (v_date($val['start'])) {
							$optionArr['where']['date_added'] = array('egt', $val['start'] . ' 00:00:00');
						} elseif (v_date($val['end'])) {
							$optionArr['where']['date_added'] = array('elt', $val['end'] . ' 23:59:59');
						}
					break;
				}
			}
		}
		$logModel = $this->_loadModel('system/log');
		$totalStr = $logModel->getTotalList($optionArr);
		$dataList = $logModel->getList($pageStr, $rowsStr, $optionArr);
		if (!empty($dataList)) {
			$userIds = array();
			foreach ($dataList as $val) {
				$userIds[] = $val['user_id'];
			}

			// 获取用户的账号和名称
			$userModel = $this->_loadModel('system/user');
			$whereArr  = array('user_id' => array('in', $userIds));
			$usersList = $userModel->getPairs2(array('where' => $whereArr, 'col' => 'user_id, account, name'));

			foreach ($dataList as $key => $val) {
				if (isset($usersList[$val['user_id']])) {
					$user = $usersList[$val['user_id']];
					$dataList[$key]['account'] = $user['account'];
					$dataList[$key]['name']    = $user['name'];
				}
			}
		}
		$this->_ajaxReturn(array('total' => $totalStr, 'rows' => $dataList));
	}
}
