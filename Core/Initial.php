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

  框架版本号：3.0.0
*/
if($_SERVER['REQUEST_METHOD']=='OPTIONS'){
	die('OPTIONS request blocked by framework.');
}

define('Runtime',microtime(TRUE));
define('RootPath',substr(str_replace(array('\\','//'),'/',dirname(__FILE__)),0,-5));
$_SERVER['84PHP_MODULE']=array();
$_SERVER['84PHP_CONFIG']=array();
$_SERVER['84PHP_LOG']='';

require(RootPath.'/Config/Common.php');
date_default_timezone_set(FrameworkConfig['TimeZone']);

if(FrameworkConfig['RunTimeLimit']!==FALSE){
	set_time_limit(FrameworkConfig['RunTimeLimit']);
}

require(RootPath.'/Core/Class/Base/Wrong.Class.php');
$_SERVER['84PHP_MODULE']['Wrong']=new Wrong;

if(FrameworkConfig['Https']){
	if(isset($_SERVER['HTTPS'])){
		Wrong::Report(__FILE__,__LINE__,'Error#C.1.0');
	}
	if($_SERVER['HTTPS']==''||$_SERVER['HTTPS']=='off'){
		header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	}
}

if(FrameworkConfig['Route']!='BASE'&&FrameworkConfig['Route']!='PATH'&&FrameworkConfig['Route']!='MIX'){
	Wrong::Report(__FILE__,__LINE__,'Error#C.1.1');
}

if(FrameworkConfig['SessionStart']&&!isset($_SESSION)){
	require(RootPath.'/Core/Class/Base/Session.Class.php');
	$_SERVER['84PHP_MODULE']['Session']=new Session;
}

header('X-Powered-By: '.FrameworkConfig['XPoweredBy']);


if(!FrameworkConfig['Debug']){
	error_reporting(0);
}
else{
	header('Cache-Control: no-cache,must-revalidate');
	header('Pragma: no-cache');
	header("Expires: -1");
	header('Last-Modified: '.gmdate('D, d M Y 00:00:00',Runtime).' GMT');
}

// 错误处理
set_error_handler('SystemErrorHandler', E_ALL | E_STRICT);
function SystemErrorHandler($ErrorNo,$ErrorMsg,$ErrorFile,$ErrorLine) {
	if(error_reporting()==0){
		return TRUE;
	}
	$PHPSystemError='Error#C.0.2 @ ';
	
	switch ($ErrorNo) {
		case E_WARNING:
			$PHPSystemError.='PHP Warning: ';
			break;
		case E_NOTICE:
			$PHPSystemError.='PHP Notice: ';
			break;
		case E_DEPRECATED:
			$PHPSystemError.='PHP Deprecated: ';
			break;
		case E_USER_ERROR:
			$PHPSystemError.='User Error: ';
			break;
		case E_USER_WARNING:
			$PHPSystemError.='User Warning: ';
			break;
		case E_USER_NOTICE:
			$PHPSystemError.='User Notice: ';
			break;
		case E_USER_DEPRECATED:
			$PHPSystemError.='User Deprecated: ';
			break;
		case E_STRICT:
			$PHPSystemError.='PHP Strict: ';
			break;
		default:
			$PHPSystemError.='Unkonw Type Error: ';
			break;
	}
 
	$PHPSystemError.=$ErrorMsg.' in '.$ErrorFile.' on '.$ErrorLine;
	Wrong::Report('','',$PHPSystemError);
	return TRUE;
}

//快捷传参
function QuickParamet($UnionData,$File,$Line,$ModuleName,$MethodName,$Name,$Dialect,$Must=TRUE,$Default=NULL){
	if(isset($UnionData[$Name])){
		return $UnionData[$Name];
	}
	else if(isset($UnionData[$Dialect])){
		return $UnionData[$Dialect];
	}
	else if(isset($UnionData[strtolower($Name)])){
		return $UnionData[strtolower($Name)];
	}
	else if(isset($UnionData[strtoupper($Name)])){
		return $UnionData[strtoupper($Name)];
	}
	else if(isset($UnionData[mb_convert_case($Dialect,MB_CASE_LOWER,'UTF-8')])){
		return $UnionData[mb_convert_case($Dialect,MB_CASE_LOWER,'UTF-8')];
	}
	else if(isset($UnionData[mb_convert_case($Dialect,MB_CASE_UPPER,'UTF-8')])){
		return $UnionData[mb_convert_case($Dialect,MB_CASE_UPPER,'UTF-8')];
	}
	else if($Must){
		$ErrorMsg='Error#C.0.5 @ '.$ModuleName.'->'.$MethodName.'() @ '.$Name.'（'.$Dialect.'）';
		Wrong::Report($File,$Line,$ErrorMsg);
	}
	else{
		return $Default;
	}
}

//获取绝对路径
function AddRootPath($Path,$Prefix=''){
	$Path=str_replace(array('\\','//'),array('/','/'),$Path);
	if(substr($Path,0,1)=='/'){
		$Path=substr($Path,1);
	}
	if(substr($Path,-1,1)=='/'){
		$Path=substr($Path,0,-1);
	}
	if(substr($Path,0,strlen(RootPath))!=RootPath){
		if(!empty($Prefix)){
			$Path=RootPath.$Prefix.'/'.$Path;
		}
		else{
			$Path=RootPath.'/'.$Path;
		}
	}
	
	return $Path;
}

//方法不存在
function MethodNotExist($ModuleName,$MethodName){
	$ErrorMsg='Error#C.0.6 @ '.$ModuleName.'->'.$MethodName.'()';
	Wrong::Report(__FILE__,__LINE__,$ErrorMsg);
}

//自动载入文件并实例化
function LoadModule($Module,$Type){
	if(!isset($_SERVER['84PHP_MODULE'][$Module])){
		require(RootPath.'/Core/Class/'.$Type.'/'.$Module.'.Class.php');
		$_SERVER['84PHP_MODULE'][$Module]=new $Module;
	}
}

//缓冲区控制开启
ob_start();