<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

require(RootPath.'/Config/Wrong.php');
class Wrong{

	public static function Report($File,$Line,$ErrorDetail,$Anytime=FALSE,$StatusCode=500){
		ob_clean();
		if(!FrameworkConfig['Always200']){
			http_response_code($StatusCode);
		}
		$ByAjax=
			(isset($_SERVER["HTTP_X_REQUESTED_WITH"])&&strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]=='xmlhttprequest'))||
			(isset($_SERVER["HTTP_ACCEPT"])&&stristr($_SERVER["HTTP_ACCEPT"],'application/json'));
		$StyleType=strtoupper($_SERVER['84PHP_CONFIG']['Wrong']['Style']);
		
		if(($StyleType=='AUTO'&&$ByAjax)||$StyleType=='JSON'){
			$Style=file_get_contents(RootPath.'/Config/ErrorJsonStyle.php');
		}
		else{
			$Style=file_get_contents(RootPath.'/Config/ErrorHtmlStyle.php');
		}
		if($Style==FALSE){
			die('Error#M.13.0');
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
			$ErrorDetail=substr(substr(json_encode(['*'=>$ErrorDetail],320),6),0,-2);
		}
		$Style=str_replace('{$ErrorInfo}',$ErrorDetail,$Style);
		$Style=str_replace('{$StatusCode}',$StatusCode,$Style);
		
		die($Style);
	}
}