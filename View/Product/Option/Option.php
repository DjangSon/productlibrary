<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>产品选项</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
</head>
<body>
<table id="option-dg"></table>
<div id="option-dlg">
	<form id="option-fm" method="post">
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label for="option-name">选项名称:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text easyui-validatebox" name="name" id="option-name" data-options="required:true" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="option-icon">选项类型:</label>
					<div class="input-box">
						<select type="text" class="input-text" name="type" id="option-icon" >
							<option style="width:160px" value="0">输入</option>
							<option style="width:160px" value="1">选择</option>
						</select>
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="option-sort">排序:</label>
					<div class="input-box">
						<input type="text" class="input-text easyui-numberbox" name="sort" id="option-sort" maxlength="3" data-options="value:0,width:171" />
					</div>
				</div>
			</li>
		</ul>
	</form>
</div>
<script type="text/javascript">
var url;
$(function(){
	$('#option-dg').datagrid({
		title:'产品选项',
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('product/option/optionList'); ?>',
		idField:'option_id',
		columns:[[
			{field:'option_id',hidden:true},
			{title:'操作',field:'other',align:'center',
				formatter:function(val,row){
					if(row.type==1){return '[ <a href="javascript:void(0)" onclick="showValue('+row.option_id+', \''+row.name+'\')">选项值</a> ]';}
				}
			},
			{title:'选项名称',field:'name',align:'left'},
			{title:'选项类型',field:'type',align:'left',
				formatter:function(val){
					if(val=='0'){return '输入';}else if(val=='1'){return '选择';}
				}
			},
			{title:'排序',field:'sort',align:'center'},
			{title:'添加人',field:'by_added',align:'center'},
			{title:'添加时间',field:'date_added',align:'center'},
			{title:'修改人',field:'by_modified',align:'center'},
			{title:'修改时间',field:'date_modified',align:'center'}
		]],
		toolbar:[
			{text:'添加选项',iconCls:'icon-add',plain:true,
				handler:function(){
					$('#option-dlg').dialog('open').dialog('setTitle','添加选项');
					url='<?php echo $this->getUrl('product/option/optionAdd'); ?>';
				}
			},
			'-',
			{text:'修改选项',iconCls:'icon-edit',
				handler:function(){
					var row=$('#option-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$('#option-dlg').dialog('open').dialog('setTitle','编辑选项');
						$('#option-fm').form('load','<?php echo $this->getUrl('product/option/optionSelect', true); ?>option_id='+row.option_id);
						url='<?php echo $this->getUrl('product/option/optionUpdate', true); ?>option_id='+row.option_id;
					}
				}
			},
			'-',
			{text:'删除选项',iconCls:'icon-remove',plain:true,
				handler:function(){
					var row=$('#option-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要删除选项<font color="red">'+row.name+'</font>吗？',function(r){
							if(r){
								$.post(
									'<?php echo $this->getUrl('product/option/optionDel'); ?>',
									{option_id:row.option_id},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#option-dg').datagrid('clearSelections');
											$('#option-dg').datagrid('reload');
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

	$('#option-dlg').dialog({
		width:290,
		closed:true,
		modal:true,
		top:58,
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
										$.messager.show({title:'失败',msg:result.msg.join("<br>")});
									}else{
										$.messager.show({title:'成功',msg:result.msg.join("<br>")});
										$('#option-dlg').dialog('close');
										$('#option-dg').datagrid('reload');
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
});

function showValue(id, name)
{
	if(id){
		parent.window.addTab('选项值管理:'+name,'<?php echo $this->getUrl('product/option/value', true); ?>option_id='+id,'icon-info');
	}
}
</script>
</body>
</html>