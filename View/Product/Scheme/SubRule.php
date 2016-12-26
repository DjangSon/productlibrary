<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>子规则管理:<?php echo $scheme['category_group']; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
	<script type="text/javascript">var schemeId='<?php echo $scheme['scheme_id']; ?>';</script>
</head>
<body>
<table id="subRule-dg"></table>
<div id="category-dlg">
	<form id="category-fm" method="post">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label>分类名称:<em>*</em></label>
					<input class="input-text" id="categorytree" name="category_id" value="请选择" />
				</div>
			</li>
		</ul>
	</form>
</div>
<div id="subRule-dlg">
	<form id="subRule-fm" method="post">
		<ul class="form-list">
			<li class="fields wide">
				<div class="field">
					<label><strong>分类规则</strong></label>
					<div class="input-box">
						<label><font color="red">[分类名称]</font></label>
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="subRule-category_meta_name">分类名称:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="categoryRule_name" id="subRule-category_meta_name" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="rule-category_description">描述:</label>
					<div class="input-box">
						<input type="text" class="input-text" name="categoryRule_description" id="rule-category_description" placeholder="可以使用[分类描述]" />
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="subRule-category_meta_title">meta标题:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="categoryRule_meta_title" id="subRule-category_meta_title" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="subRule-category_meta_keyword">meta关键字:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="categoryRule_meta_keyword" id="subRule-category_meta_keyword" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="subRule-category_meta_description">meta描述:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="categoryRule_meta_description" id="subRule-category_meta_description" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="subRule-category_url">url:</label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="categoryRule_url" id="subRule-category_url" />
					</div>
				</div>
			</li>
		</ul>
		<ul class="form-list">
			<li class="fields wide">
				<div class="field">
					<label><strong>产品规则</strong></label>
					<div class="input-box">
						<label><font color="red">[分类名称] [产品名称] [原价] [特价]<?php echo !empty($attributeList) ? '<br />' . $attributeList : ''; ?></font></label>
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="subRule-product_name">产品名称:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="productRule_name" id="subRule-product_name" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="rule-product_short_description">短描述:</label>
					<div class="input-box">
						<input type="text" class="input-text" name="productRule_short_description" id="rule-product_short_description" placeholder="可以使用[产品短描述]" />
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="rule-product_description">描述:</label>
					<div class="input-box">
						<input type="text" class="input-text" name="productRule_description" id="rule-product_description" placeholder="可以使用[产品描述]" />
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="subRule-product_meta_title">meta标题:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="productRule_meta_title" id="subRule-product_meta_title" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="subRule-product_meta_keyword">meta关键字:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="productRule_meta_keyword" id="subRule-product_meta_keyword" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="subRule-product_meta_description">meta描述:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="productRule_meta_description" id="subRule-product_meta_description" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields wide">
				<div class="field">
					<label for="subRule-product_url">url:</label>
					<div class="input-box">
						<input type="text" class="input-text" name="productRule_url" id="subRule-product_url" />
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<script type="text/javascript">
var url;
$(function(){
	$('#subRule-dg').datagrid({
		title:'子规则管理:<?php echo $scheme['category_group']; ?>',
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		url:'<?php echo $this->getUrl('product/scheme/subRuleList', true); ?>scheme_id='+schemeId,
		idField:'path',
		columns:[[
			{field:'path',hidden:true},
			{title:'分类名称',field:'category_name',align:'left'},
			{title:'子规则',field:'isset_rule',align:'center',
				formatter:function(val){if(val==1){return '<font color="red">已设置</font>';}else if(val==0){return '<font color="green">未设置</font>';}}
			},
			{title:'添加人',field:'by_added',align:'center'},
			{title:'添加时间',field:'date_added',align:'center'},
			{title:'修改人',field:'by_modified',align:'center'},
			{title:'修改时间',field:'date_modified',align:'center'}
		]],
		toolbar:[
			{text:'添加分类子规则',iconCls:'icon-add',plain:true,
				handler:function(){
					$('#category-dlg').dialog('open').dialog('setTitle','添加分类子规则');
					url='<?php echo $this->getUrl('product/scheme/subRuleAdd', true); ?>scheme_id='+schemeId;
				}
			},
			'-',
			{text:'更新子规则',iconCls:'icon-edit',plain:true,
				handler:function(){
					var row=$('#subRule-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#subRule-dlg').dialog('open').dialog('setTitle','更新子规则');
						$('#subRule-fm').form('load','<?php echo $this->getUrl('product/scheme/subRuleGet', true); ?>scheme_id='+schemeId+'&path='+row.path);
						url='<?php echo $this->getUrl('product/scheme/subRuleUpdate', true); ?>scheme_id='+schemeId+'&path='+row.path;
					}
				}
			},
			'-',
			{text:'删除子规则',iconCls:'icon-remove',plain:true,
				handler:function(){
					var row=$('#subRule-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要删除分类名为<font color="red">'+row.category_name+'</font>的子规则吗？',function(r){
							if(r){
								$.post(
									'<?php echo $this->getUrl('product/scheme/subRuleDel'); ?>',
									{scheme_id:schemeId,path:row.path},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#subRule-dg').datagrid('clearSelections');
											$('#subRule-dg').datagrid('reload');
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

	$('#subRule-dlg').dialog({
		width:598,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#subRule-fm').form('enableValidation');
							$('#subRule-fm').form('submit',{
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
										$('#subRule-dlg').dialog('close');
										$('#subRule-dg').datagrid('clearSelections');
										$('#subRule-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#subRule-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#subRule-fm').form('disableValidation');
			$('#subRule-fm').form('reset');
		}
	});


	$('#category-dlg').dialog({
		width:270,
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
										$('#category-dlg').dialog('close');
										$('#subRule-dg').datagrid('clearSelections');
										$('#subRule-dg').datagrid('reload');
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
			$('#categorytree').combotree('clear');
			var t = $('#categorytree').combotree('tree');
			t.tree('collapseAll');
			$('#category-fm').form('disableValidation');
			$('#category-fm').form('reset');
		}
	});

	$('#categorytree').combotree({
		data:<?php echo json_encode($data, true); ?>,
		label:'Select Node:',
		labelPosition:'top',
		panelWidth:'270',
		panelHeight:'400'
	});
});
</script>
</body>
</html>