var DeBug=true;

var SWidth=window.screen.availWidth;
var SHeight=window.screen.availHeight;
var WWidth=document.getElementsByTagName("html")[0].clientWidth;
var WHeight=document.getElementsByTagName("html")[0].clientHeight;
var Html=document.getElementsByTagName("html")[0];


function ByClass(ClassName,ReturnAll){
	var ReturnAll=arguments[1] || false;
	var Return=document.getElementsByClassName(ClassName);
	if(Return.length==0){
		return false;
	}
	if(ReturnAll){
		return Return;
	}
	return Return[0];
}

function ById(IDName,CloseError){
	var CloseError=arguments[1] || false;
	if(!document.getElementById(IDName)){
		if(DeBug&&!CloseError){
			console.log('%cById(\''+IDName+'\') Error','color:red');
		}
		return false;
	}
	return document.getElementById(IDName);
}

function Jump(Href,NewW,ParentW){
	var NewW=arguments[1] || false;
	var ParentW=arguments[2] || false;
	if(!arguments[0]){
		return false;
	}
	if(!NewW){
		if(ParentW&&self!=top){
			parent.location.href=Href;
		}
		else{
			window.location.href=Href;
		}
	}
	else{
		if(ParentW&&self!=top){
			parent.open(Href);
		}
		else{
			window.open(Href);
		}
	}
}

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

function Guide(e,Class){
	ByClass('main-guide-class-item-active').className=ByClass('main-guide-class-item-active').className.replace(' main-guide-class-item-active','');
	e.className+=' main-guide-class-item-active';
}

function Submit(){
	ByClass('main-go-input-box').submit();
}