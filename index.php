<?php
use core\Api;
use core\Cache;
use core\Log;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/
define('__TIME__',microtime(TRUE));
define('__VERSION__','6.0.0');
define('__ROOT__',str_replace(['\\','//'],'/',dirname(__FILE__)));

set_include_path(get_include_path().PATH_SEPARATOR.__ROOT__.'/lib/');

$_SERVER['84PHP']=['Config'=>[],'Log'=>[],'Option'=>[],'Runtime'=>[],'URI'=>''];

require(__ROOT__.'/config/base.php');

define('__DEBUG__',$_SERVER['84PHP']['Config']['Base']['Debug']);

spl_autoload_register(
    function($ClassName)
    {
        if (!file_exists(__ROOT__.'/lib/'.str_replace(['\\','//'],'/',$ClassName).'.php')) {
            Api::wrong(['level'=>'F','detail'=>'Error#C.0.5'."\r\n\r\n @ ".$ClassName,'code'=>'C.0.5']);
        }
        else {
            require(__ROOT__.'/lib/'.str_replace(['\\','//'],'/',$ClassName).'.php');
        }
    }
);

if (isset($_SERVER['REQUEST_METHOD'])&&$_SERVER['REQUEST_METHOD']=='OPTIONS') {
    die('OPTIONS request blocked by framework.');
}

date_default_timezone_set($_SERVER['84PHP']['Config']['Base']['TimeZone']);

if ($_SERVER['84PHP']['Config']['Base']['TimeLimit']!==FALSE) {
    set_time_limit($_SERVER['84PHP']['Config']['Base']['TimeLimit']);
}

if ($_SERVER['84PHP']['Config']['Base']['HTTPS']) {
    if (!isset($_SERVER['HTTPS'])) {
        Api::wrong(['level'=>'F','detail'=>'Error#C.0.6','code'=>'C.0.6']);
    }
    if ($_SERVER['HTTPS']==''||$_SERVER['HTTPS']=='off') {
        header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST__URI__']);
    }
}

if (!__DEBUG__) {
    error_reporting(0);
}
else {
    header('Cache-Control: no-cache,must-revalidate');
    header('Pragma: no-cache');
    header("Expires: -1");
    header('Last-Modified: Thu, 01 Jan 1970 00:00:00 GMT');
}

// 错误处理
set_error_handler('systemErrorHandler', E_ALL | E_STRICT);
function systemErrorHandler($ErrorNo,$ErrorMsg,$ErrorFile,$ErrorLine)
{
    if (error_reporting()==0) {
        return TRUE;
    }    
    switch ($ErrorNo) {
        case E_WARNING:
            $PHPSystemError='PHP Warning: ';
            break;
        case E_NOTICE:
            $PHPSystemError='PHP Notice: ';
            break;
        case E_DEPRECATED:
            $PHPSystemError='PHP Deprecated: ';
            break;
        case E_USER_ERROR:
            $PHPSystemError='User Error: ';
            break;
        case E_USER_WARNING:
            $PHPSystemError='User Warning: ';
            break;
        case E_USER_NOTICE:
            $PHPSystemError='User Notice: ';
            break;
        case E_USER_DEPRECATED:
            $PHPSystemError='User Deprecated: ';
            break;
        case E_STRICT:
            $PHPSystemError='PHP Strict: ';
            break;
        default:
            $PHPSystemError='Unkonw error: ';
            break;
    }
 
    $PHPSystemError.=$ErrorMsg.' in '.str_replace('\\','/',$ErrorFile).' on '.$ErrorLine;
    Api::wrong(['level'=>'S','detail'=>'Error#C.0.2 @ '.$PHPSystemError,'code'=>'C.0.2']);
    return TRUE;
}

//缓冲区控制开启
ob_start();

//路由
$_SERVER['84PHP']['URI']='';
$_SERVER['84PHP']['Option']=getopt('',['path:']);
if (empty($_SERVER['84PHP']['Option']['path'])) {
    if(!isset($_GET['p_a_t_h'])){
        $_GET['p_a_t_h']='';
    }
    $_SERVER['84PHP']['URI']=$_GET['p_a_t_h'];
}
else {
    $_SERVER['84PHP']['URI']=$_SERVER['84PHP']['Option']['path'];
}
define('__URI__',$_SERVER['84PHP']['URI']);

Cache::compile(['path'=>__URI__]);
if (file_exists(__ROOT__.'/temp/cache'.__URI__.'.php')) {
	require(__ROOT__.'/temp/cache'.__URI__.'.php');
}
else if (file_exists(__ROOT__.'/web'.__URI__.'/index.html')) {
	$Content=file_get_contents(__ROOT__.'/Web'.__URI__.'/index.html');
	echo($Content);
}
else if (file_exists(__ROOT__.'/web'.__URI__.'/index.htm')) {
	$Content=file_get_contents(__ROOT__.'/Web'.__URI__.'/index.htm');
	echo($Content);
}
else if (!empty($_SERVER['84PHP']['Config']['Base']['PageNotFound'])) {
    header('Location: '.$_SERVER['84PHP']['Config']['Base']['PageNotFound']);
}
else {
    Api::wrong(['level'=>'U','detail'=>'Error#C.0.0','code'=>'C.0.0','http'=>404]);
}

if (!empty($_SERVER['84PHP']['Log'])) {
    Log::output();
}