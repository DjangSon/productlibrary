<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>系统公告</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
</head>
<body>
<table id="notice-dg"></table>
<script type="text/javascript">
var url;
$(function(){
	$('#notice-dg').datagrid({
		title:'系统公告',
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('system/notice/list'); ?>',
		idField:'notice_id',
		columns:[[
			{field:'notice_id',hidden:true},
			{title:'公告标题',field:'title',align:'left'},
			{title:'添加人',field:'by_added',align:'center'},
			{title:'添加时间',field:'date_added',align:'center'},
			{title:'修改人',field:'by_modified',align:'center'},
			{title:'修改时间',field:'date_modified',align:'center'},
			{title:'状态',field:'status',align:'center',
				formatter:function(val,row){if(val=='0'){return '[ <a href="javascript:void(0)" onclick="isStatus(\''+row.notice_id+'\',1)"><font color="red">禁用</font></a> ]';}else if(val=='1'){return '[ <a href="javascript:void(0)" onclick="isStatus(\''+row.notice_id+'\',0)"><font color="green">启用</font></a> ]';}}
			},
			{title:'顶置',field:'is_popup',align:'center',
				formatter:function(val,row){if(val=='0'){return '[ <a href="javascript:void(0)" onclick="isPopup(\''+row.notice_id+'\')">顶置</a> ]';}else if(val=='1'){return '[ <a href="javascript:void(0)" onclick="noPopup(\''+row.notice_id+'\')"><font color="green">取消顶置</font></a> ]';}}
			}
		]],
		toolbar:[
			{text:'添加公告',iconCls:'icon-add',
				handler:function(){
					parent.window.addTab('添加公告','<?php echo $this->getUrl('system/notice/content'); ?>','icon-info');
				}
			},
			'-',
			{text:'修改公告',iconCls:'icon-edit',
				handler:function(){
					var row=$('#notice-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						parent.window.addTab('编辑公告:'+row.title,'<?php echo $this->getUrl('system/notice/content', true); ?>notice_id='+row.notice_id,'icon-info');
					}
				}
			},
			'-',
			{text:'删除公告',iconCls:'icon-remove',plain:true,
				handler:function(){
					var row=$('#notice-dg').datagrid('getSelected');
					if(row==null){
						$.messager.alert('提示','请选择数据','info');
					}else{
						$.messager.confirm('确认','您确认想要删除公告<font color="red">'+row.title+'</font>吗？',function(r){
							if(r){
								$.post(
									'<?php echo $this->getUrl('system/notice/del'); ?>',
									{notice_id:row.notice_id},
									function(result){
										if(result.error){
											$.messager.show({title:'失败',msg:result.msg.join("<br>")});
										}else{
											$.messager.show({title:'成功',msg:result.msg.join("<br>")});
											$('#notice-dg').datagrid('clearSelections');
											$('#notice-dg').datagrid('reload');
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
});

function isStatus(id, status) {
	if(id){
		var temStr;
		if(status){
			temStr = '启用';
		}else{
			temStr = '禁用';
		}
		$.messager.confirm('确认','您确认想要'+temStr+'吗？',function(r){
			if(r){
				$.post(
					'<?php echo $this->getUrl('system/notice/isStatus'); ?>',
					{id:id,status:status},
					function(result){
						if(result.error){
							$.messager.show({title:temStr+'失败',msg:result.msg.join("<br>")});
						}else{
							$.messager.show({title:temStr+'成功',msg:result.msg.join("<br>")});
							$('#notice-dg').datagrid('reload');
						}
					},
					'json'
				);
			}
		});
	}
}

function isPopup(id) {
	if(id){
		$.messager.confirm('确认','您确认想要顶置该文章吗？',function(r){
			if(r){
				$.post(
					'<?php echo $this->getUrl('system/notice/isPopup'); ?>',
					{id:id},
					function(result){
						if(result.error){
							$.messager.show({title:'顶置失败',msg:result.msg.join("<br>")});
						}else{
							$.messager.show({title:'顶置成功',msg:result.msg.join("<br>")});
							$('#notice-dg').datagrid('reload');
						}
					},
					'json'
				);
			}
		});
	}
}

function noPopup(id) {
	if(id){
		$.messager.confirm('确认','您确认想要取消顶置该文章吗？',function(r){
			if(r){
				$.post(
					'<?php echo $this->getUrl('system/notice/noPopup'); ?>',
					{id:id},
					function(result){
						if(result.error){
							$.messager.show({title:'取消失败',msg:result.msg.join("<br>")});
						}else{
							$.messager.show({title:'取消成功',msg:result.msg.join("<br>")});
							$('#notice-dg').datagrid('reload');
						}
					},
					'json'
				);
			}
		});
	}
}
</script>
</body>
</html>