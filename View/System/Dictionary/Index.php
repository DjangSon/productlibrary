<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>字典管理</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
</head>
<body>
<table id="dictionary-dg"></table>
<ul class="datagrid-filter">
	<li class="fields last">
		<div class="field">
			<label for="filter-name">字典名称:</label>
			<div class="input-box">
				<input type="text" class="input-text" id="filter-name" />
			</div>
		</div>
		<div class="field">
			<label for="filter-type">分组名称:</label>
			<div class="input-box">
				<select id="filter-type">
					<option value="">全部</option>
					<?php foreach ($typeList as $val) { ?>
					<option value="<?php echo $val; ?>"><?php echo $val; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</li>
</ul>
<div id="dictionary-dlg">
<form id="dictionary-fm" method="post">
<ul class="form-list">
	<li class="fields">
		<div class="field">
			<label for="dictionary-type">分组名称:<em>*</em></label>
			<div class="input-box">
				<input type="text" class="input-text easyui-validatebox" name="type" id="dictionary-type" data-options="required:true" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="dictionary-name">字典名称:<em>*</em></label>
			<div class="input-box">
				<input type="text" class="input-text easyui-validatebox" name="name" id="dictionary-name" data-options="required:true" />
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="dictionary-status">状态:<em>*</em></label>
			<div class="input-box">
				<select id="dictionary-status" class="easyui-validatebox" name="status" data-options="required:true">
					<option value="1">启用</option>
					<option value="0">禁用</option>
				</select>
			</div>
		</div>
	</li>
	<li class="fields">
		<div class="field">
			<label for="dictionary-sort">排序:</label>
			<div class="input-box">
				<input type="text" class="input-text easyui-numberbox" name="sort" id="dictionary-sort" maxlength="3" data-options="value:0,width:171" />
			</div>
		</div>
	</li>
</ul>
</form>
</div>
<script type="text/javascript">
var url;
$(function(){
	$('#dictionary-dg').datagrid({
		title:'字典管理',
		fit:true,
		border:false,
		rownumbers:true,
		striped:true,
		checkOnSelect:false,
		ctrlSelect:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('system/dictionary/list'); ?>',
		idField:'dictionary_id',
		columns:[[
			{field:'dictionary_id',checkbox:true},
			{title:'分组名称',field:'type',align:'left'},
			{title:'字典名称',field:'name',align:'left'},
			{title:'排序',field:'sort',align:'center'},
			{title:'状态',field:'status',align:'center',
				formatter:function(val){if(val=='1'){return '<font color="green">启用</font>';}else if(val=='0'){return '<font color="red">停用</font>';}}
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
			{text:'添加字典',iconCls:'icon-add',plain:true,
				handler:function(){
					$('#dictionary-type').attr('readOnly',false);
					$('#dictionary-name').attr('readOnly',false);
					$('#dictionary-dlg').dialog('open').dialog('setTitle','添加字典');
					url='<?php echo $this->getUrl('system/dictionary/add'); ?>';
				}
			},
			'-',
			{text:'修改字典',iconCls:'icon-edit',
				handler:function(){
					var row=$('#dictionary-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#dictionary-type').attr('readOnly',true);
						$('#dictionary-name').attr('readOnly',true);
						$('#dictionary-dlg').dialog('open').dialog('setTitle','修改字典');
						$('#dictionary-fm').form('load','<?php echo $this->getUrl('system/dictionary/get', true); ?>dictionary_id='+row.dictionary_id);
						url='<?php echo $this->getUrl('system/dictionary/update', true); ?>dictionary_id='+row.dictionary_id;
					}
				}
			},
			'-',
			{text:'启用',iconCls:'icon-ok',
				handler:function(){
					var rows=$('#dictionary-dg').datagrid('getChecked');
					if(rows.length==0){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要启用选中的字典吗？',function(r){
							if(r){
								var ids=new Array();
								for(var row in rows){
									ids[row]=rows[row].dictionary_id;
								}
								$.post(
									'<?php echo $this->getUrl('system/dictionary/enabled'); ?>',
									{ids:ids},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#dictionary-dg').datagrid('clearChecked');
											$('#dictionary-dg').datagrid('reload');
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
			{text:'停用',iconCls:'icon-cancel',
				handler:function(){
					var rows=$('#dictionary-dg').datagrid('getChecked');
					if(rows.length==0){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要停用选中的字典吗？',function(r){
							if(r){
								var ids=new Array();
								for(var row in rows){
									ids[row]=rows[row].dictionary_id;
								}
								$.post(
									'<?php echo $this->getUrl('system/dictionary/disable'); ?>',
									{ids:ids},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#dictionary-dg').datagrid('clearChecked');
											$('#dictionary-dg').datagrid('reload');
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

	$('#dictionary-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[	
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#dictionary-fm').form('enableValidation');
							$('#dictionary-fm').form('submit',{
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
										$('#dictionary-dlg').dialog('close');
										$('#dictionary-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#dictionary-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#dictionary-fm').form('disableValidation');
			$('#dictionary-fm').form('reset');
		}
	});

	$('.datagrid-filter').prependTo('.datagrid-toolbar');

	$(document).keydown(function(event){
		if(event.keyCode==13){
			filter();
		}
	});
});

function filter(){
	$('#dictionary-dg').datagrid('load',{
		filter:{
			name:$('#filter-name').val(),
			type:$('#filter-type').val(),
			status:$('#filter-status').val()
		}
	});
}
</script>
</body>
</html>