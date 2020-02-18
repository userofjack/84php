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

class Wrong{

	public function __construct(){
		if(!empty($_SESSION['ModuleSetting'][__CLASS__])&&is_array($_SESSION['ModuleSetting'][__CLASS__])){
			foreach($_SESSION['ModuleSetting'][__CLASS__] as $ModuleSettingKey => $ModuleSettingVal){
				$GLOBALS['ModuleConfig_Wrong'][$ModuleSettingKey]=$ModuleSettingVal;
			}
		}
	}

	public static function Report($File,$Line,$ErrorDetail,$Anytime=FALSE,$ErrorCode=500){
		if(isset($_SESSION['Debug'])){
			$DebugState=isset($_SESSION['Debug']);
		}
		else{
			$DebugState=$GLOBALS['FrameworkConfig']['Debug'];
		}
		ob_clean();
		$Style=file_get_contents(RootPath."/Core/Errors/Style.php");
		if(!$Style){
			die('Error#B.2.0');
		}
		if(!strstr($Style,'{$ErrorInfo}')){
			$Style='{$ErrorInfo}';
		}
		if(!$DebugState&&!$Anytime){
			$Style=str_replace('{$ErrorInfo}','Error#C.0.4',$Style);
		}
		if($DebugState&&!empty($File)){
			$ErrorDetail.=' @ Debug#the error in [ '.$File.' ] on [ '.$Line.' ].';
		}
		$ErrorDetail=str_replace('\\','/',$ErrorDetail);
		$ErrorDetail=substr(substr(json_encode(array('*'=>$ErrorDetail),320),6),0,-2);
		$Style=str_replace('{$ErrorInfo}',$ErrorDetail,$Style);
		$Style=str_replace('{$ErrorCode}',$ErrorCode,$Style);
		
		die($Style);
	}
}