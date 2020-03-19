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
<title>欢迎/Welcome -84PHP</title>
<script type="text/javascript" src="js/js.js"></script>
<link rel="stylesheet" href="css/style.css" type="text/css" />
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
		<div class="main-logo">
			<div class="main-logo-img" onClick="Jump('https://www.84php.com',true,true)">
				<div class="main-logo-version"><span>{$Version}</span></div>
			</div>
		</div>
		<div class="main-go">
			<form class="main-go-input-box"method="post" action="index.act">
				<input name="testinput" class="main-go-input" placeholder="随便输点啥..." autocomplete="off">
				<div class="main-go-btn">
					<div class="main-go-btn-icon" onClick="Submit()"></div>
				</div>
			</form>
		</div>
		<div class="main-guide">
			<div class="main-guide-class">
				<div class="main-guide-class-item main-guide-class-item-active" onMouseOver="Guide(this)">框架文档
					<div class="main-guide-class-item-linebox">
						<div class="main-guide-class-item-hoverline"></div>
					</div>
				</div>
				<div class="main-guide-class-item" onMouseOver="Guide(this)" onClick="Jump('https://www.84php.com',true,true)">进入官网
					<div class="main-guide-class-item-linebox">
						<div class="main-guide-class-item-hoverline"></div>
					</div>
				</div>
				<div class="main-guide-class-item" onMouseOver="Guide(this)" onClick="Jump('https://www.84php.com/qun.html',true,true)">加入QQ群
					<div class="main-guide-class-item-linebox">
						<div class="main-guide-class-item-hoverline"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="main-document">
			<div class="main-document-loading">文档加载中...</div>
			<iframe src="https://doc.bux.cn/84php"></iframe>
		</div>
	</div>
</body>
<script type="text/javascript" src="https://version.84php.com/{$Version}.js"></script>
</html>