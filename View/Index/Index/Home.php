<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>首页</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/extension/portal/portal.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/extension/portal/jquery.portal.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
</head>
<body>
<div id="home-ptl">
	<div style="width:100%">
		<div data-options="title:'最新公告',height:270">
			<table id="notice-dg"></table>
		</div>
		<div data-options="title:'最近30天登录情况',height:270">
			<table id="log-dg"></table>
		</div>
	</div>
</div>
<div id="notice-dlg"></div>
<script type="text/javascript">
$(function(){
	$('#home-ptl').portal({
		border:false,
		fit:true
	});
	
	$('#notice-dg').datagrid({
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:10,
		pageList:[10],
		url:'<?php echo $this->getUrl('index/index/noticeList'); ?>',
		idField:'notice_id',
		columns:[[
			{title:'公告标题',field:'title',width:535,align:'left'},
			{title:'发布时间',field:'date_added',width:150,align:'center'},
			{field:'show',width:60,align:'center',
				formatter:function(value,row,index){return '[ <a href="javascript:void(0)" onclick="showNotice('+row.notice_id+')">详情</a> ]';}
			}
		]]
	});

	$('#notice-dlg').dialog({
		title:'公告详情',
		width:590,
		height:500,
		closed:true,
		modal:true,
		top:58
	});

	$('#log-dg').datagrid({
		fit:true,
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:10,
		pageList:[10],
		url:'<?php echo $this->getUrl('index/index/loginList'); ?>',
		columns:[[
			{title:'登录帐号',field:'account',align:'left'},
			{title:'登录IP',field:'ip',align:'left'},
			{title:'登录结果',field:'status',align:'center',
				formatter:function(val){if(val=='1'){return '<font color="green">登录成功</font>';}else{return '<font color="red">登录失败</font>';}}
			},
			{title:'登录时间',field:'date_added',align:'center'}
		]]
	});
});

function showNotice(notice_id){
	if(notice_id){
		$('#notice-dlg').dialog('open');
		$('#notice-dlg').dialog('refresh','<?php echo $this->getUrl('index/index/show', true); ?>notice_id='+notice_id);
	}
}
//--></script>
</body>
</html>