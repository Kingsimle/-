//夜间模式
(function(){
    if(document.cookie.replace(/(?:(?:^|.*;\s*)night\s*\=\s*([^;]*).*$)|^.*$/, "$1") === ''){
        if(new Date().getHours() > 22 || new Date().getHours() < 6){
            document.body.classList.add('night');
            document.cookie = "night=1;path=/";
            console.log('夜间模式开启');
        }else{
            document.body.classList.remove('night');
            document.cookie = "night=0;path=/";
            console.log('夜间模式关闭');
        }
    }else{
        var night = document.cookie.replace(/(?:(?:^|.*;\s*)night\s*\=\s*([^;]*).*$)|^.*$/, "$1") || '0';
        if(night == '0'){
            document.body.classList.remove('night');
        }else if(night == '1'){
            document.body.classList.add('night');
        }
    }
})();
//夜间模式切换
function switchNightMode(){
    var night = document.cookie.replace(/(?:(?:^|.*;\s*)night\s*\=\s*([^;]*).*$)|^.*$/, "$1") || '0';
    if(night == '0'){
        document.body.classList.add('night');
        document.cookie = "night=1;path=/"
        console.log('夜间模式开启');
    }else{
        document.body.classList.remove('night');
        document.cookie = "night=0;path=/"
        console.log('夜间模式关闭');
    }
}
//切换色彩模式图标
$(function(){
  var secaimoshi = 0;
  $("#colorSwitch").on('click',function(){
	  
	  if(secaimoshi == 0){
		 $(".colorSwitch").attr("class","colorSwitch fas fa-moon-stars");
		 secaimoshi = 1;
	  }else{
		 $(".colorSwitch").attr("class","colorSwitch fad fa-sun");
		 secaimoshi = 0;
	  }
	  
  })
})

//底部百度广告phone端隐藏
var os = function (){
	var ua = navigator.userAgent,
	isWindowsPhone = /(?:Windows Phone)/.test(ua),
	isSymbian = /(?:SymbianOS)/.test(ua) || isWindowsPhone,
	isAndroid = /(?:Android)/.test(ua),
	isFireFox = /(?:Firefox)/.test(ua),
	isChrome = /(?:Chrome|CriOS)/.test(ua),
	isTablet = /(?:iPad|PlayBook)/.test(ua) || (isAndroid && !/(?:Mobile)/.test(ua)) || (isFireFox && /(?:Tablet)/.test(ua)),
	isPhone = /(?:iPhone)/.test(ua) && !isTablet,
	isPc = !isPhone && !isAndroid && !isSymbian;
	return {
		isTablet: isTablet,
		isPhone: isPhone,
		isAndroid: isAndroid,
		isPc: isPc
	};	
}();
if (os.isAndroid || os.isPhone) {   
	// 手机
	$("._dsuwubbq5h").hide();
} else if (os.isTablet) {
	// 平板
	$("._dsuwubbq5h").hide();
} else if (os.isPc) {
	// pc
	
}