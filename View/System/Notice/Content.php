<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo $this->getLang(); ?>" lang="<?php echo $this->getLang(); ?>">
<head>
	<title>系统公告</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_CSS_URL; ?>styles.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/ui-cupertino/easyui.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>easyui/themes/icon.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo APP_JS_URL; ?>ueditor/themes/default/css/ueditor.css" />
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>jquery.min-2.0.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/jquery.easyui.min-1.3.6.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/extension/portal/jquery.portal.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>easyui/locale/easyui-lang-<?php echo $this->getLocale(); ?>.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>ueditor/ueditor.config.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>ueditor/ueditor.all.min.js"></script>
	<script type="text/javascript" src="<?php echo APP_JS_URL; ?>ueditor/lang/zh-cn/zh-cn.js"></script>
</head>
<body>
<form id="notice-fm" method="post" style="display:block;">
<div id="notice-ptl">
	<div style="width:100%">
		<ul class="form-list">
			<li class="wide">
				<div class="field">
					<label for="notice-title">公告标题:<em>*</em></label>
					<div class="input-box">
						<input type="text" class="input-text" name="title" id="notice-title" value="<?php echo isset($data['title']) ? $data['title'] : ''; ?>" />
					</div>
				</div>
			</li>
			<li class="wide">
				<div class="field">
					<label for="notice-content">公告内容:<em>*</em></label>
				</div>
			</li>
			<li class="control">
				<textarea name="content" id="notice-content"><?php echo isset($data['content']) ? $data['content'] : ''; ?></textarea>
			</li>
			<li class="buttons-set">
				<a href="javascript:void(0)" class="easyui-linkbutton" onclick="save('<?php echo isset($data['notice_id']) ? 'update' : 'add'; ?>')" data-options="iconCls:'icon-ok'">保存</a>
				&nbsp;&nbsp;&nbsp;
				<a href="javascript:void(0)" class="easyui-linkbutton" onclick="window.location.replace(window.location.href)" data-options="iconCls:'icon-reload'">重置</a>
			</li>
		</ul>
	</div>
</div>
</form>
<script type="text/javascript">
$(function(){
	$('#notice-ptl').portal({
		border:false,
		fit:true
	});

	$('#notice-fm').form('disableValidation');

	//实例化编辑器
	var width = $('body').width();
	var ue = UE.getEditor('notice-content', {
		initialFrameWidth: width * 0.9 //设置编辑器宽度
	});
});
function save(action){
	var url='<?php echo $this->getUrl('system/notice/add'); ?>';
	if (action == 'update') {
		url='<?php echo $this->getUrl('system/notice/update', true) . 'notice_id=' . (isset($data['notice_id']) ? $data['notice_id'] : 0); ?>';
	}
	$.messager.confirm('确认','您确认想保存公告吗？',function(r){
		if(r){
			$('#notice-fm').form('enableValidation');
			$('#notice-fm').form('submit',{
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
						setTimeout('window.location.replace(window.location.href)',3000);
					}
				}
			});
		}
	});
}
</script>
</body>
</html>