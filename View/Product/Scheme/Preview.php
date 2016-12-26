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
	<script type="text/javascript">var schemeId='<?php echo $schemeId; ?>';</script>
	<script type="text/javascript">var groupId='<?php echo $group['group_id']; ?>';</script>
	<script type="text/javascript">var categoryId='<?php echo $category['category_id']; ?>';</script>
</head>
<body class="easyui-layout">
<div data-options="region:'west',title:'方案预览:<?php echo $group['name'] . '(ID:' . $group['group_id'] . ')'; ?>',width:'250',collapsible:false">
	<div class="easyui-panel" style="padding:5px;position:fixed;border:none;">
		<a id="treeOperate" href="javascript:;" class="easyui-linkbutton" onclick="treeOperate()" data-options="iconCls:'icon-tip'" data-value="展开" >展开</a>
	</div>
	<ul id="category-tree" style="padding-top:38px"></ul>
</div>
<div id="panelDiv" data-options="region:'center',title:'<?php echo sprintf('分类:%s(ID:%s):产品信息', str_replace("'", "\'", $category['name']), $category['category_id']); ?>'">
	<div title="产品信息">
		<table id="categoryProduct-dg"></table>
	</div>
</div>
<ul class="datagrid-filter">
	<li class="fields last">
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
				<select id="filter-is_master">
					<option value="">全部</option>
					<option value="1">是</option>
					<option value="0">否</option>
				</select>
			</div>
		</div>
	</li>
</ul>
<script type="text/javascript">
var url;
$(function(){
	$('#category-tree').tree({
		animate:true,
		lines:true,
		data:<?php echo json_encode($data); ?>,
		onClick: function(node){
			window.location.replace('<?php echo $this->getUrl('product/scheme/preview', true) ; ?>group_id='+groupId+'&category_id='+node.id+'&scheme_id='+schemeId);
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

	$('#category-fm').form('disableValidation');
	$('#category-tabs').tabs();
});

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

$(function(){
	$('#categoryProduct-dg').datagrid({
		height:$('#panelDiv').height(),
		border:false,
		rownumbers:true,
		singleSelect:true,
		striped:true,
		pagination:true,
		pageSize:25,
		pageList:[25,50,100],
		url:'<?php echo $this->getUrl('product/scheme/getCategoryToProduct', true); ?>&group_id='+groupId+'&category_id='+categoryId+'&scheme_id='+schemeId,
		idField:'product_id',
		columns:[[
			{field:'product_id',hidden:true},
			{title:'图片',field:'image',align:'left',width:'108',
				formatter:function(val){
					return '<img src="<?php echo APP_HTTP . 'images/product/'; ?>'+val+'" alt="" width="100" height="100" onerror="javascript:this.src=\'<?php echo APP_HTTP; ?>images/no_image.jpg\'" />';
				}
			},
			{title:'产品型号',field:'sku',align:'center'},
			{title:'产品名称',field:'name',align:'center'},
			{title:'分类',field:'category',align:'center'},
			{title:'属性',field:'attributes',align:'left'},
			{title:'价格',field:'prices',align:'left'},
			{title:'选项',field:'options',align:'left'},
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
			{text:'刷新',iconCls:'icon-reload',
				handler:function(){
					$('#categoryProduct-dg').datagrid('reload');
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
	$('#categoryProduct-dg').datagrid('load',{
		filter:{
			status:$('#filter-status').val(),
			is_master:$('#filter-is_master').val()
		}
	});
}
</script>
</body>
</html>