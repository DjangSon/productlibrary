<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>方案管理</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
</head>
<body>
<table id="scheme-dg"></table>
<div id="scheme-dlg">
	<form id="scheme-fm" method="post">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label>分类分组:<em>*</em></label>
					<div class="input-box">
						<select name="category_group_id">
						<?php if (!empty($categoryGroupList)) { ?>
						<?php foreach ($categoryGroupList as $val) {?>
							<option value="<?php echo $val['group_id']; ?>"><?php echo $val['name']; ?></option>
						<?php } ?>
						<?php } ?>
						</select>
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="scheme-name">方案名称:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="name" id="scheme-name" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="scheme-sort">排序:</label>
					<div class="input-box">
						<input type="text" class="input-text easyui-numberbox" name="sort" id="scheme-sort" maxlength="3" data-options="value:0,width:171" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="scheme-remarks">备注:<em>*</em></label>
					<div class="input-box">
						<textarea class="input-text easyui-validatebox" name="remarks" id="scheme-remarks" data-options="required:true" ></textarea>
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<div id="rule-dlg"></div>
<div id="export-dlg">
	<form id="export-fm" method="post">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label>导出选项:</label>
					<div class="input-box">
						<select name="exportFlag">
							<option value="1">该版本</option>
							<option value="2">截止到该版本</option>
						</select>
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label>版本号:</label>
					<div class="input-box">
						<select id="version" name="version" ></select>
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<script type="text/javascript">
var url, schemeId;
$(function(){
	$('#scheme-dg').datagrid({
		title:'方案管理',
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('product/scheme/list'); ?>',
		idField:'scheme_id',
		columns:[[
			{field:'scheme_id',hidden:true},
			{title:'操作',field:'scheme',align:'center',
				formatter:function(value,row){
					if (row.isset_rule == 1){
						return '[ <a href="javascript:void(0)" onclick="scheme(\''+row.name+'\','+row.scheme_id+')">预览</a> ]';
					}
				}
			},
			{title:'方案名称',field:'name',align:'left'},
			{title:'备注',field:'remarks',align:'left'},
			{title:'分类分组',field:'category_group',align:'left'},
			{title:'主规则',field:'isset_rule',align:'center',
				formatter:function(val){if(val==1){return '<font color="red">已设置</font>';}else if(val==0){return '<font color="green">未设置</font>';}}
			},
			{title:'添加人',field:'by_added',align:'center'},
			{title:'添加时间',field:'date_added',align:'center'},
			{title:'修改人',field:'by_modified',align:'center'},
			{title:'修改时间',field:'date_modified',align:'center'}
		]],
		toolbar:[
			{text:'添加方案',iconCls:'icon-add',
				handler:function(){
					$('#scheme-dlg').dialog('open').dialog('setTitle','请选择分类分组');
					url='<?php echo $this->getUrl('product/scheme/schemeAdd'); ?>';
				}
			},
			'-',
			{text:'导出方案',iconCls:'icon-redo',
				handler:function(){
					var row=$('#scheme-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择方案','info');
					}else if(row.isset_rule == 0){
						$.messager.alert('提示','请先设定规则','info');
					}else{
						$('#export-dlg').dialog('open').dialog('setTitle','导出方案');
						url = '<?php echo $this->getUrl('product/scheme/schemeExport', true); ?>scheme_id='+row.scheme_id;
					}
				}
			},
			'-',
			{text:'导出删除数据',iconCls:'icon-redo',
				handler:function(){
					var row=$('#scheme-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.post(
							'<?php echo $this->getUrl('product/scheme/delDataExport', true); ?>scheme_id='+row.scheme_id,
							function(result){
								if(result.error){
									$.messager.show({title:'失败',msg:result.msg.join("<br>")});
								}else{
									$.messager.show({title:'成功',msg:result.msg.join("<br>")});
									window.open(result.url,'_blank','width=0,height=0,status=0');
								}
							},
							'json'
						);
					}
				}
			},
			'-',
			{text:'主规则',iconCls:'icon-edit',
				handler:function(){
					var row=$('#scheme-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						schemeId = row.scheme_id;
						$('#rule-dlg').dialog('open').dialog('setTitle','主规则');
						$('#rule-dlg').dialog('refresh','<?php echo $this->getUrl('product/scheme/ruleGet', true); ?>scheme_id='+schemeId);
						url='<?php echo $this->getUrl('product/scheme/ruleUpdate', true); ?>scheme_id='+schemeId;
					}
				}
			},
			'-',
			{text:'子规则管理',iconCls:'icon-edit',plain:true,
				handler:function(){
					var row=$('#scheme-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else if(row.isset_rule == 0){
						$.messager.alert('提示','请先设定主规则','info');
					}else{
						parent.window.addTab('子规则管理:'+row.category_group,'<?php echo $this->getUrl('product/scheme/subRule', true); ?>scheme_id='+row.scheme_id);
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

	$('#export-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'导出',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要导出方案吗？',function(r){
						if(r){
							$.messager.progress({text:'处理中, 请等待 ...',interval:1000});
							$('#export-fm').form('enableValidation');
							$('#export-fm').form('submit', {
								url: url,
								onSubmit: function () {
									return $(this).form('validate');
								},
								success: function (result) {
									$.messager.progress('close');
									result = $.parseJSON(result);
									if (result.error) {
										$.messager.show({title: '失败', msg: result.msg.join("<br>")});
									} else {
										$.messager.show({title: '成功', msg: result.msg.join("<br>")});
										window.open(result.url,'_blank','width=0,height=0,status=0');
										$('#export-dlg').dialog('close');
										$('#scheme-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#export-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#version').empty();
			$.post(
				'<?php echo $this->getUrl('product/scheme/exportEditionGet', true) . 'scheme_id=' ;?>'+$('#scheme-dg').datagrid('getSelected').scheme_id,
				{'scheme_id':$('#scheme-dg').datagrid('getSelected').scheme_id},
				function(data){
					if (data.length > 0) {
						for (var count = 0; count < data.length; count++) {
							$('#version').append("<option value='"+data[count]+"'>"+data[count]+"</option>");
						}
					}
				},
				'json'
			);
			$('#export-fm').form('disableValidation');
			$('#export-fm').form('reset');
		}
	});

	$('#scheme-dlg').dialog({
		width:315,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#scheme-fm').form('enableValidation');
							$('#scheme-fm').form('submit',{
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
										$('#scheme-dlg').dialog('close');
										$('#scheme-dg').datagrid('clearSelections');
										$('#scheme-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#scheme-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#scheme-fm').form('disableValidation');
			$('#scheme-fm').form('reset');
		}
	});

	$('#rule-dlg').dialog({
		width:610,
		height:600,
		closed:true,
		modal:true,
		top:58,
		buttons:[
			{text:'保存',iconCls:'icon-ok',
				handler:function(){
					$.messager.confirm('确认','您确认想要保存记录吗？',function(r){
						if(r){
							$('#rule-fm').form('enableValidation');
							$('#rule-fm').form('submit',{
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
										$('#rule-dlg').dialog('close');
										$('#scheme-dg').datagrid('clearSelections');
										$('#scheme-dg').datagrid('reload');
									}
								}
							});
						}
					});
				}
			},
			{text:'取消',iconCls:'icon-cancel',
				handler:function(){
					$('#rule-dlg').dialog('close');
				}
			}
		],
		onBeforeOpen:function(){
			$('#rule-price_id').combobox({
				url:'<?php echo $this->getUrl('product/scheme/priceListGet', true); ?>scheme_id='+schemeId,
				valueField:'id',
				textField:'text'
			});
			$('#rule-fm').form('disableValidation');
			$('#rule-fm').form('reset');
		}
	});
});

function scheme(name, schemeId)
{
	parent.window.addTab('方案预览:'+name,'<?php echo $this->getUrl('product/scheme/preview', true); ?>scheme_id='+schemeId,'icon-info');
}
</script>
</body>
</html>