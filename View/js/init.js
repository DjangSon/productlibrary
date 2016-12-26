$(function(){
	$(".easyui-accordion li a").click(function(){
		var tabTitle=$(this).text();
		var url=$(this).attr("rel");
		var ico=$(this).attr("class");
		addTab(tabTitle,url,ico);
		$(".easyui-accordion li div").removeClass("selected");
		$(this).parent().addClass("selected");
	}).hover(function(){
		$(this).parent().addClass("hover");
	},function(){
		$(this).parent().removeClass("hover");
	});
	//关闭当前
	$("#rm-tabclose").click(function(){
		var currtab_title = $("#rightMenu").data("currtab");
		if(currtab_title!="后台首页")
			$("#tabs").tabs("close",currtab_title);
	})
	//全部关闭
	$("#rm-tabcloseall").click(function(){
		$(".tabs-inner span").each(function(i,n){
			var t=$(n).text();
			if(t!="后台首页")
				$("#tabs").tabs("close",t);
		});	
	});
	//关闭除当前之外的TAB
	$("#rm-tabcloseother").click(function(){
		var currtab_title = $("#rightMenu").data("currtab");
		$(".tabs-inner span").each(function(i,n){
			var t=$(n).text();
			if(t!=currtab_title&&t!="后台首页")
				$("#tabs").tabs("close",t);
		});	
	});
	//关闭当前右侧的TAB
	$("#rm-tabcloseright").click(function(){
		var nextall = $(".tabs-selected").nextAll();
		if(nextall.length==0){
			return false;
		}
		nextall.each(function(i,n){
			var t=$("a:eq(0) span",$(n)).text();
			if(t!="后台首页")
				$("#tabs").tabs("close",t);
		});
		return false;
	});
	//关闭当前左侧的TAB
	$("#rm-tabcloseleft").click(function(){
		var prevall = $(".tabs-selected").prevAll();
		if(prevall.length==0){
			return false;
		}
		prevall.each(function(i,n){
			var t=$("a:eq(0) span",$(n)).text();
			if(t!="后台首页")
				$("#tabs").tabs("close",t);
		});
		return false;
	});
	//退出
	$("#rm-exit").click(function(){
		$("#rightMenu").menu("hide");
	})
	//初始化右击事件
	tabClose();
});

function addTab(subtitle,url,ico){
	if(!$("#tabs").tabs("exists",subtitle)){
		$("#tabs").tabs("add",{
			title:subtitle,
			content:'<iframe scrolling="auto" frameborder="0" src="'+url+'" style="width:100%;height:99.5%;"></iframe>',
			iconCls:ico,
			closable:true
		});
	}else{
		$("#tabs").tabs("select",subtitle);
	}
	tabClose();
}

function tabClose(){
	/*双击关闭TAB选项卡*/
	$(".tabs-inner").dblclick(function(){
		var subtitle = $(this).children("span").text();
		if(subtitle!="后台首页")
			$("#tabs").tabs("close",subtitle);
	})
	
	$(".tabs-inner").bind("contextmenu",function(e){
		$("#rightMenu").menu("show",{
			left:e.pageX,
			top:e.pageY,
		});
		var subtitle=$(this).children("span").text();
		$("#rightMenu").data("currtab",subtitle);
		return false;
	});
}