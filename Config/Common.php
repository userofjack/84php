<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

//框架配置项

define('FrameworkConfig',[
	//调试模式
	'Debug'=>TRUE,
	//路由模式（'BASE'或'PATH'或'MIX'）
	'Route'=>'MIX',
	
	//强制HTTPS,
	'Https'=>FALSE,
	//超时时限（秒）
	'RunTimeLimit'=>FALSE,
	//安全码
	'SafeCode'=>'',
	//SESSION自动开启
	'SessionStart'=>FALSE,
	//时区
	'TimeZone'=>'Asia/Shanghai',
	//X-Powered-By隐藏
	'XPoweredBy'=>'ASP.NET',
	//始终返回200状态码
	'Always200'=>TRUE
]);