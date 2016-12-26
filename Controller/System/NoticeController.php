<?php
class System_NoticeController extends CustomController
{
	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('system/notice/index');
	}
	
	public function indexAction()
	{
		$this->_view->render('system/notice/index');
	}
	
	public function listAction()
	{
		$page   = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows   = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$option = array(
			'where' => array(),
			'order' => array('date_added' => 'DESC'),
			'col'   => 'notice_id, title, content, status, is_popup,
						by_added, by_modified, date_added, date_modified'
		);
		$noticeModel = $this->_loadModel('system/notice');
		$total = $noticeModel->getTotalList($option);
		$data  = $noticeModel->getList($page, $rows, $option);
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}
	
	public function addAction()
	{
		$data['title']      = isset($_POST['title']) ? trim($_POST['title']) : '';
		$data['content']    = isset($_POST['content']) ? $_POST['content'] : '';
		$data['by_added']   = $_SESSION['user_account'];
		$data['status']     = 1;
		$data['date_added'] = now();
		$result = array('error' => false, 'msg' => array());

		$noticeModel = $this->_loadModel('system/notice');
		if (strlen($data['title']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '公告标题不能为空';
		}
		if (strlen($data['content']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '公告内容不能为空';
		}
		if (!$result['error']) {
			if ($noticeModel->add($data)) {
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
		$noticeId = isset($_POST['notice_id']) ? $_POST['notice_id'] : 0;
		$result   = array('error' => false, 'msg' => array());
		$noticeModel = $this->_loadModel('system/notice');
		if ($noticeModel->del($noticeId)) {
			$result['msg'][] = '删除成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '删除失败';
		}
		$this->_ajaxReturn($result);
	}
	
	public function updateAction()
	{
		$noticeId 		       = isset($_GET['notice_id']) ? $_GET['notice_id'] : 0;
		$data['title']         = isset($_POST['title']) ? trim($_POST['title']) : '';
		$data['content']       = isset($_POST['content']) ? $_POST['content'] : '';
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result = array('error' => false, 'msg' => array());
		$noticeModel = $this->_loadModel('system/notice');
		if (strlen($data['title']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '公告标题不能为空';
		}
		if (strlen($data['content']) < 1) {
			$result['error'] = true;
			$result['msg'][] = '公告内容不能为空';
		}
		if (!$result['error']) {
			if ($noticeModel->update($data, $noticeId)) {
				$result['msg'][] = '修改成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '修改失败';
			}
		}
		return $this->_ajaxReturn($result);
	}

	public function contentAction()
	{
		$id   = isset($_GET['notice_id']) ? (int)$_GET['notice_id'] : 0;
		$data = array();
		if (!empty($id)) {
			$noticeModel = $this->_loadModel('system/notice');
			$data = $noticeModel->get($id, 'notice_id, title, content');
		}
		$this->_view->assign('data', $data);
		$this->_view->render('system/notice/content');
	}

	public function isStatusAction()
	{
		$result = array('error' => false, 'msg' => array());
		$id     = isset($_POST['id']) ? $_POST['id'] : 0;
		if (is_numeric($_POST['status']) && $_POST['status'] > 0) {
			$status = 1;
			$temStr = '启用';
		} else {
			$status = 0;
			$temStr = '禁用';
		}
		$data = array(
			'status'        => $status,
			'by_modified'   => $_SESSION['user_account'],
			'date_modified' => now()
		);
		$noticeModel = $this->_loadModel('system/notice');
		if ($noticeModel->update($data, $id)) {
			$result['msg'][] = $temStr . '成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = $temStr . '失败';
		}
		$this->_ajaxReturn($result);
	}
	
	public function isPopupAction()
	{
		$result = array('error' => false, 'msg' => array());
		$id     = isset($_POST['id']) ? $_POST['id'] : 0;
		$data   = array('is_popup' => '1');
		$noticeModel = $this->_loadModel('system/notice');
		if ($noticeModel->update($data, $id)) {
			$result['msg'][] = '顶置成功';
			$noticeModel->updatePopup($id);
		} else {
			$result['error'] = true;
			$result['msg'][] = '顶置失败';
		}
		$this->_ajaxReturn($result);
	}
	
	public function noPopupAction()
	{
		$result = array('error' => false, 'msg' => array());
		$id     = isset($_POST['id']) ? $_POST['id'] : 0;
		$data   = array('is_popup' => 0);
		$noticeModel = $this->_loadModel('system/notice');
		if ($noticeModel->update($data, $id)) {
			$result['msg'][] = '取消成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '取消失败';
		}
		$this->_ajaxReturn($result);
	}
}
