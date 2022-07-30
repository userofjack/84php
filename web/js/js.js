var DeBug=true;

var SWidth=window.screen.availWidth;
var SHeight=window.screen.availHeight;
var WWidth=document.getElementsByTagName("html")[0].clientWidth;
var WHeight=document.getElementsByTagName("html")[0].clientHeight;
var Html=document.getElementsByTagName("html")[0];

window.onload=function(){
	if(navigator.userAgent.toLowerCase().indexOf("trident")>-1&&navigator.userAgent.indexOf("Opera")<=-1){
		ByClass('common-dialog').style.opacity=1;
		ById('common-dialog').setAttribute('style','justify-content:flex-start');
	}
}

if(SWidth<960){
	if(SHeight/SWidth>1.7){
		var FontScale=SWidth/414;
	}
	else{
		var FontScale=SWidth/627;
	}
}
else if(SWidth<1300){
	var FontScale=SWidth/870;
}
else{
	var FontScale=1;
}
Html.style.fontSize=6.25*FontScale+'%';