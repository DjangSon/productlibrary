<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>产品管理</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
	<script type="text/javascript">var group_id='<?php echo $group['group_id']; ?>';</script>
</head>
<body>
<ul class="datagrid-filter">
	<li class="fields last">
		<div class="field">
			<label for="filter-sku">产品型号:</label>
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
	</li>
</ul>
<table id="product-dg"></table>
<div id="product-dlg">
	<form id="product-fm" method="post">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="product-icon">产品型号:</label>
					<div class="input-box">
						<input type="text" class="input-text" name="sku" id="product-icon" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="product-url">图片路径:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="image" id="product-url" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="product-is_menu">产品状态:</label>
					<div class="input-box">
						<select name="status" id="product-is_menu">
							<option value="1">启用</option>
							<option value="0">下架</option>
							<option value="2">缺货</option>
						</select>
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="product-description_short">短描述:</label>
					<div class="input-box">
						<input type="text" class="input-text" name="description_short" id="product-description_short"/>
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="product-description">描述:</label>
					<div class="input-box">
						<textarea type="text" class="input-text" name="description" id="product-description" /></textarea>
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<div id="format-dlg">
	<form id="format-fm" method="post" enctype="multipart/form-data">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="format-fl">文件名:</label>
					<div class="input-box">
						<input type="file" name="format-fl" id="format-fl" />
					</div>
				</div>
			</li>
			<li class="control">
				<p>上传文件必须为CSV文件</p>
				<p>批量上传产品示例 <a href="<?php echo APP_VAR_URL; ?>download/example/format.csv">示例下载</a></p>
			</li>
		</ul>
	</form>
</div>
<div id="productUpload-dlg">
	<form id="productUpload-fm" method="post" enctype="multipart/form-data">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="format-fl">产品:</label>
					<div class="input-box">
						<input type="file" name="product-fl" id="product-fl" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="format-fl">属性:</label>
					<div class="input-box">
						<input type="file" name="attribute-fl" id="attribute-fl" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="format-fl">价格:</label>
					<div class="input-box">
						<input type="file" name="price-fl" id="price-fl" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="format-fl">选项:</label>
					<div class="input-box">
						<input type="file" name="option-fl" id="option-fl" />
					</div>
				</div>
			</li>
			<li class="control">
				<p>上传文件必须为CSV文件</p>
				<p>批量上传产品示例 <a id="example" onclick="example()" href="javascript:void(0)">示例下载</a></p>
			</li>
		</ul>
	</form>
</div>
<div id="attribute-dlg">
	<form id="attribute-fm" method="post">
		<ul class="form-list">
			<?php  if (!empty($group['attributes[]'])) { ?>
			<?php foreach ($attributeList as $key => $val) { ?>
			<?php  if (in_array($val['dictionary_id'], $group['attributes[]'])) { ?>
			<li class="fields">
				<div class="field">
					<label for="product-icon"><?php echo $val['name']; ?> :</label>
					<input type="text" style="width: 110px" class="input-text" name="<?php echo $val['dictionary_id']?>" id="attribute-<?php echo $val['dictionary_id']; ?>"/>
				</div>
			</li>
			<?php } ?>
			<?php } ?>
			<?php } else { ?>
			<li class="fields">
				<div class="field">
					<label for="product-url">该产品分组没有分配属性！</label>
				</div>
			</li>
			<?php } ?>
		</ul>
	</form>
</div>
<div id="price-dlg">
	<form id="price-fm" method="post">
		<ul class="form-list">
			<?php if (!empty($group['prices[]'])) { ?>
			<?php foreach ($priceList as $key => $val) { ?>
			<?php  if (in_array($val['dictionary_id'],$group['prices[]'])) { ?>
			<li class="fields">
				<div class="field">
					<label for="product-icon"><?php echo $val['name'] ?>:</label>
					<input type="text" style="width: 73px" class="input-text easyui-numberbox easyui-validatebox" data-options="required:true,width:171,precision:2,value:0.00" name="<?php echo $val['dictionary_id']?>-price" id="price-<?php echo $val['dictionary_id']; ?>"/> /
					<input type="text" style="width: 73px" class="input-text easyui-numberbox easyui-validatebox" data-options="required:true,width:171,precision:2,value:0.00" name="<?php echo $val['dictionary_id']?>-special_price" id="special_price-<?php echo $val['dictionary_id']; ?>"/>
				</div>
			</li>
			<?php } ?>
			<?php } ?>
			<?php } else { ?>
			<li class="fields">
				<div class="field">
					<label for="product-url">该产品分组没有分配价格！</label>
				</div>
			</li>
			<?php } ?>
		</ul>
	</form>
</div>
<div id="option-dlg"></div>
<script type="text/javascript">
var url;
$(function(){
	$('#product-dg').datagrid({
		title:'产品管理:<?php echo $group['name']; ?>',
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('product/group/productList', true); ?>group_id='+group_id,
		idField:'product_id',
		columns:[[
			{field:'product_id',hidden:true},
			{title:'图片',field:'image',align:'left',width:'108',
				formatter:function(val){
					return '<img src="<?php echo APP_HTTP . 'images/product/'; ?>'+val+'" alt="" height="100" />';
				}
			},
			{title:'产品型号',field:'sku',align:'left'},
			{title:'产品状态',field:'status',align:'center',
				formatter:function(val){if(val==0){return '<font color="gray">下架</font>';}else if(val==1){return '<font color="green">启用</font>';}else if(val==2){return '<font color="red">缺货</font>';}}
			},
			{title:'属性',field:'attributes',align:'left',
				formatter:function(val){if(val){return htmlDecodeByRegExp(val);}else{return '';}}
			},
			{title:'价格',field:'prices',align:'left',
				formatter:function(val){if(val){return htmlDecodeByRegExp(val);}else{return '';}}
			},
			{title:'选项',field:'options',align:'left',
				formatter:function(val){if(val){return htmlDecodeByRegExp(val);}else{return '';}}
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
			{text:'添加产品',iconCls:'icon-add',plain:true,
				handler:function(){
					$('#product-dlg').dialog('open').dialog('setTitle','添加产品');
					url='<?php echo $this->getUrl('product/group/addProduct', true); ?>group_id='+group_id;
				}
			},
			'-',
			{text:'修改产品',iconCls:'icon-edit',
				handler:function(){
					var row=$('#product-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#product-dlg').dialog('open').dialog('setTitle','修改产品');
						$('#product-fm').form('load','<?php echo $this->getUrl('product/group/getProduct', true); ?>product_id='+row.product_id);
						url='<?php echo $this->getUrl('product/group/updateProduct', true); ?>product_id='+row.product_id+'&group_id='+group_id;
					}
				}
			},
			'-',
			{text:'导入产品',iconCls:'icon-undo',plain:true,
				handler:function(){
					$('#productUpload-dlg').dialog('open').dialog('setTitle','导入产品');
					url='<?php echo $this->getUrl('product/group/uploadProduct', true); ?>group_id='+group_id;
				}
			},
			'-',
			{text:'批量格式化',iconCls:'icon-undo',plain:true,
				handler:function(){
					$('#format-dlg').dialog('open').dialog('setTitle','批量格式化');
					url='<?php echo $this->getUrl('product/group/format', true); ?>group_id='+group_id;
				}
			},
			'-',
			{text:'属性管理',iconCls:'icon-edit',
				handler:function(){
					var row=$('#product-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#attribute-dlg').dialog('open').dialog('setTitle','属性管理');
						$('#attribute-fm').form('load','<?php echo $this->getUrl('product/group/getAttributes', true); ?>group_id='+group_id+'&product_id='+row.product_id);
						url='<?php echo $this->getUrl('product/group/updateAttributeValue', true); ?>product_id='+row.product_id+'&group_id='+group_id;
					}
				}
			},
			'-',
			{text:'价格管理',iconCls:'icon-edit',
				handler:function(){
					var row=$('#product-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#price-dlg').dialog('open').dialog('setTitle','价格管理');
						$('#price-fm').form('load','<?php echo $this->getUrl('product/group/getPrices', true); ?>group_id='+group_id+'&product_id='+row.product_id);
						url='<?php echo $this->getUrl('product/group/updatePriceValue', true); ?>product_id='+row.product_id+'&group_id='+group_id;
					}
				}
			},
			'-',
			{text:'选项管理',iconCls:'icon-edit',
				handler:function(){
					var row=$('#product-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#option-dlg').dialog('open').dialog('setTitle','选项管理');
						$('#option-dlg').dialog('refresh','<?php echo $this->getUrl('product/group/getOptionValues', true); ?>group_id='+group_id+'&product_id='+row.product_id);
						url='<?php echo $this->getUrl('product/group/updateProductToOptionValue', true); ?>product_id='+row.product_id+'&group_id='+group_id;
					}
				}
			},
			'-',
			{text:'刷新',iconCls:'icon-reload',
				handler:function(){
					window.location.replace(window.location.href);
				}
			}
		],
		onLoadSuccess:function(data){
			$('img').error(function() {
				$(this).attr('src', '<?php echo APP_HTTP; ?>images/no_image.jpg');
			});
		}
	});

	$('#product-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#product-fm').form('enableValidation');
							$('#product-fm').form('submit',{
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
										$('#product-dlg').dialog('close');
										$('#product-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#product-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#product-fm').form('disableValidation');
			$('#product-fm').form('reset');
		}
	});

	$('#attribute-dlg').dialog({
		width:250,
		closed:true,
		modal:true,
		top:150,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#attribute-fm').form('enableValidation');
							$('#attribute-fm').form('submit',{
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
										$('#attribute-dlg').dialog('close');
										$('#product-dg').datagrid('reload');
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
			$('#attribute-fm').form('disableValidation');
			$('#attribute-fm').form('reset');
		}
	});

	$('#price-dlg').dialog({
		width:280,
		closed:true,
		modal:true,
		top:150,
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
										$('#product-dg').datagrid('reload');
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


	$('#option-dlg').dialog({
		width:600,
		height:450,
		closed:true,
		modal:true,
		top:150,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#option-fm').form('enableValidation');
							$('#option-fm').form('submit',{
								url:url,
								onSubmit:function(){
									return $(this).form('validate');
								},
								success:function(result){
									result=$.parseJSON(result);
									if(result.error){
										$.messager.alert('失败',result.msg.join("<br>"),'info');
									}else{
										$.messager.alert('成功',result.msg.join("<br>"),'info');
										$('#option-dlg').dialog('close');
										$('#product-dg').datagrid('reload');
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
			$('#option-fm').form('disableValidation');
			$('#option-fm').form('reset');
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
									if (result.error) {
										$.messager.alert('格式化失败',result.msg.join("<br>"));
									} else {
										$.messager.alert('格式化成功',result.msg.join("<br>"));
										window.open(result.url,'_blank','width=0,height=0,status=0');
										$('#format-dlg').dialog('close');
									}
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

	$('#productUpload-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'导入',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要导入记录吗？',function(r){
						if(r){
							$.messager.progress({text:'处理中, 请等待 ...',interval:1000});
							$('#productUpload-fm').form('enableValidation');
							$('#productUpload-fm').form('submit',{
								url:url,
								onSubmit:function(){
									return $(this).form('validate');
								},
								success:function(result){
									$.messager.progress('close');
									result=$.parseJSON(result);
									if (result.error) {
										$.messager.alert('导入产品失败',result.msg.join("<br>"));
									} else {
										$.messager.alert('导入产品成功',result.msg.join("<br>"));
										$('#productUpload-dlg').dialog('close');
										$('#product-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#productUpload-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#productUpload-fm').form('disableValidation');
			$('#productUpload-fm').form('reset');
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
	$('#product-dg').datagrid('load',{
		filter:{
			sku:$('#filter-sku').val(),
			status:$('#filter-status').val(),
		}
	});
}

function example(){
	$.post(
		'<?php echo $this->getUrl('product/group/example', true) . 'group_id=' . $group['group_id']; ?>',
		function(result){
			$.messager.progress('close');
			window.open(result.url,'_blank','width=0,height=0,status=0');
		},
		'json'
	);
}

function htmlDecodeByRegExp(str){

	if(str.length == 0) return "";
	s = str.replace(/&amp;/g,"&");
	s = s.replace(/&lt;/g,"<");
	s = s.replace(/&gt;/g,">");
	s = s.replace(/&nbsp;/g," ");
	s = s.replace(/&#39;/g,"\'");
	s = s.replace(/&quot;/g,"\"");
	return s;
}
</script>
</body>
</html>