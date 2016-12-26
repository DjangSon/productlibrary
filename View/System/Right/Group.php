<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>权限分组</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
</head>
<body>
<table id="rightgroup-dg"></table>
<div id="rightgroup-dlg">
<form id="rightgroup-fm" method="post">
<ul class="form-list">
	<li class="fields">
		<div class="field">
			<label for="rightgroup-name">分组名称:<em>*</em></label>
			<div class="input-box">
				<input type="text" class="input-text easyui-validatebox" name="name" id="rightgroup-name" data-options="required:true" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="rightgroup-icon">icon:</label>
			<div class="input-box">
				<input type="text" class="input-text easyui-validatebox" name="icon" id="rightgroup-icon" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="rightgroup-sort">排序:</label>
			<div class="input-box">
				<input type="text" class="input-text easyui-numberbox" name="sort" id="rightgroup-sort" maxlength="3" data-options="value:0,width:171" />
			</div>
		</div>
	</li>
</ul>
</form>
</div>
<script type="text/javascript">
var url;
$(function(){
	$('#rightgroup-dg').datagrid({
		title:'权限分组',
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('system/right/groupList'); ?>',
		idField:'rightgroup_id',
		columns:[[
			{field:'rightgroup_id',hidden:true},
			{title:'分组名称',field:'name',align:'left'},
			{title:'icon',field:'icon',align:'left'},
			{title:'排序',field:'sort',align:'center'},
			{title:'添加人',field:'by_added',align:'center'},
			{title:'添加时间',field:'date_added',align:'center'},
			{title:'修改人',field:'by_modified',align:'center'},
			{title:'修改时间',field:'date_modified',align:'center'}
		]],
		toolbar:[
			{text:'添加分组',iconCls:'icon-add',plain:true,
				handler:function(){
					$('#rightgroup-dlg').dialog('open').dialog('setTitle','添加分组');
					url='<?php echo $this->getUrl('system/right/addGroup'); ?>';
				}
			},
			'-',
			{text:'修改分组',iconCls:'icon-edit',
				handler:function(){
					var row=$('#rightgroup-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#rightgroup-dlg').dialog('open').dialog('setTitle','编辑分组');
						$('#rightgroup-fm').form('load','<?php echo $this->getUrl('system/right/getGroup', true); ?>rightgroup_id='+row.rightgroup_id);
						url='<?php echo $this->getUrl('system/right/updateGroup', true); ?>rightgroup_id='+row.rightgroup_id;
					}
				}
			},
			'-',
			{text:'删除分组',iconCls:'icon-remove',plain:true,
				handler:function(){
					var row=$('#rightgroup-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要删除分组<font color="red">'+row.name+'</font>吗？',function(r){
							if(r){
								$.post(
									'<?php echo $this->getUrl('system/right/delGroup'); ?>',
									{rightgroup_id:row.rightgroup_id},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#rightgroup-dg').datagrid('clearSelections');
											$('#rightgroup-dg').datagrid('reload');
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
			{text:'权限管理',iconCls:'icon-add',plain:true,
				handler:function(){
					var row=$('#rightgroup-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						parent.window.addTab('权限管理:'+row.name,'<?php echo $this->getUrl('system/right/right', true); ?>rightgroup_id='+row.rightgroup_id,'icon-right');
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

	$('#rightgroup-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#rightgroup-fm').form('enableValidation');
							$('#rightgroup-fm').form('submit',{
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
										$('#rightgroup-dlg').dialog('close');
										$('#rightgroup-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#rightgroup-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#rightgroup-fm').form('disableValidation');
			$('#rightgroup-fm').form('reset');
		}
	});
});
</script>
</body>
</html>