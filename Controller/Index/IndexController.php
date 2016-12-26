<?php
class Index_IndexController extends CustomController
{
	//验证登录
	protected function _validateLogin()
	{
		//验证登录
		$userModel = $this->_loadModel('system/user');
		if (!isset($_SESSION['user_id'])
			|| !$userModel->validateUser($_SESSION['user_id'])) {
			session_destroy();
			$this->_redirectUrl($this->getUrl('index/index/login'));
		}
		//验证密码强制修改
		if ($userModel->forcePassword($_SESSION['user_id'])) {
			$this->_redirectUrl($this->getUrl('index/index/forcePassword'));
		}
	}

	//首页内容
	public function homeAction()
	{
		$this->_validateLogin();
		// model
		$noticeModel = $this->_loadModel('system/notice');
		// 获取弹出的文章id
		$noticeId = (int) $noticeModel->getPopup();
		$this->_view->assign('noticeId', $noticeId);
		$this->_view->render('index/index/home');
	}

	//系统公告列表
	public function noticeListAction()
	{
		$this->_validateLogin();
		$page   = isset($_POST['page'])?$_POST['page']:1;
		$rows   = isset($_POST['rows'])?$_POST['rows']:10;
		$option = array(
			'where' => array('status' => array('eq', '1')),
			'order' => array('date_added' => 'DESC'),
			'col'   => 'notice_id, title, date_added'
		);
		$noticeModel = $this->_loadModel('system/notice');
		$total = $noticeModel->getTotalList($option);
		$data = $noticeModel->getList($page, $rows, $option);
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}

	//系统公告详情
	public function showAction()
	{
		$this->_validateLogin();
		$notice_id   = isset($_GET['notice_id'])?$_GET['notice_id']:0;
		$noticeModel = $this->_loadModel('system/notice');
		$notice      = $noticeModel->get($notice_id, 'title, date_added, content');
		$this->_view->assign('notice', $notice);
		$this->_view->render('index/index/show');
	}

	//登录列表
	public function loginListAction()
	{
		$this->_validateLogin();
		$page   = isset($_POST['page'])?$_POST['page']:1;
		$rows   = isset($_POST['rows'])?$_POST['rows']:10;
		$option = array(
			'where' => array('account' => array('eq', $_SESSION['user_account'])),
			'order' => array('date_added' => 'DESC'),
			'col'   => 'account, ip, status, date_added'
		);
		$loginModel = $this->_loadModel('system/login');
		$total = $loginModel->getTotalList($option);
		$data = $loginModel->getList($page, $rows, $option);
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}

	//首页
	public function indexAction()
	{
		$this->_validateLogin();
		$option = array(
			'where' => array(),
			'order' => array('sort' => 'ASC'),
			'col'   => 'rightgroup_id, name, icon'
		);
		$rightGroupModel = $this->_loadModel('system/rightgroup');
		$rightModel      = $this->_loadModel('system/right');
		$rightGroupList  = $rightGroupModel->getAllList($option);
		$rightList       = $rightModel->getMenu($_SESSION['user_id']);
		foreach ($rightGroupList as $key => $rightGroup) {
			$rightGroupList[$key]['right'] = array();
			foreach ($rightList as $right) {
				if ($right['rightgroup_id'] == $rightGroup['rightgroup_id']) {
					$rightGroupList[$key]['right'][] = $right;
				}
			}
		}
		$this->_view->assign('rightGroupList', $rightGroupList);
		$this->_view->render('index/index/index');
	}

	//强制修改密码
	public function forcePasswordAction()
	{
		//验证登录
		$userModel = $this->_loadModel('system/user');
		if (!isset($_SESSION['user_id'])
			|| !$userModel->validateUser($_SESSION['user_id'])) {
			session_destroy();
			$this->_redirectUrl($this->getUrl('index/index/login'));
		}
		if (!$userModel->forcePassword($_SESSION['user_id'])) {
			$this->_redirectUrl($this->getUrl('index/index/index'));
		}
		$this->_view->render('index/index/forcePassword');
	}

	//修改密码
	public function changePasswordAction()
	{
		//验证登录
		$userModel = $this->_loadModel('system/user');
		if (!isset($_SESSION['user_id'])
			|| !$userModel->validateUser($_SESSION['user_id'])) {
			session_destroy();
			$this->_redirectUrl($this->getUrl('index/index/login'));
		}
		$opassword = isset($_POST['opassword'])?$_POST['opassword']:'';
		$password  = isset($_POST['password'])?$_POST['password']:'';
		$rpassword = isset($_POST['rpassword'])?$_POST['rpassword']:'';
		$result = array('error' => false, 'msg' => array());
		if (strlen($opassword) < 7) {
			$result['error'] = true;
			$result['msg'][] = '密码不能少于7位';
		}
		if (strlen($password) < 7) {
			$result['error'] = true;
			$result['msg'][] = '新密码不能少于7位';
		} elseif ($password != $rpassword) {
			$result['error'] = true;
			$result['msg'][] = '新密码不一致';
		}
		if ($userModel->contrastPassword($password, $_SESSION['user_id'])) {
			$result['error'] = true;
			$result['msg'][] = '新密码不能与最近四次的密码重复';
		}
		if (!$result['error']) {
			if (!$userModel->validatePassword($opassword, $_SESSION['user_id'])) {
				$result['error'] = true;
				$result['msg'][] = '密码错误';
			} elseif ($userModel->changePassword($password, $_SESSION['user_id'])) {
				$result['msg'][] = '修改密码成功';
			} else {
				$result['error'] = true;
				$result['msg'][] = '修改密码失败';
			}
		}
		return $this->_ajaxReturn($result);
	}

	//登录页面
	public function loginAction()
	{
		$userModel = $this->_loadModel('system/user');
		if (isset($_SESSION['user_id'])
			&& $userModel->validateUser($_SESSION['user_id'])) {
			$this->_redirectUrl($this->getUrl('index/index/index'));
		}
		$account  = isset($_COOKIE['account'])?$_COOKIE['account']:'';
		$password = isset($_COOKIE['password'])?$_COOKIE['password']:'';
		$save     = isset($_COOKIE['save'])?$_COOKIE['save']:'';
		$this->_view->assign('account', $account);
		$this->_view->assign('password', $password);
		$this->_view->assign('save', $save);
		$this->_view->render('index/index/login');
	}

	//验证登录
	public function loginPostAction()
	{
		$ip = $this->getIp();
		$allowLogin = $this->_allowLogin($ip);
		$result = array('error' => false, 'msg' => array());
		if ($allowLogin['status']) {
			$userModel = $this->_loadModel('system/user');
			if (isset($_SESSION['user_id'])
				&& $userModel->validateUser($_SESSION['user_id'])) {
				return $this->_ajaxReturn($result);
			}
			$account  = isset($_POST['account'])?$_POST['account']:'';
			$password = isset($_POST['password'])?$_POST['password']:'';
			$captcha  = isset($_POST['captcha'])?$_POST['captcha']:'';
			$save     = isset($_POST['save'])?$_POST['save']:'';
			if (strlen($account) < 1) {
				$result['error'] = true;
				$result['msg'][] = '用户名不能为空';
			}
			if (strlen($password) < 1) {
				$result['error'] = true;
				$result['msg'][] = '密码不能为空';
			}
			if (!isset($_SESSION['captchacode'])
				|| strtolower($captcha) != $_SESSION['captchacode']) {
				$result['error'] = true;
				$result['msg'][] = '验证码错误';
			}
			if (!$result['error']) {
				if ($save == 'on') {
					setcookie('account', $account, time()+3600*24*7);
					setcookie('password', $password, time()+3600*24*7);
					setcookie('save', $save, time()+3600*24*7);
				} else {
					setcookie('account', NULL);
					setcookie('password', NULL);
					setcookie('save', NULL);
				}
				//登录日志
				$loginData = array(
					'account'    => $account,
					'ip'         => $ip,
					'date_added' => now()
				);
				$loginModel = $this->_loadModel('system/login');
				if ($user = $userModel->validateLogin($account, $password)) {
					session_regenerate_id();
					$_SESSION['user_id']      = $user['user_id'];
					$_SESSION['user_account'] = $user['account'];
					$_SESSION['user_name']    = $user['name'];
					$loginData['status'] = '1';
					$loginModel->addLogin($loginData);
				} else {
					$loginData['status'] = '0';
					$loginModel->addLogin($loginData);
					$result['error'] = true;
					$result['msg'][] = '用户名或密码错误，登录失败。('. ($allowLogin['num'] + 1) .'次)';
				}
			}
		} else {
			$result['error'] = true;
			$result['msg'][] = '登录出错已有' . $allowLogin['num'] . '次。当前禁止登录，下次允许登录时间:' . date('Y-m-d H:i:s', $allowLogin['time']);
		}
		return $this->_ajaxReturn($result);
	}

	//登出处理
	public function logoutAction()
	{
		session_destroy();
		setcookie(session_name(), '', time() - 3600, '/');
		$_SESSION = array();
		$this->_redirectUrl($this->getUrl('index/index/login'));
	}

	//验证码页面
	public function captchaAction()
	{
		//初始化
		$border = 1;
		$how = 4;
		$w = 117;
		$h = 42;
		$y = 29;
		$fontsize = 18;
		$alpha = "abcdefghjkmnpqrstuvwxyz";
		$number = "23456789";
		$captchacode = "";
		srand((double)microtime()*1000000);
		$font = VAR_PATH . "font/arialbd.ttf";

		$img = imagecreate($w, $h);
		//绘制基本框架
		$bgcolor = imagecolorallocate($img, 247, 255, 236);
		imagefill($img, 0, 0, $bgcolor);
		if ($border) {
			$black = imagecolorallocate($img, 204, 204, 204);
			imagerectangle($img, 0, 0, $w-1, $h-1, $black);
		}

		//逐位产生随机字符
		for ($i=0; $i<$how; $i++) {
			$alpha_or_number = mt_rand(0, 1);
			$str = $alpha_or_number ? $alpha : $number;
			$which = mt_rand(0, strlen($str)-1);
			$code = substr($str, $which, 1);
			$j = !$i ? 15 : $j+25;
			$color3 = imagecolorallocate($img, 87, 123, 35);
			imagettftext($img, $fontsize, 0, $j, $y, $color3, $font, $code);
			//imagechar($img, $fontsize, $j, $y, $code, $color3);
			$captchacode .= $code;
		}

		//绘背景干扰线
		/*
		for ($i=0; $i<10; $i++) {
			$color1 = imagecolorallocate($img, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
			imagearc($img, mt_rand(-5,$w), mt_rand(-5,$h), mt_rand(20,300), mt_rand(20,200), 55, 44, $color1);
		}
		*/

		$_SESSION['captchacode'] = $captchacode;
		header("Content-type: image/gif");
		imagegif ($img);
		imagedestroy($img);
		exit;
	}

	private function _allowLogin($ip)
	{
		$option = array(
			'where' => array(
				'ip'     => array('eq', $ip),
				'status' => array('eq', '0')
			),
			'order' => array('date_added' => 'DESC'),
			'col'   => 'date_added'
		);
		$loginModel = $this->_loadModel('system/login');
		$list = $loginModel->getAllList($option);
		$num  = count($list);
		$n    = ceil($num / 5);
		if ($num >= 5 && $num % 5 == 0) {
			$time = strtotime($list[0]['date_added']) + pow($n, 3) * 60;
			if (time() < $time)
				return array('status' => false, 'time' => $time, 'num' => $num);
		}
		return array('status' => true, 'num' => $num);
	}
}
