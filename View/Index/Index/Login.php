<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title><?php echo PROJECT_NAME; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="icon" href="<?php echo APP_IMAGES_URL; ?>favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="<?php echo APP_IMAGES_URL; ?>favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>login.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>validate/css/validate.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>validate/validate.min-1.11.0.js"></script>
	<script type="text/javascript">if(top.location!==self.location){top.location=self.location;}</script>
</head>
<body>
<div class="wrapper">
	<div class="main-box">
		<div class="box-header">
			<div class="logo f-left"><?php echo PROJECT_NAME; ?></div>
			<div class="title f-left">管理后台</div>
		</div>
		<div class="box-content">
			<div class="messages"></div>
			<form id="loginFm" method="post">
				<ul class="form-list">
					<li class="fields">
						<div class="field">
							<label>用户名</label>
							<div class="input-box">
								<input type="text" class="input-text required" value="<?php echo $account; ?>" name="account" />
							</div>
						</div>
					</li>
					<li class="fields">
						<div class="field">
							<label>密&nbsp;&nbsp;&nbsp;码</label>
							<div class="input-box">
								<input type="password" class="input-text required" value="<?php echo $password; ?>" name="password" />
							</div>
						</div>
					</li>
					<li class="fields">
						<div class="field">
							<label>验证码</label>
							<div class="input-box">
								<input type="text" class="input-text required" name="captcha" style="width:197px;" />
								<img src="<?php echo $this->getUrl('index/index/captcha'); ?>" onclick="this.src=this.src+'?';" title="点击刷新验证码"  style="cursor:pointer;" />
							</div>
						</div>
					</li>
					<li class="fields">
						<div class="field">
							<label>&nbsp;</label>
							<div class="input-box">
								<input type="checkbox" class="checkbox" name="save" id="login-save"<?php if ($save == 'on') {?> checked="checked"<?php } ?> /><label for="login-save">保存登录信息</label>
							</div>
						</div>
					</li>
					<li class="buttons-set">
						<a class="button" href="javascript:void(0)" id="loginBtn" onclick="$('#loginFm').submit();">登录</a>
					</li>
				</ul>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
$(function(){
	$('#loginFm').validate({
		errorPlacement:function(error,element){},
		submitHandler:function(form){
			if ($('#loginBtn').html()=='登录') {
				$('#loginBtn').html('正在登录');
				$.post(
					'<?php echo $this->getUrl('index/index/loginPost'); ?>',
					$('#loginFm').serialize(),
					function(result){
						if(result.error){
							$('.messages').html('<p class="error-msg">'+result.msg.join('<br>')+'</p>');
							$('#loginBtn').html('登录');
							$('#loginFm img').click();
						}else{
							$('.messages').html('<p class="success-msg">登录成功，请稍候...</p>');
							$('#loginBtn').html('登录成功');
							top.location.replace(window.location.href);
						}
					},
					'json'
				);
			}
		}
	});

	$('#loginFm input').keydown(function(e){
		if(e.keyCode==13){
			if ($('#loginBtn').html()=='登录') {
				$('#loginFm').submit();
			}
		}
	});
});
</script>
</body>
</html>