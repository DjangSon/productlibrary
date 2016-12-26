<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>角色管理</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
	<?php if (!empty($roleData)) { ?>
		<script type="text/javascript">var roleId='<?php echo $roleData['role_id']; ?>';</script>
	<?php } ?>
</head>
<body class="easyui-layout">
<?php if (!empty($roleData)) { ?>
	<div data-options="region:'west',title:'角色列表',width:'250',collapsible:false">
		<div class="easyui-panel" style="padding:5px;position:fixed;border:none;">
			<a href="javascript:;" class="easyui-menubutton" data-options="menu:'#operate',iconCls:'icon-edit'">操作</a>
		</div>
		<div id="operate" style="width:150px;">
			<div onclick="addRole();" data-options="iconCls:'icon-add'" >
				添加角色
			</div>
			<div onclick="delRole();" data-options="iconCls:'icon-remove'" >
				删除角色
			</div>
		</div>
		<ul id="role-tree" style="padding-top:38px"></ul>
	</div>
	<div data-options="region:'center'">
		<div class="easyui-tabs" data-options="fit:true,border:false" style="height:360px;">
			<div id="right-dlg" title="<?php echo $roleData['name']; ?>：权限分配">
				<form id="right-fm" method="post">
					<ul class="form-list">
						<?php foreach ($rightGroupList as $rightGroup) {
							if (empty($rightGroup['right'])) {
								continue;
							}
							?>
							<?php $i=0; ?>
							<?php $count = count($rightGroup); ?>
							<li class="control"><input type="checkbox" class="checkbox" onclick="$('input[id^=right-<?php echo $rightGroup['rightgroup_id']; ?>]').prop('checked', this.checked);" /><strong><?php echo $rightGroup['name']; ?></strong></li>
							<?php foreach ($rightGroup['right'] as $count => $right) { ?>
								<?php if ($i++%4==0) { ?>
									<li class="control">
								<?php } ?>
								<div class="field" style="width:24.9%">
									<p><input type="checkbox" class="checkbox" value="<?php echo $right['right_id']; ?>" name="rights[]" id="right-<?php echo $rightGroup['rightgroup_id']; ?>-<?php echo $right['right_id']; ?>" <?php if (in_array($right['right_id'], $roleRight)) { ?> checked="checked" <?php } ?> /><label for="right-<?php echo $rightGroup['rightgroup_id']; ?>-<?php echo $right['right_id']; ?>"><?php echo $right['name']; ?></label></p>
								</div>
								<?php if ($i%4==0 || $i==$count) { ?>
									</li>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					</ul>
				</form>
				<div class="buttons-set">
					<a href="javascript:void(0)" class="easyui-linkbutton" onclick="updateRights();" data-options="iconCls:'icon-ok'">保 存</a>
				</div>
			</div>
		</div>
	</div>
	<div id="role-dlg">
		<form id="role-fm" method="post">
			<ul class="form-list">
				<li class="fields">
					<div class="field">
						<label for="role-name">角色名称:<em>*</em></label>
						<div class="input-box">
							<input type="text" class="input-text easyui-validatebox" name="name" id="role-name" data-options="required:true" />
						</div>
					</div>
				</li>
			</ul>
		</form>
	</div>
<?php } else { ?>
	<div id="addRole-dlg">
		<form id="addRole-fm" method="post">
			<ul class="form-list">
				<li class="fields">
					<div class="field">
						<label for="role-name">角色名称:<em>*</em></label>
						<div class="input-box">
							<input type="text" class="input-text easyui-validatebox" name="name" id="role-name" data-options="required:true" />
						</div>
					</div>
				</li>
			</ul>
		</form>
	</div>
<?php } ?>
<script type="text/javascript">
	var url;
	$(function(){
		<?php if (empty($roleData)) { ?>
		$('#addRole-dlg').dialog({
			width:315,
			closed:true,
			modal:true,
			top:58,
			buttons:[
				{text:'保存',iconCls:'icon-ok',
					handler:function(){
						$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
							if(r){
								$('#addRole-fm').form('enableValidation');
								$('#addRole-fm').form('submit',{
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
											setTimeout(function() {
												window.location.replace('<?php echo $this->getUrl('system/role/index'); ?>');
											}, 2000);
										}
									}
								});
							}
						});
					}
				},
			],
			onBeforeOpen:function(){
				$('#addRole-fm').form('disableValidation');
				$('#addRole-fm').form('reset');
			}
		});
		$('#addRole-dlg').dialog('open').dialog('setTitle','添加角色');
		url='<?php echo $this->getUrl('system/role/add'); ?>';
		<?php } else { ?>

		$('#role-tree').tree({
			animate:true,
			lines:true,
			data:<?php echo json_encode($roleTree); ?>,
			onClick: function(node){
				window.location.replace('<?php echo $this->getUrl('system/role/index', true); ?>&role_id='+node.id);
			},
			formatter:function(node){
				if(node.id==roleId){
					return '<font color="red">'+node.text+'</font>';
				}
				return node.text;
			},
			onLoadSuccess:function(){
				$('#role-tree').tree('expandAll');
			}
		});

		$('#role-dlg').dialog({
			width:315,
			closed:true,
			modal:true,
			top:58,
			buttons:[
				{text:'保存',iconCls:'icon-ok',
					handler:function(){
						$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
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
											setTimeout(function() {
												window.location.replace('<?php echo $this->getUrl('system/role/index'); ?>');
											}, 2000);
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
		<?php } ?>
	});
	<?php if (!empty($roleData)) { ?>
	function addRole()
	{
		$('#role-dlg').dialog('open').dialog('setTitle','添加角色');
		url='<?php echo $this->getUrl('system/role/add'); ?>';
	}

	function delRole()
	{
		$.messager.confirm('确认','您确认想要删除分类<font color="red">'+'<?php echo str_replace("'", "\'", $roleData['name']) ;?>'+'</font>吗？',function(r){
			if(r){
				$.post(
					'<?php echo $this->getUrl('system/role/del', true) . '&role_id=' . $roleData['role_id']; ?>',
					function(result){
						if(result.error){
							$.messager.show({title:'失败',msg:result.msg.join("<br>")});
						}else{
							$.messager.show({title:'成功',msg:result.msg.join("<br>")});
							setTimeout(function() {
								window.location.replace('<?php echo $this->getUrl('system/role/index'); ?>');
							}, 2000);
						}
					},
					'json'
				);
			}
		});
	}

	function updateRights()
	{
		$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
			if(r){
				$('#right-fm').form('enableValidation');
				$('#right-fm').form('submit',{
					url:'<?php echo $this->getUrl('system/role/updateRights', true); ?>role_id='+roleId,
					onSubmit:function(){
						return $(this).form('validate');
					},
					success:function(result){
						result=$.parseJSON(result);
						if(result.error){
							$.messager.alert('分配失败',result.msg.join("<br>"),'info');
						}else{
							$.messager.alert('分配成功',result.msg.join("<br>"),'info');
						}
					}
				});
			}
		});
	}
	<?php } ?>
</script>
</body>
</html>