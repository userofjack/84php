<?php
require(dirname(__FILE__)."/../Core/Initial.php");
if($FrameworkConfig['Debug']){
	require_once(RootPath."/Core/Class/Base/Cache.Class.php");
	$ClassCache=new Cache;
}

if($FrameworkConfig['Route']=='BASE'||$FrameworkConfig['Route']=='MIX'){
	$RequestUri='/';
	if(isset($_GET['p_a_t_h'])){
		$RequestUri=$_GET['p_a_t_h'];
		if($RequestUri==''){
			$RequestUri='/';
		}
	}
}
if($FrameworkConfig['Route']=='PATH'||($FrameworkConfig['Route']=='MIX'&&!isset($_GET['p_a_t_h']))){
	$RequestUri=str_ireplace($_SERVER['SCRIPT_NAME'],'',$_SERVER['PHP_SELF']);
}

if($RequestUri=='/'){
	$RequestUri='/index';
}
if($FrameworkConfig['Debug']){
	$ClassCache->Compile(array('path'=>$RequestUri));
}
if(file_exists(RootPath.'/Cache'.$RequestUri.'.php')){
	require_once(RootPath.'/Cache'.$RequestUri.'.php');
	exit;
}
else {
	if($FrameworkConfig['Debug']){
		$ClassCache->Compile(array('path'=>$RequestUri.'/index'));
	}
	if(file_exists(RootPath.'/Cache'.$RequestUri.'/index.php')){
		require_once(RootPath.'/Cache'.$RequestUri.'/index.php');
	}
	else{
		Wrong::Report('','','Error#C.0.0',TRUE,404);
	}
}