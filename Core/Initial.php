<?php
/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/
define('__TIME__',microtime(TRUE));

define('__VERSION__','6.0.0');

define('__ROOT__',substr(str_replace(['\\','//'],'/',dirname(__FILE__)),0,-5));
set_include_path(get_include_path().PATH_SEPARATOR.__ROOT__.'/Lib/');

$_SERVER['84PHP']=['Config'=>[],'Log'=>[],'Option'=>[],'URI'=>''];

require(__ROOT__.'/Config/Common.php');
define('__DEBUG__',$_SERVER['84PHP']['Config']['Common']['Debug']);

spl_autoload_register(
    function($ClassName)
    {
        if (!file_exists(__ROOT__.'/Core/Class/'.$ClassName.'.Class.php')) {
            Api::wrong(['level'=>'F','detail'=>'Error#C.0.5'."\r\n\r\n @ ".$ClassName,'code'=>'C.0.5']);
        }
        else {
            require(__ROOT__.'/Core/Class/'.$ClassName.'.Class.php');
        }
    }
);

if (isset($_SERVER['REQUEST_METHOD'])&&$_SERVER['REQUEST_METHOD']=='OPTIONS') {
    die('OPTIONS request blocked by framework.');
}

date_default_timezone_set($_SERVER['84PHP']['Config']['Common']['TimeZone']);

if ($_SERVER['84PHP']['Config']['Common']['TimeLimit']!==FALSE) {
    set_time_limit($_SERVER['84PHP']['Config']['Common']['TimeLimit']);
}

if ($_SERVER['84PHP']['Config']['Common']['HTTPS']) {
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

//路由
$_SERVER['84PHP']['URI']='';
$_SERVER['84PHP']['Option']=getopt('',['path:']);
if (empty($_SERVER['84PHP']['Option']['path'])) {
    $_SERVER['84PHP']['URI']=$_SERVER['84PHP_URI']=$_GET['p_a_t_h'];;
}
else {
    $_SERVER['84PHP']['URI']=$_SERVER['84PHP']['Option']['path'];
}
define('__URI__',$_SERVER['84PHP']['URI']);

//快捷传参
function quickParamet($UnionData,$Name,$Dialect,$Must=TRUE,$Default=NULL)
{
    if (isset($UnionData[$Name])) {
        return $UnionData[$Name];
    }
    else if (isset($UnionData[$Dialect])) {
        return $UnionData[$Dialect];
    }
    else if (isset($UnionData[strtolower($Name)])) {
        return $UnionData[strtolower($Name)];
    }
    else if (isset($UnionData[strtoupper($Name)])) {
        return $UnionData[strtoupper($Name)];
    }
    else if (isset($UnionData[mb_convert_case($Dialect,MB_CASE_LOWER,'UTF-8')])) {
        return $UnionData[mb_convert_case($Dialect,MB_CASE_LOWER,'UTF-8')];
    }
    else if (isset($UnionData[mb_convert_case($Dialect,MB_CASE_UPPER,'UTF-8')])) {
        return $UnionData[mb_convert_case($Dialect,MB_CASE_UPPER,'UTF-8')];
    }
    else if ($Must) {
        $Stack=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);
        $ErrorMsg='';
        if (isset($Stack[1]['class'])) {
            $ErrorMsg="\r\n\r\n @ ".$Stack[1]['class'].$Stack[1]['type'].$Stack[1]['function'].'() @ '.$Name.'（'.$Dialect.'）';
        }
        Api::wrong(['level'=>'F','detail'=>'Error#C.0.3'.$ErrorMsg,'code'=>'C.0.3']);
    }
    else {
        return $Default;
    }
}

//获取磁盘路径
function diskPath($Path,$Prefix='')
{
    $Path=str_replace(['\\','//'],['/','/'],$Path);
    if (substr($Path,0,1)=='/') {
        $Path=substr($Path,1);
    }
    if (substr($Path,-1,1)=='/') {
        $Path=substr($Path,0,-1);
    }
    if (substr($Path,0,strlen(__ROOT__))!=__ROOT__) {
        if (!empty($Prefix)) {
            $Path=__ROOT__.$Prefix.'/'.$Path;
        }
        else {
            $Path=__ROOT__.'/'.$Path;
        }
    }
    
    return $Path;
}

//方法不存在
function unknownStaticMethod($ModuleName,$MethodName)
{
    Api::wrong(['level'=>'F','detail'=>'Error#C.0.4 @ '.$ModuleName.' :: '.$MethodName.'()','code'=>'C.0.4']);
}

//缓冲区控制开启
ob_start();