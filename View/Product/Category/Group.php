<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>分类方案</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
</head>
<body>
<table id="categorygroup-dg"></table>
<div id="categorygroup-dlg">
	<form id="categorygroup-fm" method="post">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="productgroup-name">产品分组:<em>*</em></label>
					<select type="text" class="input-text easyui-validatebox" name="product_group_id" id="productgroup-name">
						<option value="0">请选择</option>
						<?php foreach ($productGroupList as $val) { ?>
							<option value="<?php echo $val['group_id']; ?>"><?php echo $val['name']; ?></option>
						<?php } ?>
					</select>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="categorygroup-name">分类分组:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="name" id="categorygroup-name" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="categorygroup-sort">排序:</label>
					<div class="input-box">
						<input type="text" class="input-text easyui-numberbox" name="sort" id="categorygroup-sort" maxlength="3" data-options="value:0,width:171" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="categorygroup-remarks">备注:</label>
					<div class="input-box">
						<textarea class="input-text" name="remarks" id="categorygroup-remarks" ></textarea>
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<div id="uploadallcategory-dlg">
	<form id="uploadallcategory-fm" method="post" enctype="multipart/form-data">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="uploadallcategory-fl">分类:</label>
					<div class="input-box">
						<input type="file" name="uploadallcategory-fl" id="uploadallcategory-fl" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="categoryToProduct-fl">主分类产品:</label>
					<div class="input-box">
						<input type="file" name="categoryToProduct-fl" id="categoryToProduct-fl" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="subCategory-fl">副分类产品:</label>
					<div class="input-box">
						<input type="file" name="subCategory-fl" id="subCategory-fl" />
					</div>
				</div>
			</li>
			<li class="control">
				<p>上传文件必须为CSV文件</p>
				<p>批量上传分类示例 <a href="<?php echo APP_VAR_URL; ?>download/example/category.zip">示例下载</a></p>
			</li>
		</ul>
	</form>
</div>
<div id="format-dlg">
	<form id="format-fm" method="post" enctype="multipart/form-data">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="uploadallcategory-fl">分类表:</label>
					<div class="input-box">
						<input type="file" name="uploadallcategory-fl" id="uploadallcategory-fl" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="categoryToProduct-fl">主分类产品:</label>
					<div class="input-box">
						<input type="file" name="categoryToProduct-fl" id="categoryToProduct-fl" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="subCategory-fl">副分类产品:</label>
					<div class="input-box">
						<input type="file" name="subCategory-fl" id="subCategory-fl" />
					</div>
				</div>
			</li>
			<li class="control">
				<p>上传文件必须为CSV文件</p>
				<p>批量上传分类示例 <a href="<?php echo APP_VAR_URL; ?>download/example/categoryFormat.zip">示例下载</a></p>
			</li>
		</ul>
	</form>
</div>
<script type="text/javascript">
	var url;
	$(function(){
		$('#categorygroup-dg').datagrid({
			title:'分类分组',
			fit:true,
			border:false,
			rownumbers:true,
			singleSelect:true,
			striped:true,
			pagination:true,
			pageSize:25,
			pageList:[25,50,100],
			url:'<?php echo $this->getUrl('product/category/groupList'); ?>',
			idField:'group_id',
			columns:[[
				{field:'group_id',hidden:true},
				{title:'操作',field:'category',align:'center',
					formatter:function(value,row){
						return '[ <a href="javascript:void(0)" onclick="category(\''+row.name+'\','+row.group_id+')">管理</a> ]';
					}
				},
				{title:'分组名称',field:'name',align:'left'},
				{title:'备注',field:'remarks',align:'center'},
				{title:'产品分组',field:'product_group_id',align:'center'},
				{title:'排序',field:'sort',align:'center'},
				{title:'添加人',field:'by_added',align:'center'},
				{title:'添加时间',field:'date_added',align:'center'},
				{title:'修改人',field:'by_modified',align:'center'},
				{title:'修改时间',field:'date_modified',align:'center'}
			]],
			toolbar:[
				{text:'添加分组',iconCls:'icon-add',plain:true,
					handler:function(){
						$('#categorygroup-dlg').dialog('open').dialog('setTitle','添加分组');
						$('#productgroup-name').removeAttr('disabled');
						url='<?php echo $this->getUrl('product/category/groupAdd'); ?>';
					}
				},
				'-',
				{text:'修改分组',iconCls:'icon-edit',
					handler:function(){
						var row=$('#categorygroup-dg').datagrid('getSelected');
						if(row==null){
							$.messager.alert('提示','请选择数据','info');
						}else{
							$('#categorygroup-dlg').dialog('open').dialog('setTitle','修改分组');
							$('#productgroup-name').attr('disabled','disabled');
							$('#categorygroup-fm').form('load','<?php echo $this->getUrl('product/category/groupGet', true); ?>group_id='+row.group_id);
							url='<?php echo $this->getUrl('product/category/groupUpdate', true); ?>group_id='+row.group_id;
						}
					}
				},
				'-',
				{text:'删除分组',iconCls:'icon-remove',plain:true,
					handler:function(){
						var row=$('#categorygroup-dg').datagrid('getSelected');
						if(row==null){
							$.messager.alert('提示','请选择数据','info');
						}else{
							$.messager.confirm('确认','您确认想要删除分组<font color="red">'+row.name+'</font>吗？',function(r){
								if(r){
									$.post(
										'<?php echo $this->getUrl('product/category/groupDel'); ?>',
										{group_id:row.group_id},
										function(result){
											if(result.error){
												$.messager.show({title:'失败',msg:result.msg.join("<br>")});
											}else{
												$.messager.show({title:'成功',msg:result.msg.join("<br>")});
												$('#categorygroup-dg').datagrid('clearSelections');
												$('#categorygroup-dg').datagrid('reload');
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
				{text:'导入分类及分类与产品的关系',iconCls:'icon-undo',plain:true,
					handler:function(){
						var row=$('#categorygroup-dg').datagrid('getSelected');
						if(row==null){
							$.messager.alert('提示','请选择数据','info');
						}else {
							$('#uploadallcategory-dlg').dialog('open').dialog('setTitle', '导入分类及分类与产品的关系');
							url = '<?php echo $this->getUrl('product/category/categoryAllUpload', true); ?>group_id='+row.group_id;
						}
					}
				},
				'-',
				{text:'批量格式化',iconCls:'icon-undo',plain:true,
					handler:function(){
						$('#format-dlg').dialog('open').dialog('setTitle', '批量格式化');
						url = '<?php echo $this->getUrl('product/category/format'); ?>';
					}
				},
				'-',
				{text:'清除数据',iconCls:'icon-remove',plain:true,
					handler:function(){
						var row=$('#categorygroup-dg').datagrid('getSelected');
						if(row==null){
							$.messager.alert('提示','请选择数据','info');
						}else{
							$.messager.confirm('确认','您确认想要清除<font color="red">'+row.name+'</font>分类的无用数据（已删除）吗？',function(r){
								if(r){
									$.post(
										'<?php echo $this->getUrl('product/category/categoryClean'); ?>',
										{group_id:row.group_id},
										function(result){
											if(result.error){
												$.messager.show({title:'失败',msg:result.msg.join("<br>")});
											}else{
												$.messager.show({title:'成功',msg:result.msg.join("<br>")});
												$('#categorygroup-dg').datagrid('clearSelections');
												$('#categorygroup-dg').datagrid('reload');
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

		$('#categorygroup-dlg').dialog({
			width:290,
			closed:true,
			modal:true,
			top:58,
			buttons:[
				{text:'保存',iconCls:'icon-ok',
					handler:function(){
						$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
							if(r){
								$('#categorygroup-fm').form('enableValidation');
								$('#categorygroup-fm').form('submit',{
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
											$('#categorygroup-dlg').dialog('close');
											$('#categorygroup-dg').datagrid('reload');
										}
									}
								});
							}
						});
					}
				},
				{text:'取消',iconCls:'icon-cancel',
					handler:function(){
						$('#categorygroup-dlg').dialog('close');
					}
				}
			],
			onBeforeOpen:function(){
				$('#categorygroup-fm').form('disableValidation');
				$('#categorygroup-fm').form('reset');
			}
		});

		$('#uploadallcategory-dlg').dialog({
			width:315,
			closed:true,
			modal:true,
			top:58,
			buttons:[
				{text:'测试',iconCls:'icon-ok',
					handler:function(){
						$.messager.confirm('确认','您确认想要测试数据吗？',function(r){
							if(r){
								$.messager.progress({text:'处理中, 请等待 ...',interval:1000});
								$('#uploadallcategory-fm').form('enableValidation');
								$('#uploadallcategory-fm').form('submit',{
									url:url+'&debug',
									onSubmit:function(){
										return $(this).form('validate');
									},
									success:function(result){
										$.messager.progress('close');
										result=$.parseJSON(result);
										$.messager.alert('导入完成',result.msg.join("<br>"),'info');
										$('#uploadallcategory-dlg').dialog('close');
										$('#categorygroup-dg').datagrid('reload');
									}
								});
							}
						});
					}
				},
				{text:'导入',iconCls:'icon-ok',
					handler:function(){
						$.messager.confirm('确认','您确认想要导入数据吗？',function(r){
							if(r){
								$.messager.progress({text:'处理中, 请等待 ...',interval:1000});
								$('#uploadallcategory-fm').form('enableValidation');
								$('#uploadallcategory-fm').form('submit',{
									url:url,
									onSubmit:function(){
										return $(this).form('validate');
									},
									success:function(result){
										$.messager.progress('close');
										result=$.parseJSON(result);
										$.messager.alert('导入完成',result.msg.join("<br>"),'info');
										$('#uploadallcategory-dlg').dialog('close');
										$('#categorygroup-dg').datagrid('reload');
									}
								});
							}
						});
					}
				},
				{text:'取消',iconCls:'icon-cancel',
					handler:function(){
						$('#uploadallcategory-dlg').dialog('close');
					}
				}
			],
			onBeforeOpen:function(){
				$('#uploadallcategory-fm').form('disableValidation');
				$('#uploadallcategory-fm').form('reset');
			}
		});

		$('#format-dlg').dialog({
			width:315,
			closed:true,
			modal:true,
			top:58,
			buttons:[
				{text:'格式化',iconCls:'icon-ok',
					handler:function(){
						$.messager.confirm('确认','您确认想要格式化数据吗？',function(r){
							if(r){
								$.messager.progress({text:'处理中, 请等待 ...',interval:1000});
								$('#format-fm').form('enableValidation');
								$('#format-fm').form('submit',{
									url:url,
									onSubmit:function(){
										return $(this).form('validate');
									},
									success:function(result){
										$.messager.progress('close');
										result=$.parseJSON(result);
										$.messager.alert('格式化完成',result.msg.join("<br>"),'info');
										window.open(result.url,'_blank','width=0,height=0,status=0');
										$('#format-dlg').dialog('close');
									}
								});
							}
						});
					}
				},
				{text:'取消',iconCls:'icon-cancel',
					handler:function(){
						$('#format-dlg').dialog('close');
					}
				}
			],
			onBeforeOpen:function(){
				$('#format-fm').form('disableValidation');
				$('#format-fm').form('reset');
			}
		});
	});

	function category(name, groupId)
	{
		parent.window.addTab('分类管理:'+name,'<?php echo $this->getUrl('product/category/category', true); ?>group_id='+groupId,'icon-info');
	}
</script>
</body>
</html>