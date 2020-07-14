<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no,minimal-ui">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
<meta content="email=no" name="format-detection" />
<meta content="telephone=no" name="format-detection" />
<meta name="screen-orientation" content="portrait">
<meta name="browsermode" content="application">
<meta name="x5-orientation" content="portrait">
<title>出错了</title>
<style>
	*{
		touch-action: manipulation;
	}

	@keyframes hello {
		0% {
			opacity: 0;
		}
		100% {
			opacity: 1;
		}
	}
	@keyframes byebye {
		0% {
			opacity: 1;
		}
		100% {
			opacity: 0;
		}
	}

	html{
		height:100%;
		width:100%;
		font-size:6.25%;
		overflow-x:hidden;
		-webkit-user:none;
		user:none; 
	}
	body{
		height:100%;
		width:100%;
		margin:0px;
		background-color:#f3f5fa;
		font-family:"Microsoft YaHei", 微软雅黑, "Microsoft JhengHei";
		color:#333;
		position:relative;
		-webkit-overflow-scrolling:touch;
		-webkit-user:none;
		user:none;
		font-size: 16rem;
		overflow-x: hidden;
	}
	a{
		text-decoration:none;
		color:inherit;
	}
	a:visited{
		color:inherit;
	}
	a img{
		border:none;
	}
	input, select, textarea{
		font-family:"Microsoft YaHei", 微软雅黑, "Microsoft JhengHei";
		box-sizing:content-box;
		text-align:center;
		-webkit-appearance:none;
		border-radius: 0;
		outline: 0
	}
	input:hover{
		border: none
	}
	option{
		text-align:center;
	}
	ul{
		list-style:none;
	}
	.hello{
		animation:hello 0.5s;
		-webkit-animation:hello 0.5s;
		animation-fill-mode:forwards;
		-webkit-animation-fill-mode:forwards;
	}
	.byebye{
		animation:byebye 0.5s;
		-webkit-animation:byebye 0.5s;
		animation-fill-mode:forwards;
		-webkit-animation-fill-mode:forwards;
	}
	.displaynone{
		display: none;
	}
	.IEBrowser-bg{
		width: 100%;
		height: 100%;
		position: fixed;
		left: 0;
		top:0;
		z-index: 998;
		background-color: #24273d;
		filter:alpha(opacity=80)
	}
	.IEBrowser{
		width: 100%;
		height: 100%;
		position: fixed;
		left: 0;
		top:0;
		z-index: 999;
		color: #ffffff;
		display: table
	}
	.IEBrowser-box{
		width: 100%;
		height: 100%;
		display: table-cell;
		vertical-align: middle;
		text-align: center;
		line-height: 1.5;
		font-size: 18px;
	}
	.IEBrowser-box b{
		color: #f53840
	}

	.main{
		width:100%;
		height: 100%;
		max-width: 1280px;
		position: relative;
		margin: auto;
		display: flex;
		flex-direction: column;
		align-items: center;
	}
	.main-icon{
		width: 100%;
		height: 19%;
		display: flex;
		justify-content: flex-end;
		align-items: center;
		flex-direction: column;
		margin-top: 10%
	}
	.main-icon-img{
		width: 13em;
	}
	.main-notice{
		width: 90%;
		margin: 6% 0 5% 0;
		border: none;
		font-size: 24rem;
		line-height: 1;
		padding: 1.3% 0;
		box-sizing: border-box;
		color:#333;
		text-align: center
	}
	.main-title{
		width: 80%;
		margin: 0 auto;
		margin-bottom: 2%;
		display: flex;
		align-items: center;
		flex-direction: column;
	}
	.main-title-box{
		width: 100%;
		border-bottom: 1px solid #e9e9e9;
		display: flex;
		justify-content: center
	}
	.main-title-box-text{
		margin: 0.5% 1% 0 1%;
		padding: 0.5%;
		padding-bottom: 0.7%;
		font-size: 16rem;
		position: relative;
		flex-shrink: 0;
		color: #333;
		font-weight: bold;
	}
	.main-title-box-text-line{
		width: 100%;
		position: absolute;
		left: 0;
		bottom: 0;
		height: 2px;
		width: 100%;
		background-color: #3377ff;
	}
	.main-detail{
		width: 100%;
		height: 0;
		flex-grow: 1;
		position: relative;
		margin-bottom: 3%;
	}
	.main-detail-code{
		height: 100%;
		width: 100%;
		position: absolute;
		left: 0;
		top: 0;
		z-index: 1;
		line-height: 1;
		background-color: #fff;
		color: #ccc;
		font-size: 22rem;
		padding: 5%;
		box-sizing: border-box
	}
	@media screen and (max-width: 960px){
		body{
			display: flex;
			justify-content: center;
			align-items: center;
		}
		.main-icon{
			height: 18%
		}
		.main-icon-img{
			width: 15.8em;
			height: 5em;
		}
		.main-notice{
			width: 96%;
		}
		.main-notice-text-box{
			width: 90%;
			position: relative;
			padding: 0 3.5%;
			padding-right: 1.5%;
		}
		.main-notice-text{
			width: 90%;
			font-size: 18rem;
			padding: 1.5% 0%;
		}
		.main-title{
			width: 96%
		}
		.main-title-box{
			height: 100%;
			justify-content: space-between
		}
		.main-title-box::before, .main-title-box::after{
			content: '';
		}
		.main-title-box-text{
			font-size: 14rem;
			margin: 0.5% 1.6% 0 1.6%;
			padding-bottom: 1.2%;
			white-space:nowrap;
		}
		.main-detail{
			width: 94%;
			margin: auto;
			margin-bottom: 3%;
		}
	}
</style>
</head>
<body>
	<!--[if IE]>
	<div class="IEBrowser-bg"></div>
	<div class="IEBrowser">
		<div class="IEBrowser-box">
<h1>页面无法正确被显示</h1><br><span>您可能使用了老旧的IE浏览器或现代浏览器的兼容模式。</span><br>请尝试关闭兼容模式，或使用如 <b>Chrome</b> 、<b>FireFox</b> 、<b>360极速浏览器</b> 等现代浏览器访问。<br>很抱歉给您带来不便，如需帮助请联系 cs@bux.cn。
		</div>
	</div>
	<![endif]-->
	<div class="note" id="note"></div>
	<div class="main">
		<div class="main-icon">
			<svg class="main-icon-img" data-name="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1819 1920.47"><defs><style>.cls-6{fill:#fed15c}</style></defs><path d="M933.19 421.61h-48a459.28 459.28 0 0 0-207 49.11v1253.67a459.41 459.41 0 0 0 207 49.11h48c255 0 463.55-208.6 463.55-463.55V885.16c0-254.95-208.6-463.55-463.55-463.55z" fill="#f3715b"/><path d="M421.69 885.16V1310c0 180.69 104.79 338.07 256.58 414.44 12.94-166.78 27.42-306.58 39.21-410.07 51-448.21 92.95-579.19 167.65-699a912.59 912.59 0 0 1 150-182.33 652.9 652.9 0 0 0-117.58-11c-41.6-.1-115.87-.29-197 30-139.76 52.25-212.18 162.43-227.5 186.78-64.5 102.6-71.05 202.72-71.36 246.34z" fill="#ffa194"/><rect x="501.98" y="738.44" width="100.41" height="636.23" rx="50.21" fill="#fff"/><path d="M1724.57 1568.88v6.46a1690.83 1690.83 0 0 0-228.91-108.14c-131.48-50.4-586.52-217.28-1091-38.51-83.21 29.47-192 77-310.77 155.88v-15.69c0-161 130.51-291.5 291.51-291.5h1047.66c161 0 291.51 130.51 291.51 291.5z" fill="#576b7c"/><path d="M1724.57 1575.34v345.13H93.86v-335.9c118.76-78.93 227.56-126.41 310.77-155.88 504.51-178.77 959.55-11.89 1091 38.51a1690.83 1690.83 0 0 1 228.94 108.14z" fill="#33495e"/><rect class="cls-6" y="923" width="264" height="61" rx="30.5"/><rect class="cls-6" x="1555" y="923" width="264" height="61" rx="30.5"/><rect class="cls-6" x="1327.06" y="329.44" width="264" height="61" rx="30.5" transform="rotate(-45 1459.064 359.946)"/><rect class="cls-6" x="777.21" y="101.5" width="264" height="61" rx="30.5" transform="rotate(-90 909.21 132)"/><path fill="none" d="M564.45 564L452.72 452.28"/><rect class="cls-6" x="227.54" y="328.75" width="264" height="61" rx="30.5" transform="rotate(-135 359.542 359.248)"/></svg>
		</div>
		<div class="main-notice">此页面出现错误，请稍后重试</div>
		<div class="main-title">
			<div class="main-title-box">
				<div class="main-title-box-text">错误详情
					<div class="main-title-box-text-line">{$ErrorInfo}</div>
				</div>
			</div>
		</div>
		<div class="main-detail">
			<div class="main-detail-code">文档加载中...</div>
		</div>
	</div>
</body>
<script>
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
</script>
</html>