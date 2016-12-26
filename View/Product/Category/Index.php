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
	<script type="text/javascript">var groupId='<?php echo $group['group_id']; ?>';</script>
	<script type="text/javascript">var categoryId='<?php echo $category['category_id']; ?>';</script>
</head>
<body class="easyui-layout">
<div data-options="region:'west',title:'分类管理:<?php echo $group['name'] . '(ID:' . $group['group_id'] . ')'; ?>',width:'250',collapsible:false">
	<div class="easyui-panel" style="padding:5px;position:fixed;border:none;">
		<a id="treeOperate" href="javascript:;" class="easyui-linkbutton" onclick="treeOperate()" data-options="iconCls:'icon-tip'" data-value="展开" >展开</a>
		<a href="javascript:;" class="easyui-menubutton" data-options="menu:'#operate',iconCls:'icon-edit'">操作</a>
	</div>
	<div id="operate" style="width:150px;">
		<?php if ($canAddSubCategory) { ?>
		<div onclick="addCategory()" data-options="iconCls:'icon-add'" >
			添加分类
		</div>
		<?php } ?>
		<?php if ($addCategory && $canAddSubCategory) { ?>
		<div onclick="uploadCategory()" data-options="iconCls:'icon-undo'" >
			导入分类
		</div>
		<?php } ?>
		<?php if ($category['category_id'] > 0) { ?>
		<div id="updateCategory" onclick="updateCategory()" data-options="iconCls:'icon-edit'" >
			修改分类
		</div>
		<div id="delCategory" onclick="delCategory();" data-options="iconCls:'icon-remove'" >
			删除分类
		</div>
		<?php } ?>
	</div>
	<ul id="category-tree" style="padding-top:38px"></ul>
</div>
<div id="category-dlg">
	<form id="category-fm" method="post">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="category-name">名称:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="name" id="category-name" data-options="required:true" value="" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="category-image">图片路径:</label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="image" id="category-image" value="" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label>状态:</label>
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
					<label for="category-sort">排序:</label>
					<div class="input-box">
						<input type="text" class="input-text easyui-numberbox" name="sort" id="category-sort" maxlength="3" data-options="value:0,width:171" value="" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="category-sort">描述:</label>
					<div class="input-box">
						<textarea type="text" class="input-text" name="description" id="category-description"  data-options="multiline:true" style="height:60px" ></textarea>
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<div id="panelDiv" data-options="region:'center',title:'<?php echo sprintf('分类:%s(ID:%s):产品信息', str_replace("'", "\'", $category['name']), $category['category_id']); ?>'">
	<div title="产品信息">
		<table id="categoryProduct-dg"></table>
		<div id="categoryProduct-dlg">
			<form id="categoryProduct-fm" method="post">
				<ul class="form-list">
					<li class="fields">
						<div class="field">
							<label for="categoryProduct-sku">产品型号:<em>*</em></label>
							<div class="input-box">
								<input type="text" class="input-text easyui-validatebox" name="sku" id="categoryProduct-sku" data-options="required:true"/>
							</div>
						</div>
					</li>
					<li class="fields">
						<div class="field">
							<label for="category-sort">排序:</label>
							<div class="input-box">
								<input type="text" class="input-text easyui-numberbox" name="sort" id="category-sort" maxlength="10" data-options="value:0,width:171" />
							</div>
						</div>
					</li>
				</ul>
			</form>
		</div>
	</div>
</div>
<ul class="datagrid-filter">
	<li class="fields last">
		<div class="field">
			<label for="filter-sku">型号:</label>
			<div class="input-box">
				<input type="text" class="input-text" id="filter-sku" />
			</div>
		</div>
		<div class="field">
			<label>状态:</label>
			<div class="input-box">
				<select id="filter-status">
					<option value="">全部</option>
					<option value="1">启用</option>
					<option value="0">下架</option>
					<option value="2">缺货</option>
				</select>
			</div>
		</div>
		<div class="field">
			<label>主分类:</label>
			<div class="input-box">
				<select id="filter-isMaster">
					<option value="">全部</option>
					<option value="1">是</option>
					<option value="0">否</option>
				</select>
			</div>
		</div>
	</li>
</ul>
<div id="uploadCategory-dlg">
	<form id="uploadCategory-fm" method="post" enctype="multipart/form-data">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="uploadCategory-fl">文件名:</label>
					<div class="input-box">
						<input type="file" name="uploadCategory-fl" id="uploadCategory-fl" />
					</div>
				</div>
			</li>
			<li class="control">
				<p>上传文件必须为CSV文件</p>
				<p>批量上传根分类示例 <a href="<?php echo APP_VAR_URL; ?>download/example/importCategory.csv">示例下载</a></p>
			</li>
		</ul>
	</form>
</div>
<script type="text/javascript">
$(function(){
	$('#category-fm').form('disableValidation');
	$('#category-tabs').tabs();

	$('#category-tree').tree({
		animate:true,
		lines:true,
		data:<?php echo json_encode($data); ?>,
		onClick: function(node){
			window.location.replace('<?php echo $this->getUrl('product/category/category', true) . 'group_id='; ?>'+groupId+'&category_id='+node.id);
		},
		formatter:function(node){
			if(node.id==categoryId){
				return '<font color="red">'+node.text+'</font>';
			}
			return node.text;
		},
		onLoadSuccess:function(){
			var node = $('#category-tree').tree('find', categoryId);
			$('#category-tree').tree('expandTo', node.target);
			$('#category-tree').tree('expand', node.target);
			setTimeout(function(){
				$('#category-tree').tree('scrollTo', node.target);
			}, 300);
		}
	});

	$('#uploadCategory-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'导入',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要导入数据吗？',function(r){
						if(r){
							$.messager.progress({text:'处理中, 请等待 ...',interval:1000});
							$('#uploadCategory-fm').form('enableValidation');
							$('#uploadCategory-fm').form('submit',{
								url:url,
								onSubmit:function(){
									return $(this).form('validate');
								},
								success:function(result){
									$.messager.progress('close');
									result=$.parseJSON(result);
									if(result.error){
										$.messager.show({title:'失败',msg:result.msg.join("<br>")});
									}else{
										$.messager.alert('导入成功',result.msg.join("<br>"),'info',function(){
											window.location.replace(window.location.href);
										});
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#uploadCategory-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#uploadCategory-fm').form('disableValidation');
			$('#uploadCategory-fm').form('reset');
		}
	});

	$('#categoryProduct-dg').datagrid({
		height:$('#panelDiv').height(),
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('product/category/getCategoryToProduct', true); ?>group_id='+groupId+'&category_id='+categoryId,
		idField:'product_id',
		columns:[[
			{field:'product_id',hidden:true},
			{title:'图片',field:'image',align:'left',width:'108',
				formatter:function(val){
					return '<img src="<?php echo APP_HTTP . 'images/product/'; ?>'+val+'" alt="" width="100" height="100" onerror="javascript:this.src=\'<?php echo APP_HTTP; ?>images/no_image.jpg\'" />';
				}
			},
			{title:'产品型号',field:'sku',align:'center'},
			{title:'分类',field:'category',align:'center'},
			{title:'状态',field:'status',align:'center',
				formatter:function(val){if(val==0){return '<font color="red">下架</font>';}else if(val==1){return '<font color="green">启用</font>';}else if(val==3){return '<font color="gray">缺货</font>';}}
			},
			{title:'主分类',field:'is_master',align:'center',
				formatter:function(val){if(val==0){return '<font color="red">否</font>';}else if(val==1){return '<font color="green">是</font>';}}
			},
			{title:'排序',field:'sort',align:'center'},
			{title:'添加人',field:'by_added',align:'center'},
			{title:'添加时间',field:'date_added',align:'center'},
		]],
		toolbar:[
			{text:'查询',iconCls:'icon-search',
				handler:function(){
					filter();
				}
			},
			'-',
			<?php if ($showProduct) { ?>
			{text:'添加产品',iconCls:'icon-add',plain:true,
				handler:function(){
					$('#categoryProduct-dlg').dialog('open').dialog('setTitle','添加产品');
					url='<?php echo $this->getUrl('product/category/productAdd', true); ?>group_id='+groupId+'&category_id='+categoryId;
				}
			},
			'-',
			<?php } ?>
			{text:'刷新',iconCls:'icon-reload',
				handler:function(){
					$('#categoryProduct-dg').datagrid('reload');
				}
			}
		]
	});

	$('#categoryProduct-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#categoryProduct-fm').form('enableValidation');
							$('#categoryProduct-fm').form('submit',{
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
										$('#categoryProduct-dlg').dialog('close');
										$('#categoryProduct-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#categoryProduct-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#categoryProduct-fm').form('disableValidation');
			$('#categoryProduct-fm').form('reset');
		}
	});

	$('#category-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#category-fm').form('enableValidation');
							$('#category-fm').form('submit',{
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
										setTimeout(function(){
											window.location.replace('<?php echo $this->getUrl('product/category/category', true) . 'group_id='; ?>'+groupId+'&category_id='+result.category_id);
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
					$('#category-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#category-fm').form('disableValidation');
			$('#category-fm').form('reset');
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
	$('#categoryProduct-dg').datagrid('load',{
		filter:{
			sku:$('#filter-sku').val(),
			status:$('#filter-status').val(),
			is_master:$('#filter-isMaster').val()
		}
	});
}

function delCategory()
{
	$.messager.confirm('确认','您确认想要删除分类<font color="red">'+'<?php echo str_replace("'", "\'", $category['name']) ;?>'+'</font>吗？',function(r){
		if(r){
			$.post(
				'<?php echo $this->getUrl('product/category/categoryDel', true) . 'group_id='; ?>'+groupId+'&category_id='+categoryId,
				function(result){
					if(result.error){
						$.messager.show({title:'失败',msg:result.msg.join("<br>")});
					}else{
						$.messager.show({title:'成功',msg:result.msg.join("<br>")});
						setTimeout(function() {
							window.location.replace('<?php echo $this->getUrl('product/category/category', true) . 'group_id='; ?>'+groupId);
						}, 2000);
					}
				},
				'json'
			);
		}
	});
}

function treeOperate()
{
	var text = $('#treeOperate').attr('data-value');
	if (text == '展开') {
		$('#category-tree').tree('expandAll');
		$('#treeOperate').linkbutton({text: '收起'});
		$('#treeOperate').attr('data-value', '收起');
	} else if (text == '收起') {
		$('#category-tree').tree('collapseAll');
		$('#treeOperate').linkbutton({text: '展开'});
		$('#treeOperate').attr('data-value', '展开');
	}
}

function addCategory()
{
	$('#category-dlg').dialog('open').dialog('setTitle','添加分类');
	url='<?php echo $this->getUrl('product/category/categoryAdd', true); ?>group_id='+groupId;
}

function updateCategory()
{
	$('#category-dlg').dialog('open').dialog('setTitle','修改分类');
	$('#category-fm').form('load','<?php echo $this->getUrl('product/category/categoryGet', true); ?>group_id='+groupId+'&category_id='+categoryId);
	url='<?php echo $this->getUrl('product/category/categoryUpdate', true) . 'group_id='; ?>'+groupId+'&category_id='+categoryId;
}

function uploadCategory()
{
	$('#uploadCategory-dlg').dialog('open').dialog('setTitle','导入分类');
	url='<?php echo $this->getUrl('product/category/categoryUpload', true); ?>group_id='+groupId+'&category_id='+categoryId;
}
</script>
</body>
</html>