<?php
require(substr(dirname(__FILE__),0,-4).'/Core/Initial.php');
if(FrameworkConfig['Debug']){
	require(RootPath.'/Core/Class/Base/Cache.Class.php');
	$_SERVER['84PHP_MODULE']['Cache']=new Cache;
}

$RequestUri='';
if((FrameworkConfig['Route']=='BASE'||FrameworkConfig['Route']=='MIX')&&isset($_GET['p_a_t_h'])){
	$RequestUri=$_GET['p_a_t_h'];
}
if(FrameworkConfig['Route']=='PATH'||(FrameworkConfig['Route']=='MIX'&&!isset($_GET['p_a_t_h']))){
	$RequestUri=str_ireplace($_SERVER['SCRIPT_NAME'],'',$_SERVER['PHP_SELF']);
}
if($RequestUri==''||$RequestUri=='/'){
	$RequestUri='/index';
}

if(FrameworkConfig['Debug']){
	$_SERVER['84PHP_MODULE']['Cache']->Compile(array('path'=>$RequestUri));
}
if(file_exists(RootPath.'/Temp/Cache'.$RequestUri.'.php')){
	require(RootPath.'/Temp/Cache'.$RequestUri.'.php');
	exit;
}
else {
	if(FrameworkConfig['Debug']){
		$_SERVER['84PHP_MODULE']['Cache']->Compile(array('path'=>$RequestUri.'/index'));
	}
	if(file_exists(RootPath.'/Temp/Cache'.$RequestUri.'/index.php')){
		require(RootPath.'/Temp/Cache'.$RequestUri.'/index.php');
	}
	else{
		Wrong::Report('','','Error#C.0.0',FALSE,404);
	}
}