/*
	极速测试体验，用于审查元素时直接执行的
	1. 随便打开一个网页
	2. 右键-审查元素
	3. 粘贴入一下代码：
		var head= document.getElementsByTagName('head')[0];  var script= document.createElement('script');  script.type= 'text/javascript';  script.src= 'https://res.zvo.cn/translate/inspector_v2.js';  head.appendChild(script); 
	4. Enter 回车键 ， 执行
	5. 在当前网页的左上角，就出现了一个大大的切换语言了	
	
	使用的是 v2.x 版本进行的翻译
 */
 function getcookie2(objname){
    var arrstr = document.cookie.split("; ");
    for(var i = 0;i < arrstr.length;i ++){
        var temp = arrstr[i].split("=");
        if(temp[0] == objname){
            return unescape(temp[1]);
        }
    }
    return -1;
}

var head= document.getElementsByTagName('head')[0]; 
var script= document.createElement('script'); 
script.type= 'text/javascript'; 
script.src= 'https://www.gogo198.net/js/res.zvo.cn_translate_translate.js?v=3211122v'; 
script.onload = script.onreadystatechange = function() {
	translate.storage.set('to','');
	//设置使用v2.x 版本
	translate.setUseVersion2(); 
    // translate.selectLanguageTag.languages = 'chinese_simplified,chinese_traditional,english,japanese,german,corsican,guarani,hausa,welsh,gongen,aymara,french,haitian_creole,czech,hawaiian,dogrid,russian,thai,armenian,persian,hmong,dhivehi,bhojpuri,turkish,hindi,belarusian,bulgarian,twi,irish,gujarati,hungarian,estonian,arabic,bengali,azerbaijani,portuguese,Cebuano,afrikaans,kurdish_sorani,greek,spanish,frisian,danish,amharic,bambara,basque,vietnamese,korean,assamese,catalan,finnish,ewe,croatian,scottish-gaelic,bosnian,galician,';
    // var lang = 'chinese_simplified';
    // if(getcookie2('c_language')!=-1){
    //     lang = getcookie2('c_language');
    //     translate.changeLanguage(lang);
    // }else{
    //     console.log('无设置cookie');
    // }
    // console.log(lang);
    
    // translate.setAutoDiscriminateLocalLanguage();
    //更换字体大小
    var timeZoneOffset = new Date().getTimezoneOffset();
    var userLang = navigator.language || navigator.userLanguage;

    if(userLang!='zh-CN' && userLang!='zh-TW' && userLang!='zh-HK' && userLang!='zh-SG' && userLang!='zh-MO'){
        $('.f15').removeClass('f15').addClass('f13');
        $('.f16').removeClass('f16').addClass('f15');
        $('.f18').removeClass('f18').addClass('f16');
        $('.f22').removeClass('f22').addClass('f18');
        $('.f26').removeClass('f26').addClass('f22');
    }
    
    translate.selectLanguageTag.show = true;
	//SELECT 修改 onchange 事件
	translate.selectLanguageTag.selectOnChange = function(event){
		//判断是否是第一次翻译，如果是，那就不用刷新页面了。 true则是需要刷新，不是第一次翻译
		var isReload = translate.to != null && translate.to.length > 0;
		var language = event.target.value;
		document.cookie = 'c_language='+language;
		translate.changeLanguage(language);
		//更换字体大小
        if(language!='chinese_simplified' && language!='chinese_traditional'){
            $('.f15').removeClass('f15').addClass('f13');
            $('.f16').removeClass('f16').addClass('f15');
            $('.f18').removeClass('f18').addClass('f16');
            $('.f22').removeClass('f22').addClass('f18');
            $('.f26').removeClass('f26').addClass('f22');
        }
	}
	
	translate.listener.start();
	translate.execute();

	document.getElementById('translate').style.color = 'black';
	document.getElementById('translate').style.zIndex = '9999999999999';

	setInterval(function() {
		try{
			if(document.getElementById('translateSelectLanguage') == null){
				return;
			}
			document.getElementById('translateSelectLanguage').style.fontSize = '16px';
			document.getElementById('translateSelectLanguage').style.borderWidth = '1px';
			document.getElementById('translateSelectLanguage').style.borderColor = 'black';
		}catch(e){
			//select数据是通过接口返回的
		}
	},1000);
}
head.appendChild(script); 


