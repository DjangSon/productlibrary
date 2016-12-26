<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>产品价格</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
</head>
<body>
<table id="price-dg"></table>
<div id="price-dlg">
	<form id="price-fm" method="post">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="price-name">价格:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="name" id="price-name" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="price-sort">排序:</label>
					<div class="input-box">
						<input type="text" class="input-text easyui-numberbox" name="sort" id="price-sort" maxlength="3" data-options="value:0,width:171" />
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<script type="text/javascript">
var url;
$(function(){
	$('#price-dg').datagrid({
		title:'产品价格',
		fit:true,
		border:false,
		rownumbers:true,
		striped:true,
		checkOnSelect:false,
		ctrlSelect:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('product/price/list'); ?>',
		idField:'dictionary_id',
		columns:[[
			{field:'dictionary_id',checkbox:true},
			{title:'价格名称',field:'name',align:'left'},
			{title:'排序',field:'sort',align:'center'},
			{title:'状态',field:'status',align:'center',
				formatter:function(val){
					if(val==0){return '<font color="red">停用</font>';}else if(val==1){return '<font color="green">启用</font>';}
				}
			},
			{title:'添加人',field:'by_added',align:'center'},
			{title:'添加时间',field:'date_added',align:'center'},
			{title:'修改人',field:'by_modified',align:'center'},
			{title:'修改时间',field:'date_modified',align:'center'}
		]],
		toolbar:[
			{text:'添加价格',iconCls:'icon-add',
				handler:function(){
					$("#price-name").attr("readOnly",false);
					$('#price-dlg').dialog('open').dialog('setTitle','添加价格');
					url='<?php echo $this->getUrl('product/price/add'); ?>';
				}
			},
			'-',
			{text:'启用',iconCls:'icon-ok',
				handler:function(){
					var rows=$('#price-dg').datagrid('getChecked');
					if(rows.length==0){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要启用选中的价格吗？',function(r){
							if(r){
								var ids=new Array();
								for(var row in rows){
									ids[row]=rows[row].dictionary_id;
								}
								$.post(
									'<?php echo $this->getUrl('product/price/enabled'); ?>',
									{ids:ids},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#price-dg').datagrid('clearChecked');
											$('#price-dg').datagrid('reload');
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
					var rows=$('#price-dg').datagrid('getChecked');
					if(rows.length==0){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要停用选中的价格吗？',function(r){
							if(r){
								var ids=new Array();
								for(var row in rows){
									ids[row]=rows[row].dictionary_id;
								}
								$.post(
									'<?php echo $this->getUrl('product/price/disable'); ?>',
									{ids:ids},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#price-dg').datagrid('clearChecked');
											$('#price-dg').datagrid('reload');
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

	$('#price-dlg').dialog({
		width:290,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#price-fm').form('enableValidation');
							$('#price-fm').form('submit',{
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
										$('#price-dlg').dialog('close');
										$('#price-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#price-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#price-fm').form('disableValidation');
			$('#price-fm').form('reset');
		}
	});
});
</script>
</body>
</html>