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

  框架版本号：4.0.2
*/

require(RootPath.'/Config/Wrong.php');
class Wrong{

	public function __construct(){
		if($_SERVER['84PHP_CONFIG']['Wrong']['Log']){
			LoadModule('Log','Base');
		}
	}

	public static function Report($File,$Line,$ErrorDetail,$Anytime=FALSE,$StatusCode=500){
		ob_clean();
		$ByAjax=isset($_SERVER["HTTP_X_REQUESTED_WITH"])&&strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]=='xmlhttprequest');
		if(!FrameworkConfig['Always200']){
			http_response_code($StatusCode);
		}
		if($ByAjax){
			$Style=file_get_contents(RootPath.'/Core/Errors/json-style.php');
		}
		else{
			$Style=file_get_contents(RootPath.'/Core/Errors/html-style.php');
		}
		if($Style==FALSE){
			die('Error#B.2.0');
		}
		if(!strstr($Style,'{$ErrorInfo}')){
			$Style='{$ErrorInfo}';
		}
		if(!FrameworkConfig['Debug']&&!$Anytime){
			$Style=str_replace('{$ErrorInfo}','Error#C.0.4',$Style);
		}
		if(FrameworkConfig['Debug']&&!empty($File)){
			$ErrorDetail.="\r\n\r\n".' @ Debug#the error in [ '.$File.' ] on [ '.$Line.' ].';
		}
		$ErrorDetail=str_replace('\\','/',$ErrorDetail);
		if($_SERVER['84PHP_CONFIG']['Wrong']['Log']){
			$_SERVER['84PHP_LOG'].='[error] '.$ErrorDetail.' @ Debug#the error in [ '.$File.' ] on [ '.$Line.' ].'.' <'.strval((intval(microtime(TRUE)*1000)-intval(Runtime*1000))/1000)."s>\r\n";
		}
		if($ByAjax){
			$ErrorDetail=substr(substr(json_encode(array('*'=>$ErrorDetail),320),6),0,-2);
		}
		$Style=str_replace('{$ErrorInfo}',$ErrorDetail,$Style);
		$Style=str_replace('{$StatusCode}',$StatusCode,$Style);
		
		die($Style);
	}
}