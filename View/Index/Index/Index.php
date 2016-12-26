<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title><?php echo PROJECT_NAME; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="icon" href="<?php echo APP_IMAGES_URL; ?>favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="<?php echo APP_IMAGES_URL; ?>favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>init.js"></script>
</head>
<body class="easyui-layout">
<div data-options="region:'north',border:false">
	<div id="header">
		<div class="logo f-left">
			<a href="<?php echo $this->getUrl('index/index/index'); ?>"><?php echo PROJECT_NAME; ?>管理后台</a>
		</div>
		<ul class="links f-right">
			<li>您好, <?php echo $_SESSION['user_account'] ; ?> (<?php echo $_SESSION['user_name'] ; ?>)</li>
			<li><a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#password-dlg').dialog('open');" data-options="plain:true,iconCls:'icon-reload'">修改密码</a> <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$.messager.confirm('系统提示','您确定要退出本次登录吗?',function(r){if(r){location.href='<?php echo $this->getUrl('index/index/logout'); ?>';}});" data-options="plain:true,iconCls:'icon-back'">安全退出</a></li>
		</ul>
	</div>
</div>
<div id="footer" data-options="region:'south',border:false">
	<address>&copy; 2014-<?php echo date('Y') + 1; ?> 图远网络科技有限公司 版权所有</address>
</div>
<div id="col-left" data-options="region:'west',title:'导航菜单'">
	<div class="easyui-accordion" data-options="fit:true,border:false">
		<?php foreach ($rightGroupList as $rightGroup) { ?>
		<?php if (count($rightGroup['right']) > 0) { ?>
		<div data-options="title:'<?php echo $rightGroup['name']; ?>',iconCls:'<?php echo $rightGroup['icon']; ?>'">
			<ul>
				<?php foreach ($rightGroup['right'] as $right) { ?>
				<li><div><a class="<?php echo $right['icon']; ?>" rel="<?php echo $this->getUrl($right['url']); ?>" href="javascript:void(0)"><?php echo $right['name']; ?></a></div></li>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>
		<?php } ?>
	</div>
</div>
<div id="col-main" data-options="region:'center'">
	<div class="easyui-tabs" id="tabs" data-options="fit:true,border:false">
		<div id="home" data-options="title:'后台首页',iconCls:'icon-home'">
			<iframe scrolling="auto" frameborder="0" src="<?php echo $this->getUrl('index/index/home'); ?>" style="width:100%;height:99.5%;"></iframe>
		</div>
	</div>
</div>
<div class="easyui-menu" id="rightMenu">
	<div id="rm-tabclose">关闭</div>
	<div id="rm-tabcloseall">全部关闭</div>
	<div id="rm-tabcloseother">除此之外全部关闭</div>
	<div class="menu-sep"></div>
	<div id="rm-tabcloseright">当前页右侧全部关闭</div>
	<div id="rm-tabcloseleft">当前页左侧全部关闭</div>
	<div class="menu-sep"></div>
	<div id="rm-exit">退出</div>
</div>
<div id="password-dlg">
<form id="password-fm" method="post">
<ul class="form-list">
	<li class="fields">
		<div class="field">
			<label for="opassword">密码:<em>*</em></label>
			<div class="input-box">
				<input type="password" class="input-text easyui-validatebox" name="opassword" id="opassword" data-options="required:true,validType:'length[7,16]'" maxlength="16" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="password">新密码:<em>*</em></label>
			<div class="input-box">
				<input type="password" class="input-text easyui-validatebox" name="password" id="password" data-options="required:true,validType:'length[7,16]'" maxlength="16" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="rpassword">确认密码:<em>*</em></label>
			<div class="input-box">
				<input type="password" class="input-text easyui-validatebox" name="rpassword" id="rpassword" data-options="required:true,validType:'equals[\'#password\']'" maxlength="16" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label><a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true,iconCls:'icon-reload'" id="generate-password">生成密码</a></label>
			<div class="input-box">
				<input type="text" class="input-text" name="gpassword" id="user-gpassword" maxlength="16" />
			</div>
		</div>
	</li>
</ul>
</form>
</div>
<script type="text/javascript">
$(function(){
	$('#password-dlg').dialog({
		title:'修改密码',
		width:315,
		closed:true,
		modal:true,
		top:158,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认修改密码吗？',function(r){
						if(r){
							$('#password-fm').form('enableValidation');
							$('#password-fm').form('submit',{
								url:'<?php echo $this->getUrl('index/index/changePassword'); ?>',
								onSubmit:function(){
									return $(this).form('validate');
								},
								success:function(result){
									result=$.parseJSON(result);
									if(result.error){
										$.messager.show({title:'失败',msg:result.msg.join("<br>")});
									}else{
										$.messager.show({title:'成功',msg:result.msg.join("<br>")});
										$('#password-dlg').dialog('close');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#password-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#password-fm').form('disableValidation');
			$('#password-fm').form('reset');
		}
	});

	$('#generate-password').bind('click',function(){
		var password='';
		for(i=0;i<16;i++){
			password+="abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ~@#$%^&*(){}[]|".charAt(Math.floor(Math.random()*77));
		}
		$("input[name='gpassword']").val(password);
		$("input[name='password']").val(password);
		$("input[name='rpassword']").val(password);
    });
});
</script>
</body>
</html>