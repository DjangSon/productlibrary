<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>配置分组</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
</head>
<body>
<table id="configgroup-dg"></table>
<div id="configgroup-dlg">
<form id="configgroup-fm" method="post">
<ul class="form-list">
	<li class="fields">
		<div class="field">
			<label for="configgroup-name">分组名称:<em>*</em></label>
			<div class="input-box">
				<input type="text" class="input-text easyui-validatebox" name="name" id="configgroup-name" data-options="required:true" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label>状态:<em>*</em></label>
			<div class="input-box">
				<select name="status">
					<option value="1">启用</option>
					<option value="0">禁用</option>
				</select>
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="configgroup-sort">排序:</label>
			<div class="input-box">
				<input type="text" class="input-text" name="sort" id="configgroup-sort" />
			</div>
		</div>
	</li>
</ul>
</form>
</div>
<script type="text/javascript">
var url;
$(function(){
	$('#configgroup-dg').datagrid({
		title:'配置分组',
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('system/config/groupList'); ?>',
		idField:'dictionary_id',
		columns:[[
			{field:'dictionary_id',hidden:true},
			{title:'分组名',field:'name',align:'left'},
			{title:'状态',field:'status',align:'center',
				formatter:function(val){if(val=='1'){return '<font color="green">启用</font>';}else if(val=='0'){return '<font color="red">停用</font>';}}
			},
			{title:'排序',field:'sort',align:'left'},
			{title:'添加人',field:'by_added',align:'center'},
			{title:'添加时间',field:'date_added',align:'center'},
			{title:'修改人',field:'by_modified',align:'center'},
			{title:'修改时间',field:'date_modified',align:'center'}
		]],
		toolbar:[
			{text:'添加分组',iconCls:'icon-add',
				handler:function(){
					$('#configgroup-dlg').dialog('open').dialog('setTitle','添加分组');
					url='<?php echo $this->getUrl('system/config/addGroup'); ?>';
				}
			},
			'-',
			{text:'修改分组',iconCls:'icon-edit',
				handler:function(){
					var row=$('#configgroup-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#configgroup-dlg').dialog('open').dialog('setTitle','修改配置');
						$('#configgroup-fm').form('load','<?php echo $this->getUrl('system/config/getGroup', true); ?>id='+row.dictionary_id);
						url='<?php echo $this->getUrl('system/config/updateGroup', true); ?>id='+row.dictionary_id;
					}
				}
			},
			'-',
			{text:'删除分组',iconCls:'icon-remove',plain:true,
				handler:function(){
					var row=$('#configgroup-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要删除配置分组<font color="red">'+row.name+'</font>吗？',function(r){
							if(r){
								$.post(
									'<?php echo $this->getUrl('system/config/delGroup'); ?>',
									{id:row.dictionary_id,type:row.name},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#configgroup-dg').datagrid('clearSelections');
											$('#configgroup-dg').datagrid('reload');
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
			{text:'配置管理',iconCls:'icon-add',plain:true,
				handler:function(){
					var row=$('#configgroup-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						parent.window.addTab('配置管理:'+row.name,'<?php echo $this->getUrl('system/config/config', true); ?>type='+row.name,'icon-right');
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

	$('#configgroup-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#configgroup-fm').form('enableValidation');
							$('#configgroup-fm').form('submit',{
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
										$('#configgroup-dlg').dialog('close');
										$('#configgroup-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#configgroup-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#configgroup-fm').form('disableValidation');
			$('#configgroup-fm').form('reset');
		}
	});
});
</script>
</body>
</html>