<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/
define('RootPath',substr(str_replace(['\\','//'],'/',dirname(__FILE__)),0,-5));
set_include_path(get_include_path().PATH_SEPARATOR.RootPath.'/Lib/');

require(RootPath.'/Config/Common.php');

spl_autoload_register(function($ClassName){
	if(!file_exists(RootPath.'/Core/Class/'.$ClassName.'.Class.php')){
		Wrong::Report(__FILE__,__LINE__,'Error#C.0.7'."\r\n\r\n @ ".$ClassName);
	}
	else{
		require(RootPath.'/Core/Class/'.$ClassName.'.Class.php');
	}
});

if(isset($_SERVER['REQUEST_METHOD'])&&$_SERVER['REQUEST_METHOD']=='OPTIONS'){
	die('OPTIONS request blocked by framework.');
}

date_default_timezone_set(FrameworkConfig['TimeZone']);
define('Runtime',microtime(TRUE));

$_SERVER['84PHP_CONFIG']=[];
$_SERVER['84PHP_LOG']='';

if(FrameworkConfig['RunTimeLimit']!==FALSE){
	set_time_limit(FrameworkConfig['RunTimeLimit']);
}

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

if(FrameworkConfig['SessionStart']){
	Session::Start();
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

//路由
$_SERVER['84PHP_URI']='';
$_SERVER['84PHP_OptionArray']=getopt('',['path:']);
if(empty($_SERVER['84PHP_OptionArray']['path'])){
	if((FrameworkConfig['Route']=='BASE'||FrameworkConfig['Route']=='MIX')&&isset($_GET['p_a_t_h'])){
		$_SERVER['84PHP_URI']=$_GET['p_a_t_h'];
	}
	if(FrameworkConfig['Route']=='PATH'||(FrameworkConfig['Route']=='MIX'&&!isset($_GET['p_a_t_h']))){
		$_SERVER['84PHP_URI']=str_ireplace($_SERVER['SCRIPT_NAME'],'',$_SERVER['PHP_SELF']);
	}
}
else{
	$_SERVER['84PHP_URI']=$_SERVER['84PHP_OptionArray']['path'];
}
if($_SERVER['84PHP_URI']==''||$_SERVER['84PHP_URI']=='/'){
	$_SERVER['84PHP_URI']='/index';
}
define('URI',$_SERVER['84PHP_URI']);

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

//获取磁盘路径
function DiskPath($Path,$Prefix=''){
	$Path=str_replace(['\\','//'],['/','/'],$Path);
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


//加载完成后调用
$_SERVER['84PHP_LastWork']=[];
function LastWork(){
	foreach ($_SERVER['84PHP_LastWork'] as $Key => $Val){
		if($Key!=''&&$Val!=''){
			call_user_func($Key.'::'.$Val);
		}
	}
}

//方法不存在
function UnknownStaticMethod($ModuleName,$MethodName){
	$ErrorMsg='Error#C.0.6 @ '.$ModuleName.' :: '.$MethodName.'()';
	Wrong::Report(__FILE__,__LINE__,$ErrorMsg);
}

//缓冲区控制开启
ob_start();