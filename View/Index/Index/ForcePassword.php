<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>修改密码</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
</head>
<body>
<div id="password-dlg">
<form id="password-fm" method="post">
<ul class="form-list">
	<li class="control">
		<p>1.首次登录时需要修改密码</p>
		<p>2.重置密码</p>
		<p>3.密码使用超过90天需要修改密码</p>
	</li>
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
				<input type="text" class="input-text" name="gpassword" id="user-gpassword" maxlength="16" readonly="readonly" />
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
										window.location.replace(window.location.href);
									}
								}
							});
						}
					});
				}
			},
			{text:'重置',iconCls:'icon-reload',
				handler:function(){
					$('#password-fm').form('reset');
				}
			}
		],
		onBeforeOpen:function(){
			$('#password-fm').form('disableValidation');
			$('#password-fm').form('reset');
		}
	});

	$('#password-dlg').dialog('open');
	
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