<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>系统登录</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
</head>
<body>
<table id="login-dg"></table>
<ul class="datagrid-filter">
	<li class="fields last">
		<div class="field">
			<label for="filter-account">登录账户:</label>
			<div class="input-box">
				<input type="text" class="input-text" id="filter-account" />
			</div>
		</div>
		<div class="field">
			<label for="filter-date_start">起始日期:</label>
			<div class="input-box">
				<input type="text" class="input-text easyui-datebox" id="filter-date_start" data-options="width:111,value:'<?php echo date('Y-m-d', strtotime('-2 week')); ?>',editable:false" />
			</div>
		</div>
		<div class="field">
			<label for="filter-date_end">终止日期:</label>
			<div class="input-box">
				<input type="text" class="input-text easyui-datebox" id="filter-date_end" data-options="width:111,value:'<?php echo now(true); ?>',editable:false" />
			</div>
		</div>
	</li>
</ul>
<script type="text/javascript">
$(function(){
	$('#login-dg').datagrid({
		title:'系统登录',
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('system/login/list'); ?>',
		queryParams:{
			filter:{
				date_added:{
					start:$('#filter-date_start').datebox('getValue'),
					end:$('#filter-date_end').datebox('getValue')
				}
			}
		},
		idField:'log_id',
		columns:[[
			{field:'log_id',hidden:true},
			{title:'登录帐号',field:'account',align:'left'},
			{title:'登录IP',field:'ip',align:'center'},
			{title:'登录结果',field:'status',align:'center',
				formatter:function(val){if(val=='1'){return '<font color="green">登录成功</font>';}else{return '<font color="red">登录失败</font>';}}
			},
			{title:'登录时间',field:'date_added',align:'center'}
		]],
		toolbar:[
			{text:'查询',iconCls:'icon-search',
				handler:function(){
					filter();
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

	$('.datagrid-filter').prependTo('.datagrid-toolbar');

	$(document).keydown(function(event){
		if(event.keyCode==13){
			filter();
		}
	});
});

function filter(){
	$('#login-dg').datagrid('load',{
		filter:{
			account:$('#filter-account').val(),
			date_added:{
				start:$('#filter-date_start').datebox('getValue'),
				end:$('#filter-date_end').datebox('getValue')
			}
		}
	});
}
</script>
</body>
</html>