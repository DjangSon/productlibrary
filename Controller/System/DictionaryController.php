<?php
class System_DictionaryController extends CustomController
{
	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('system/dictionary/index');
	}
	
	public function indexAction()
	{
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$typeList = $dictionaryModel->getTypeList();
		$this->_view->assign('typeList', $typeList);
		$this->_view->render('system/dictionary/index');
	}
	
	public function listAction()
	{
		$pageStr   = isset($_POST['page']) ? $_POST['page'] : 1;
		$rowsStr   = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$optionArr = array(
			'where' => array(),
			'order' => array('type' => 'ASC' , 'sort' => 'ASC')
		);
		//过滤
		if (isset($_POST['filter']) && count($_POST['filter']) > 0) {
			foreach ($_POST['filter'] as $key => $val) {
				switch ($key) {
					case 'name':
						if (strlen($val) > 0) {
							$optionArr['where'][$key] = array('like', $val);
						}
						break;
					case 'type':
					case 'status':
						if (strlen($val) > 0) {
							$optionArr['where'][$key] = array('eq', $val);
						}
						break;
				}
			}
		}
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$totalStr = $dictionaryModel->getTotalList($optionArr);
		$dataList = $dictionaryModel->getList($pageStr, $rowsStr, $optionArr);
		$this->_ajaxReturn(array('total' => $totalStr, 'rows' => $dataList));
	}
	
	public function getAction()
	{
		$dictionaryId   = isset($_GET['dictionary_id']) ? $_GET['dictionary_id'] : 0;
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$dataList = $dictionaryModel->get($dictionaryId, 'name, type, sort, status');
		$this->_ajaxReturn($dataList);
	}
	
	public function addAction()
	{
		$dataArr['type']       = isset($_POST['type']) ? trim($_POST['type']) : '';
		$dataArr['name']       = isset($_POST['name']) ? trim($_POST['name']) : '';
		$dataArr['sort']       = isset($_POST['sort']) ? $_POST['sort'] : 0;
		$dataArr['status']     = isset($_POST['status']) ? $_POST['status'] : 0;
		$dataArr['by_added']   = $_SESSION['user_account'];
		$dataArr['date_added'] = now();
		$resultArr = array('error' => false, 'msg' => array());
		
		$whereArr['type'] = array('eq', $dataArr['type']);
		$whereArr['name'] = array('eq', $dataArr['name']);
		$dictionaryModel = $this->_loadModel('system/dictionary');
		if (strlen($dataArr['name']) < 1) {
			$resultArr['error'] = true;
			$resultArr['msg'][] = '字典名称不能为空';
		} elseif ($dictionaryModel->getTotalList(array('where' => $whereArr))) {
			$resultArr['error'] = true;
			$resultArr['msg'][] = '字典名称已存在';
		} 
		if (strlen($dataArr['type']) < 1) {
			$resultArr['error'] = true;
			$resultArr['msg'][] = '分组名称不能为空';
		}
		if (!$resultArr['error']) {
			if ($dictionaryModel->add($dataArr)) {
				$resultArr['msg'][] = '添加成功';
			} else {
				$resultArr['error'] = true;
				$resultArr['msg'][] = '添加失败';
			}
		}
		return $this->_ajaxReturn($resultArr);
	}
	
	public function updateAction()
	{
		$dictionaryId 		      = isset($_GET['dictionary_id']) ? $_GET['dictionary_id'] : 0;
		$dataArr['type']          = isset($_POST['type']) ? trim($_POST['type']) : '';
		$dataArr['name']          = isset($_POST['name']) ? trim($_POST['name']) : '';
		$dataArr['sort']          = isset($_POST['sort']) ? $_POST['sort'] : 0;
		$dataArr['status']        = isset($_POST['status']) ? $_POST['status'] : 0;
		$dataArr['by_modified']   = $_SESSION['user_account'];
		$dataArr['date_modified'] = now();
		$resultArr = array('error' => false, 'msg' => array());
		$dictionaryModel = $this->_loadModel('system/dictionary');
		if (strlen($dataArr['name']) < 1) {
			$resultArr['error'] = true;
			$resultArr['msg'][] = '字典名称不能为空';
		} elseif ($dictionaryModel->existName($dataArr['name'], $dictionaryId)) {
			$resultArr['error'] = true;
			$resultArr['msg'][] = '字典名称已存在';
		}
		if (strlen($dataArr['type']) < 1) {
			$resultArr['error'] = true;
			$resultArr['msg'][] = '分组名称不能为空';
		}
		if (!$resultArr['error']) {
			if ($dictionaryModel->update($dataArr, $dictionaryId)) {
				$resultArr['msg'][] = '修改成功';
			} else {
				$resultArr['error'] = true;
				$resultArr['msg'][] = '修改失败';
			}
		}
		return $this->_ajaxReturn($resultArr);
	}
	
	/**
	 * 启用
	 */
	public function enabledAction()
	{
		$ids = isset($_POST['ids']) ? $_POST['ids'] : 0;
		$this->_updateStatus(1, '启用', $ids);
	}
	
	/**
	 * 停用
	 */
	public function disableAction()
	{
		$ids = isset($_POST['ids']) ? $_POST['ids'] : 0;
		$this->_updateStatus(0, '停用', $ids);
	}
	
	/**
	 * 更新状态
	 * @param integer $status
	 * @param string  $name
	 * @param array   $ids
	 */
	private function _updateStatus($status, $name, $ids)
	{
		$resultArr = array('error' => false, 'msg' => array());
		$dictionaryModel = $this->_loadModel('system/dictionary');
		
		$whereArr = array(
			'dictionary_id' => array('in', $ids)
		);
		$dataArr = array(
			'status'        => $status,
			'by_modified'   => $_SESSION['user_account'],
			'date_modified' => now()
		);
		if ($dictionaryModel->updateByWhere($dataArr, $whereArr)) {
			$resultArr['msg'][] = $name . '成功';
		} else {
			$resultArr['error'] = true;
			$resultArr['msg'][] = $name . '失败';
		}
		return $this->_ajaxReturn($resultArr);
	}
}
