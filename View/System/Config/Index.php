<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>配置管理:<?php echo $type; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
	<script type="text/javascript">var type='<?php echo $type; ?>';</script>
</head>
<body>
<table id="config-dg"></table>
<div id="config-dlg">
<form id="config-fm" method="post">
<ul class="form-list">
	<li class="fields">
		<div class="field">
			<label for="config-config_title">配置标题:<em>*</em></label>
			<div class="input-box">
				<input type="text" class="input-text easyui-validatebox" name="config_title" id="config-config_title" data-options="required:true" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="config-config_key">配置键名:<em>*</em></label>
			<div class="input-box">
				<input type="text" class="input-text easyui-validatebox" name="config_key" id="config-config_key" data-options="required:true" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="config-config_value">配置键值:<em>*</em></label>
			<div class="input-box">
				<input type="text" class="input-text easyui-validatebox" name="config_value" id="config-config_value" data-options="required:true" />
			</div>
		</div>
	</li>
</ul>
</form>
</div>
<script type="text/javascript">
var url;
$(function(){
	$('#config-dg').datagrid({
		title:'配置管理:'+type,
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('system/config/list', true) . 'type=' . $type; ?>',
		idField:'config_id',
		columns:[[
			{field:'config_id',hidden:true},
			{title:'配置标题',field:'config_title',width:200,align:'left'},
			{title:'配置键名',field:'config_key',width:200,align:'left'},
			{title:'配置键值',field:'config_value',width:200,align:'left'},
			{title:'添加人',field:'by_added',align:'center'},
			{title:'添加时间',field:'date_added',align:'center'},
			{title:'修改人',field:'by_modified',align:'center'},
			{title:'修改时间',field:'date_modified',align:'center'}
		]],
		toolbar:[
			{text:'添加配置',iconCls:'icon-add',
				handler:function(){
					$('#config-dlg').dialog('open').dialog('setTitle','添加配置');
					url='<?php echo $this->getUrl('system/config/add', true) . 'type=' . $type; ?>';
				}
			},
			'-',
			{text:'修改配置',iconCls:'icon-edit',
				handler:function(){
					var row=$('#config-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#config-dlg').dialog('open').dialog('setTitle','修改配置');
						$('#config-fm').form('load','<?php echo $this->getUrl('system/config/get', true); ?>config_id='+row.config_id);
						url='<?php echo $this->getUrl('system/config/update', true); ?>config_id='+row.config_id;
					}
				}
			},
			'-',
			{text:'删除配置',iconCls:'icon-remove',plain:true,
				handler:function(){
					var row=$('#config-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要删除配置<font color="red">'+row.config_title+'</font>吗？',function(r){
							if(r){
								$.post(
									'<?php echo $this->getUrl('system/config/del'); ?>',
									{config_id:row.config_id},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#config-dg').datagrid('clearSelections');
											$('#config-dg').datagrid('reload');
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

	$('#config-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#config-fm').form('enableValidation');
							$('#config-fm').form('submit',{
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
										$('#config-dlg').dialog('close');
										$('#config-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#config-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#config-fm').form('disableValidation');
			$('#config-fm').form('reset');
		}
	});
});
</script>
</body>
</html>