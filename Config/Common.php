<?php
/*****************************************************/
/*****************************************************/
/*                                                   */
/*               84PHP-www.84php.com                 */
/*                                                   */
/*****************************************************/
/*****************************************************/

/*
  本框架为免费开源、遵循Apache2开源协议的框架，但不得删除此文件的版权信息，违者必究。
  This framework is free and open source, following the framework of Apache2 open source protocol, but the copyright information of this file is not allowed to be deleted,violators will be prosecuted to the maximum extent possible.

  ©2017-2020 Bux. All rights reserved.

  框架版本号：4.0.0
*/

//框架配置项

define('FrameworkConfig',array(
	//调试模式
	'Debug'=>TRUE,
	//路由模式（'BASE'或'PATH'或'MIX'）
	'Route'=>'MIX',
	
	//强制HTTPS,
	'Https'=>FALSE,
	//超时时限（秒）
	'RunTimeLimit'=>FALSE,
	//安全码
	'SafeCode'=>'cfchcggcychcgmhcmchgm',
	//SESSION自动开启
	'SessionStart'=>FALSE,
	//时区
	'TimeZone'=>'Asia/Shanghai',
	//X-Powered-By隐藏
	'XPoweredBy'=>'ASP.NET',
	//始终返回200状态码
	'Always200'=>TRUE
));