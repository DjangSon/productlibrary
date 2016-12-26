<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>用户管理</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
	<script type="text/javascript">
		$.extend($.fn.validatebox.defaults.rules,{
			equals:{validator:function(value,param){return value==$(param[0]).val();},message:'密码不一致。'}
		});
	</script>
</head>
<body>
<table id="user-dg"></table>
<ul class="datagrid-filter">
	<li class="fields last">
		<div class="field">
			<label for="filter-role">角色:</label>
			<div class="input-box">
				<select id="filter-role">
					<option value="">全部</option>
					<?php foreach ($roleList as $key => $val) { ?>
						<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</li>
</ul>
<div id="user-dlg">
	<form id="user-fm" method="post">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="user-name">姓名:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="name" id="user-name" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="user-account">帐号:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="account" id="user-account" data-options="required:true" />
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<div id="role-dlg">
	<form id="role-fm" method="post">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="role_id">角色:<em>*</em></label>
					<div class="input-box">
						<select name="role_id" id="role_id">
							<option value="0">请选择</option>
							<?php foreach ($roleList as $key => $val) { ?>
								<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<div id="password-dlg">
	<form id="password-fm" method="post">
		<ul class="form-list">
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
					<label><a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true,iconCls:'icon-reload'" id="gpassword">生成密码</a></label>
					<div class="input-box">
						<input type="text" class="input-text" name="gpassword" id="gpassword" maxlength="16" />
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<script type="text/javascript">
	var url;
	$(function(){
		$('#user-dg').datagrid({
			title:'用户管理',
			fit:true,
			border:false,
			rownumbers:true,
			singleSelect:true,
			striped:true,
			pagination:true,
			pageSize:25,
			pageList:[25,50,100],
			url:'<?php echo $this->getUrl('system/user/list'); ?>',
			idField:'user_id',
			columns:[[
				{field:'user_id',hidden:true},
				{title:'用户名称',field:'name',align:'left'},
				{title:'用户帐号',field:'account',align:'left'},
				{title:'角色名称',field:'role_name',align:'left'},
				{title:'状态',field:'status',align:'center',
					formatter:function(val){if(val==0){return '<font color="red">停用</font>';}else if(val==1){return '<font color="green">启用</font>';}}
				},
				{title:'添加人',field:'by_added',align:'center'},
				{title:'添加时间',field:'date_added',align:'center'},
				{title:'修改人',field:'by_modified',align:'center'},
				{title:'修改时间',field:'date_modified',align:'center'}
			]],
			toolbar:[
				{text:'查询',iconCls:'icon-search',
					handler:function(){
						filter();
					}
				},
				'-',
				{text:'添加用户',iconCls:'icon-add',
					handler:function(){
						$('#user-account').attr({readonly:false});
						$('#user-dlg').dialog('open').dialog('setTitle','添加用户');
						url='<?php echo $this->getUrl('system/user/add'); ?>';
					}
				},
				'-',
				{text:'修改用户',iconCls:'icon-edit',
					handler:function(){
						var row=$('#user-dg').datagrid('getSelected');
						if(row==null){
							$.messager.alert('提示','请选择数据','info');
						}else{
							$('#user-account').attr({readonly:true});
							$('#user-dlg').dialog('open').dialog('setTitle','修改用户');
							$('#user-fm').form('load','<?php echo $this->getUrl('system/user/get', true); ?>user_id='+row.user_id);
							url='<?php echo $this->getUrl('system/user/update', true); ?>user_id='+row.user_id;
						}
					}
				},
				'-',
				{text:'删除用户',iconCls:'icon-remove',plain:true,
					handler:function(){
						var row=$('#user-dg').datagrid('getSelected');
						if(row==null){
							$.messager.alert('提示','请选择数据','info');
						}else{
							$.messager.confirm('确认','您确认想要删除用户<font color="red">'+row.name+'</font>吗？',function(r){
								if(r){
									$.post(
										'<?php echo $this->getUrl('system/user/del'); ?>',
										{user_id:row.user_id},
										function(result){
											if(result.error){
												$.messager.show({title:'失败',msg:result.msg.join("<br>")});
											}else{
												$.messager.show({title:'成功',msg:result.msg.join("<br>")});
												$('#user-dg').datagrid('clearSelections');
												$('#user-dg').datagrid('reload');
											}
										},
										'json'
									);
								}
							});
						}
					}
				},
				'-',
				{text:'角色分配',iconCls:'icon-add',
					handler:function(){
						var row=$('#user-dg').datagrid('getSelected');
						if(row==null){
							$.messager.alert('提示','请选择数据','info');
						}else{
							$('#role-dlg').dialog('open').dialog('setTitle','角色分配 用户名:'+row.account);
							$('#role-fm').form('load','<?php echo $this->getUrl('system/user/getRole', true); ?>user_id='+row.user_id);
							url='<?php echo $this->getUrl('system/user/updateRole', true); ?>user_id='+row.user_id;
						}
					}
				},
				'-',
				{text:'重置密码',iconCls:'icon-reload',
					handler:function(){
						var row=$('#user-dg').datagrid('getSelected');
						if(row==null){
							$.messager.alert('提示','请选择数据','info');
						}else{
							$('#password-dlg').dialog('open').dialog('setTitle','重置密码 用户名:'+row.account);
							url='<?php echo $this->getUrl('system/user/resetPassword', true); ?>user_id='+row.user_id;
						}
					}
				},
				'-',
				{text:'启用用户',iconCls:'icon-ok',
					handler:function(){
						var row=$('#user-dg').datagrid('getSelected');
						if(row==null){
							$.messager.alert('提示','请选择数据','info');
						}else{
							$.messager.confirm('确认','您确认想要启用用户<font color="red">'+row.name+'</font>吗？',function(r){
								if(r){
									$.post(
										'<?php echo $this->getUrl('system/user/enable'); ?>',
										{user_id:row.user_id},
										function(result){
											if(result.error){
												$.messager.show({title:'失败',msg:result.msg.join("<br>")});
											}else{
												$.messager.show({title:'成功',msg:result.msg.join("<br>")});
												$('#user-dg').datagrid('reload');
											}
										},
										'json'
									);
								}
							});
						}
					}
				},
				'-',
				{text:'停用用户',iconCls:'icon-cancel',
					handler:function(){
						var row=$('#user-dg').datagrid('getSelected');
						if(row==null){
							$.messager.alert('提示','请选择数据','info');
						}else{
							$.messager.confirm('确认','您确认想要停用用户<font color="red">'+row.name+'</font>吗？',function(r){
								if(r){
									$.post(
										'<?php echo $this->getUrl('system/user/disable'); ?>',
										{user_id:row.user_id},
										function(result){
											if(result.error){
												$.messager.show({title:'失败',msg:result.msg.join("<br>")});
											}else{
												$.messager.show({title:'成功',msg:result.msg.join("<br>")});
												$('#user-dg').datagrid('reload');
											}
										},
										'json'
									);
								}
							});
						}
					}
				},
				'-',
				{text:'刷新',iconCls:'icon-reload',
					handler:function(){
						window.location.replace(window.location.href);
					}
				}
			]
		});

		$('.datagrid-filter').prependTo('.datagrid-toolbar');

		$('#user-dlg').dialog({
			width:315,
			closed:true,
			modal:true,
			top:58,
			buttons:[
				{text:'保存',iconCls:'icon-ok',
					handler:function(){
						$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
							if(r){
								$('#user-fm').form('enableValidation');
								$('#user-fm').form('submit',{
									url:url,
									onSubmit:function(){
										return $(this).form('validate');
									},
									success:function(result){
										result=$.parseJSON(result);
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#user-dlg').dialog('close');
											$('#user-dg').datagrid('reload');
										}
									}
								});
							}
						});
					}
				},
				{text:'取消',iconCls:'icon-cancel',
					handler:function(){
						$('#user-dlg').dialog('close');
					}
				}
			],
			onBeforeOpen:function(){
				$('#user-fm').form('disableValidation');
				$('#user-fm').form('reset');
			}
		});

		$('#role-dlg').dialog({
			width:315,
			closed:true,
			modal:true,
			top:58,
			buttons:[
				{text:'提交',iconCls:'icon-ok',
					handler:function(){
						$.messager.confirm('确认','您确认提交吗？',function(r){
							if(r){
								$('#role-fm').form('enableValidation');
								$('#role-fm').form('submit',{
									url:url,
									onSubmit:function(){
										return $(this).form('validate');
									},
									success:function(result){
										result=$.parseJSON(result);
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#role-dlg').dialog('close');
											$('#user-dg').datagrid('reload');
										}
									}
								});
							}
						});
					}
				},
				{text:'取消',iconCls:'icon-cancel',
					handler:function(){
						$('#role-dlg').dialog('close');
					}
				}
			],
			onBeforeOpen:function(){
				$('#role-fm').form('disableValidation');
				$('#role-fm').form('reset');
			}
		});

		$('#password-dlg').dialog({
			width:315,
			closed:true,
			modal:true,
			top:58,
			buttons:[
				{text:'提交',iconCls:'icon-ok',
					handler:function(){
						$.messager.confirm('确认','您确认提交吗？',function(r){
							if(r){
								$('#password-fm').form('enableValidation');
								$('#password-fm').form('submit',{
									url:url,
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
											$('#user-dg').datagrid('reload');
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

		$('#gpassword').bind('click',function(){
			var password='';
			for(i=0;i<16;i++){
				password+="abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ~@#$%^&*(){}[]|".charAt(Math.floor(Math.random()*77));
			}
			$("input[name='gpassword']").val(password);
			$("input[name='password']").val(password);
			$("input[name='rpassword']").val(password);
		});

		$(document).keydown(function(event){
			if(event.keyCode==13){
				filter();
			}
		});
	});

	function filter(){
		$('#user-dg').datagrid('load',{
			filter:{
				role_id:$('#filter-role').val()
			}
		});
	}
</script>
</body>
</html>