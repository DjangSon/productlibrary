<?php
class System_LoginController extends CustomController
{
	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('system/login/index');
	}
	
	public function indexAction()
	{
		$this->_view->render('system/login/index');
	}
	
	public function listAction()
	{
		$page   = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows   = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$option = array(
			'where' => array(),
			'order' => array('login_id' => 'DESC'),
			'col'   => '*'
		);
		//过滤
		if (isset($_POST['filter']) && count($_POST['filter']) > 0) {
			foreach ($_POST['filter'] as $key => $val) {
				switch ($key) {
					case 'account':
						if (strlen($val) > 0) {
							$option['where'][$key] = array('eq', $val);
						}
					break;
					case 'date_added':
						if (v_date($val['start']) && v_date($val['end'])) {
							$option['where'][$key . '&' . $key] = array(
								array('egt', $val['start'] . ' 00:00:00'),
								array('elt', $val['end'] . ' 23:59:59')
							);
						} elseif (v_date($val['start'])) {
							$option['where'][$key] = array('egt', $val['start'] . ' 00:00:00');
						} elseif (v_date($val['end'])) {
							$option['where'][$key] = array('elt', $val['end'] . ' 23:59:59');
						}
					break;
				}
			}
		}
		$loginModel = $this->_loadModel('system/login');
		$total = $loginModel->getTotalList($option);
		$data  = $loginModel->getListByLarge($page, $rows, $option);
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}
}
