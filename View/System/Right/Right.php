<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>权限管理:<?php echo $rightGroup['name']; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
	<script type="text/javascript">var rightgroup_id='<?php echo $rightGroup['rightgroup_id']; ?>';</script>
</head>
<body>
<table id="right-dg"></table>
<div id="right-dlg">
<form id="right-fm" method="post">
<ul class="form-list">
	<li class="fields">
		<div class="field">
			<label for="right-name">名称:<em>*</em></label>
			<div class="input-box">
				<input type="text" class="input-text easyui-validatebox" name="name" id="right-name" data-options="required:true" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="right-icon">icon:</label>
			<div class="input-box">
				<input type="text" class="input-text" name="icon" id="right-icon" data-options="required:true" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="right-url">地址:<em>*</em></label>
			<div class="input-box">
				<input type="text" class="input-text easyui-validatebox" name="url" id="right-url" data-options="required:true" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="right-sort">排序:</label>
			<div class="input-box">
				<input type="text" class="input-text easyui-numberbox" name="sort" id="right-sort" maxlength="3" data-options="value:0,width:171" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="right-is_menu">菜单显示:</label>
			<div class="input-box">
				<select name="is_menu" id="right-is_menu">
					<option value="1">是</option>
					<option value="0">否</option>
				</select>
			</div>
		</div>
	</li>
</ul>
</form>
</div>
<script type="text/javascript">
var url;
$(function(){
	$('#right-dg').datagrid({
		title:'权限管理:<?php echo $rightGroup['name']; ?>',
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('system/right/rightList', true); ?>rightgroup_id='+rightgroup_id,
		idField:'right_id',
		columns:[[
			{field:'right_id',hidden:true},
			{title:'权限名称',field:'name',align:'left'},
			{title:'icon',field:'icon',align:'left'},
			{title:'地址',field:'url',align:'left'},
			{title:'排序',field:'sort',align:'center'},
			{title:'菜单显示',field:'is_menu',align:'center',
				formatter:function(val){if(val==0){return '<font color="red">否</font>';}else if(val==1){return '<font color="green">是</font>';}}
			},
			{title:'添加人',field:'by_added',align:'center'},
			{title:'添加时间',field:'date_added',align:'center'},
			{title:'修改人',field:'by_modified',align:'center'},
			{title:'修改时间',field:'date_modified',align:'center'}
		]],
		toolbar:[
			{text:'添加权限',iconCls:'icon-add',plain:true,
				handler:function(){
					$('#right-dlg').dialog('open').dialog('setTitle','添加权限');
					url='<?php echo $this->getUrl('system/right/addRight', true); ?>rightgroup_id='+rightgroup_id;
				}
			},
			'-',
			{text:'修改权限',iconCls:'icon-edit',
				handler:function(){
					var row=$('#right-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#right-dlg').dialog('open').dialog('setTitle','修改权限');
						$('#right-fm').form('load','<?php echo $this->getUrl('system/right/getRight', true); ?>right_id='+row.right_id+'&rightgroup_id='+rightgroup_id);
						url='<?php echo $this->getUrl('system/right/updateRight', true); ?>right_id='+row.right_id;
					}
				}
			},
			'-',
			{text:'删除权限',iconCls:'icon-remove',plain:true,
				handler:function(){
					var row=$('#right-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要删除权限<font color="red">'+row.name+'</font>吗？',function(r){
							if(r){
								$.post(
									'<?php echo $this->getUrl('system/right/delRight'); ?>',
									{right_id:row.right_id},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#right-dg').datagrid('clearSelections');
											$('#right-dg').datagrid('reload');
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

	$('#right-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[	
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#right-fm').form('enableValidation');
							$('#right-fm').form('submit',{
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
										$('#right-dlg').dialog('close');
										$('#right-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#right-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#right-fm').form('disableValidation');
			$('#right-fm').form('reset');
		}
	});
});
</script>
</body>
</html>