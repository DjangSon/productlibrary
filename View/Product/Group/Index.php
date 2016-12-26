<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>产品分组</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
</head>
<body>
<table id="group-dg"></table>
<ul class="datagrid-filter">
	<li class="fields last">
		<div class="field">
			<label for="filter-group">分组名称:</label>
			<div class="input-box">
				<input type="text" class="input-text" id="filter-name" />
			</div>
		</div>
	</li>
</ul>
<div id="group-dlg">
	<form id="group-fm" method="post">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="group-name">分组名称:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="name" id="group-name" data-groups="required:true" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="group-icon">备注:</label>
					<div class="input-box">
						<div class="input-box">
							<input type="text" class="input-text easyui-validatebox" name="remarks" id="group-remarks" data-groups="required:true" />
						</div>
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<div id="attribute-dlg">
	<form id="attribute-fm" method="post">
		<ul class="form-list">
			<li class="control"><input type="checkbox" class="checkbox" onclick="$('input[id^=attribute-]').prop('checked', this.checked);" /><strong>全选/全不选</strong></li>
			<?php $i=0; ?>
			<?php $count = count($attributeList); ?>
			<?php foreach ($attributeList as $attribute) { ?>
				<?php if ($i++%2==0) { ?>
					<li class="control">
						<?php } ?>
							<input type="checkbox" value="<?php echo $attribute['dictionary_id']; ?>" name="attributes[]" id="attribute-<?php echo $attribute['dictionary_id']; ?>" /><label style="margin-right: 60px" for="attribute-<?php echo $attribute['dictionary_id']; ?>"><?php echo $attribute['name']; ?></label>
						<?php if ($i%2==0 || $i==$count) { ?>
					</li>
				<?php } ?>
			<?php } ?>
		</ul>
	</form>
</div>
<div id="price-dlg">
	<form id="price-fm" method="post">
		<ul class="form-list">
			<li class="control"><input type="checkbox" class="checkbox" onclick="$('input[id^=price-]').prop('checked', this.checked);" /><strong>全选/全不选</strong></li>
			<?php $i=0; ?>
			<?php $count = count($priceList); ?>
			<?php foreach ($priceList as $price) { ?>
				<?php if ($i++%2==0) { ?>
					<li class="control">
				<?php } ?>
				<div style="float: left; width: 130px">
					<input type="checkbox" class="checkbox" value="<?php echo $price['dictionary_id']; ?>" name="prices[]" id="price-<?php echo $price['dictionary_id']; ?>" /><label for="price-<?php echo $price['dictionary_id']; ?>"><?php echo $price['name']; ?></label>
				</div>
				<?php if ($i%2==0 || $i==$count) { ?>
					</li>
				<?php } ?>
			<?php } ?>
		</ul>
	</form>
</div>
<div id="option-dlg">
	<form id="option-fm" method="post" >
		<ul class="form-list">
			<li class="control"><input type="checkbox" class="checkbox" onclick="$('input[id^=option-]').prop('checked', this.checked);" /><strong>全选/全不选</strong></li>
			<?php $i=0; ?>
			<?php $count = count($optionList); ?>
			<?php foreach ($optionList as $option) { ?>
				<?php if ($i++%2==0) { ?>
					<li class="control">
				<?php } ?>
					<div class="field" style="width:50%">
						<input type="checkbox" class="checkbox" value="<?php echo $option['option_id']; ?>" name="options[]" id="option-<?php echo $option['option_id']; ?>" /><?php echo $option['name']; ?>（<?php echo (!isset($option['type'])) ? '没有类型' : (($option['type'] == '0') ? '输入' : (($option['type'] == '1') ? '选择' : '类型错误')) ; ?>）
					</div>
				<?php if ($i%2==0 || $i==$count) { ?>
					</li>
				<?php } ?>
			<?php } ?>
		</ul>
	</form>
</div>
<div id="batch-dlg">
	<form id="batch-fm" method="post" enctype="multipart/form-data">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="batch-fl">文件名:</label>
					<div class="input-box">
						<input type="file" name="batch-fl" id="batch-fl" />
					</div>
				</div>
			</li>
			<li class="control">
				<p>上传文件必须为CSV文件</p>
				<p>批量上传产品示例 <a href="<?php echo APP_VAR_URL; ?>download/example/batch.csv">示例下载</a></p>
			</li>
		</ul>
	</form>
</div>
<script type="text/javascript">
var url;
$(function(){
	$('#group-dg').datagrid({
		title:'产品分组',
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('product/group/groupList'); ?>',
		queryParams:{
			filter:{
				date:{
					name:$('#filter-name').val()
				}
			}
		},
		idField:'group_id',
		columns:[[
			{field:'group_id',hidden:true},
			{title:'操作',field:'product',align:'center',
				formatter:function(value,row){
					return '[ <a href="javascript:void(0)" onclick="product(\''+row.name+'\','+row.group_id+')">管理</a> ]';
				}
			},
			{title:'分组名称',field:'name',align:'left'},
			{title:'备注',field:'remarks',align:'left'},
			{title:'属性',field:'attributes',align:'left'},
			{title:'价格',field:'prices',align:'left'},
			{title:'选项',field:'options',align:'left'},
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
			{text:'添加分组',iconCls:'icon-add',plain:true,
				handler:function(){
					$('#group-dlg').dialog('open').dialog('setTitle','添加分组');
					url='<?php echo $this->getUrl('product/group/addGroup'); ?>';
				}
			},
			'-',
			{text:'修改分组',iconCls:'icon-edit',
				handler:function(){
					var row=$('#group-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#group-dlg').dialog('open').dialog('setTitle','编辑选项');
						$('#group-fm').form('load','<?php echo $this->getUrl('product/group/getGroup', true); ?>group_id='+row.group_id);
						url='<?php echo $this->getUrl('product/group/updateGroup', true); ?>group_id='+row.group_id;
					}
				}
			},
			'-',
			{text:'删除分组',iconCls:'icon-remove',plain:true,
				handler:function(){
					var row=$('#group-dg').datagrid('getSelected');

					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要删除分组<font color="red">'+row.name+'</font>吗？',function(r){
							if(r){
								$.post(
									'<?php echo $this->getUrl('product/group/delGroup'); ?>',
									{group_id:row.group_id},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#group-dg').datagrid('clearSelections');
											$('#group-dg').datagrid('reload');
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
			{text:'批量下架',iconCls:'icon-undo',plain:true,
				handler:function(){
					var row=$('#group-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#batch-dlg').dialog('open').dialog('setTitle','批量下架');
						url='<?php echo $this->getUrl('product/group/batchStatus', true); ?>group_id='+row.group_id+'&status=0';
					}
				}
			},
			'-',
			{text:'批量启用',iconCls:'icon-undo',plain:true,
				handler:function(){
					var row=$('#group-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#batch-dlg').dialog('open').dialog('setTitle','批量启用');
						url='<?php echo $this->getUrl('product/group/batchStatus', true); ?>group_id='+row.group_id+'&status=1';
					}
				}
			},
			'-',
			{text:'批量缺货',iconCls:'icon-undo',plain:true,
				handler:function(){
					var row=$('#group-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#batch-dlg').dialog('open').dialog('setTitle','批量缺货');
						url='<?php echo $this->getUrl('product/group/batchStatus', true); ?>group_id='+row.group_id+'&status=2';
					}
				}
			},
			'-',
			{text:'属性分配',iconCls:'icon-right',
				handler:function(){
					var row=$('#group-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#attribute-dlg').dialog('open').dialog('setTitle','属性分配:'+row.name);
						$('#attribute-fm').form('load','<?php echo $this->getUrl('product/group/getAttributes', true); ?>group_id='+row.group_id);
						url='<?php echo $this->getUrl('product/group/updateAttributes', true); ?>group_id='+row.group_id;
					}
				}
			},
			'-',
			{text:'价格分配',iconCls:'icon-right',plain:true,
				handler:function(){
					var row=$('#group-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#price-dlg').dialog('open').dialog('setTitle','价格分配:'+row.name);
						$('#price-fm').form('load','<?php echo $this->getUrl('product/group/getPrices', true); ?>group_id='+row.group_id);
						url='<?php echo $this->getUrl('product/group/updatePrices', true); ?>group_id='+row.group_id;
					}
				}
			},
			'-',
			{text:'选项分配',iconCls:'icon-right',plain:true,
				handler:function(){
					var row=$('#group-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#option-dlg').dialog('open').dialog('setTitle','选项分配:'+row.name);
						$('#option-fm').form('load','<?php echo $this->getUrl('product/group/getOptions', true); ?>group_id='+row.group_id);
						url='<?php echo $this->getUrl('product/group/updateOptions', true); ?>group_id='+row.group_id;
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

	$('#group-dlg').dialog({
		width:290,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#group-fm').form('enableValidation');
							$('#group-fm').form('submit',{
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
										$('#group-dlg').dialog('close');
										$('#group-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#group-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#group-fm').form('disableValidation');
			$('#group-fm').form('reset');
		}
	});

	$('#batch-dlg').dialog({
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
							$('#batch-fm').form('enableValidation');
							$('#batch-fm').form('submit',{
								url:url,
								onSubmit:function(){
									return $(this).form('validate');
								},
								success:function(result){
									$.messager.progress('close');
									result=$.parseJSON(result);
									if (result.error) {
										$.messager.alert('导入失败',result.msg.join("<br>"));
									} else {
										$.messager.alert('导入完成',result.msg.join("<br>"));
										$('#batch-dlg').dialog('close');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#batch-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#batch-fm').form('disableValidation');
			$('#batch-fm').form('reset');
		}
	});

	$('#attribute-dlg').dialog({
		width:290,
		height:250,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存吗？',function(r){
						if(r){
							$.messager.progress({text:'处理中, 请等待 ...',interval:1000});
							$('#attribute-fm').form('submit',{
								url:url,
								onSubmit:function(){
									return $(this).form('validate');
								},
								success:function(result){
									$.messager.progress('close');
									result=$.parseJSON(result);
									if(result.error){
										$.messager.alert('失败',result.msg.join("<br>"),'error');
									}else{
										$.messager.show({title:'成功',msg:result.msg.join("<br>")});
										$('#attribute-dlg').dialog('close');
										$('#group-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#attribute-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#attribute-fm').form('reset');
		}
	});

	$('#price-dlg').dialog({
		width:300,
		height:300,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存吗？',function(r){
						if(r){
							$.messager.progress({text:'处理中, 请等待 ...',interval:1000});
							$('#price-fm').form('submit',{
								url:url,
								onSubmit:function(){
									return $(this).form('validate');
								},
								success:function(result){
									$.messager.progress('close');
									result=$.parseJSON(result);
									if(result.error){
										$.messager.alert('失败',result.msg.join("<br>"),'error');
									}else{
										$.messager.show({title:'成功',msg:result.msg.join("<br>")});
										$('#price-dlg').dialog('close');
										$('#group-dg').datagrid('reload');
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
			$('#price-fm').form('reset');
		}
	});

	$('#option-dlg').dialog({
		width:350,
		height:300,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存吗？',function(r){
						if(r){
							$.messager.progress({text:'处理中, 请等待 ...',interval:1000});
							$('#option-fm').form('submit',{
								url:url,
								onSubmit:function(){
									return $(this).form('validate');
								},
								success:function(result){
									$.messager.progress('close');
									result=$.parseJSON(result);
									if(result.error){
										$.messager.alert('失败',result.msg.join("<br>"),'error');
									}else{
										$.messager.show({title:'成功',msg:result.msg.join("<br>")});
										$('#option-dlg').dialog('close');
										$('#group-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#option-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#price-fm').form('reset');
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
	$('#group-dg').datagrid('load',{
		filter:{
			name:$('#filter-name').val(),
		}
	});
}

function product(name, groupId)
{
	parent.window.addTab('产品管理:'+name,'<?php echo $this->getUrl('product/group/product', true); ?>group_id='+groupId,'icon-info');
}
</script>
</body>
</html>