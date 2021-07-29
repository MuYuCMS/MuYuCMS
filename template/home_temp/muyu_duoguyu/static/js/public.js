function checkSearch(){
	    var searchInput = $('#searchInput').val();
	    var searchVal = $.trim(searchInput);
	    if(searchVal == ''){
			layer.msg("哎呀，你好像忘记输入搜索内容了！");
			return false;
	    }
	    if(searchVal.length < 2){
			layer.msg("搜索关键字至少需要2个字哟！");
			return false;
	    }
		return true;
	};

$(function(){ 

	$("#searchselected").click(function(){ 
		$("#searchtab").toggle();
		if($(this).hasClass('searchopen')){
			$(this).removeClass("searchopen");
		}else{
			$(this).addClass("searchopen");
		}
	}); 

	$("#searchtab li").hover(function(){
		$(this).addClass("selected");
	},function(){
		$(this).removeClass("selected");
	});
	 
	$("#searchtab li").click(function(){
		$("#modelid").val($(this).attr('data') );
		$("#searchselected").html($(this).html());
		$("#searchtab").hide();
		$("#searchselected").removeClass("searchopen");
	});


	$(".yzm-nav>li").hover(function(){
		$(this).children('ul').stop(true,true).slideDown(200);
	},function(){
		$(this).children('ul').stop(true,true).slideUp(200);
	})
	
});


function toreply(obj){
    if($("#rep_" + obj).css("display") == "none"){
        $("#rep_" + obj).css("display", "block");
    }else{
        $("#rep_" + obj).css("display", "none");
    }
}



$(function(){
    scrollTop();
})
var scrollTop = function(){
	var offset = 300,
		offset_opacity = 1200,
		scroll_top_duration = 700,
		$back_to_top = $('.backTop');
	$(window).scroll(function(){
		( $(this).scrollTop() > offset ) ? $back_to_top.addClass('backTopVisible') : $back_to_top.removeClass('backTopVisible cd-fade-out');
		if( $(this).scrollTop() > offset_opacity ) { 
			$back_to_top.addClass('cd-fade-out');
		}
	});
	$back_to_top.on('click', function(event){
		event.preventDefault();
		$('body,html').animate({
			scrollTop: 0 ,
		 	}, scroll_top_duration
		);
	});
};
$(function() {
			$(".quickBtn").click(function() {
				$("html,body").animate({
					scrollTop: $('.quickDownLoad').offset().top
				}, 500)
			})
		})
		
	
        $(function(){
            $('#switchMenu, #closeMenuBtn').click(function(){
                $('#navGrid').toggleClass('on');
            });
        });

$(function () {
	$('.articleDetailGroup').find('img').each(function () {
		var _this = $(this);
		var _src = _this.attr("src");
		var _alt = _this.attr("alt");
		_this.wrap('<a data-fancybox="images" href="' + _src + '" data-caption="' + _alt + '"></a>');
	})
})


