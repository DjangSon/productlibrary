<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>选项值管理:<?php echo $option['name']; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
	<script type="text/javascript">var optionId='<?php echo $option['option_id']; ?>';</script>
</head>
<body>
<table id="value-dg"></table>
<div id="value-dlg">
	<form id="value-fm" method="post">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="value-name">选项值名称:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="name" id="value-name" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="value-sort">排序:</label>
					<div class="input-box">
						<input type="text" class="input-text easyui-numberbox" name="sort" id="value-sort" maxlength="3" data-options="value:0,width:171" />
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<div id="upload-dlg">
	<form id="upload-fm" method="post" enctype="multipart/form-data">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="optionValueUpload-fl">文件名:</label>
					<div class="input-box">
						<input type="file" name="optionValueUpload-fl" id="optionValueUpload-fl" />
					</div>
				</div>
			</li>
			<li class="control">
				<p>上传文件必须为CSV文件</p>
				<p>批量上传选项示例 <a href="<?php echo APP_VAR_URL; ?>download/example/option.csv">示例下载</a></p>
			</li>
		</ul>
	</form>
</div>
<script type="text/javascript">
var url;
$(function(){
	$('#value-dg').datagrid({
		title:'选项值管理:<?php echo $option['name']; ?>',
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('product/option/valueList', true); ?>option_id='+optionId,
		idField:'option_value_id',
		columns:[[
			{field:'option_value_id',hidden:true},
			{title:'选项值名称',field:'name',align:'left'},
			{title:'排序',field:'sort',align:'center'},
			{title:'添加人',field:'by_added',align:'center'},
			{title:'添加时间',field:'date_added',align:'center'},
			{title:'修改人',field:'by_modified',align:'center'},
			{title:'修改时间',field:'date_modified',align:'center'},
		]],
		toolbar:[
			{text:'添加选项值',iconCls:'icon-add',plain:true,
				handler:function(){
					$('#value-dlg').dialog('open').dialog('setTitle','添加选项值');
					url='<?php echo $this->getUrl('product/option/valueAdd', true); ?>option_id='+optionId;
				}
			},
			'-',
			{text:'批量导入',iconCls:'icon-undo',plain:true,
				handler:function(){
					$('#upload-dlg').dialog('open').dialog('setTitle','批量导入');
					url='<?php echo $this->getUrl('product/option/valueOptionImport', true); ?>option_id='+optionId;
				}
			},
			'-',
			{text:'修改选项值',iconCls:'icon-edit',
				handler:function(){
					var row=$('#value-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#value-dlg').dialog('open').dialog('setTitle','修改选项值');
						$('#value-fm').form('load','<?php echo $this->getUrl('product/option/valueSelect', true); ?>option_value_id='+row.option_value_id);
						url='<?php echo $this->getUrl('product/option/valueUpdate', true); ?>option_value_id='+row.option_value_id;
					}
				}
			},
			'-',
			{text:'删除选项值',iconCls:'icon-remove',plain:true,
				handler:function(){
					var row=$('#value-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要删除选项值<font color="red">'+row.name+'</font>吗？',function(r){
							if(r){
								$.post(
									'<?php echo $this->getUrl('product/option/valueDel'); ?>',
									{option_value_id:row.option_value_id},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#value-dg').datagrid('clearSelections');
											$('#value-dg').datagrid('reload');
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

	$('#value-dlg').dialog({
		width:290,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#value-fm').form('enableValidation');
							$('#value-fm').form('submit',{
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
										$('#value-dlg').dialog('close');
										$('#value-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#value-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#value-fm').form('disableValidation');
			$('#value-fm').form('reset');
		}
	});

	$('#upload-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'导入',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要导入吗？',function(r){
						if(r){
							$.messager.progress({text:'处理中, 请等待 ...',interval:1000});
							$('#upload-fm').form('enableValidation');
							$('#upload-fm').form('submit',{
								url:url,
								onSubmit:function(){
									return $(this).form('validate');
								},
								success:function(result){
									$.messager.progress('close');
									result=$.parseJSON(result);
									if(result.error){
										$.messager.alert('导入失败',result.msg.join("<br>"));
									}else{
										$.messager.alert('导入完成',result.msg.join("<br>"));
										$('#upload-dlg').dialog('close');
										$('#value-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#upload-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#upload-fm').form('disableValidation');
			$('#upload-fm').form('reset');
		}
	});
});
</script>
</body>
</html>