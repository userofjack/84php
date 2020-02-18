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

require(RootPath."/Config/Ftp.php");

class Ftp{

	public function __construct(){
		if(!empty($_SESSION['ModuleSetting'][__CLASS__])&&is_array($_SESSION['ModuleSetting'][__CLASS__])){
			foreach($_SESSION['ModuleSetting'][__CLASS__] as $ModuleSettingKey => $ModuleSettingVal){
				$GLOBALS['ModuleConfig_Ftp'][$ModuleSettingKey]=$ModuleSettingVal;
			}
		}
	}

	//上传
	public function Up($UnionData){
		$From=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'from','本地路径');
		$To=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'to','远程路径');
		$Timeout=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'timeout','超时时间',FALSE,90);
		
		$From=AddRootPath($From);

		$Connect=ftp_connect($GLOBALS['ModuleConfig_Ftp']['Server'],$GLOBALS['ModuleConfig_Ftp']['Port'],$Timeout);
		$Login=ftp_login($Connect,$GLOBALS['ModuleConfig_Ftp']['User'],$GLOBALS['ModuleConfig_Ftp']['Password']);
		if((!$Connect)||(!$Login)){
			Wrong::Report(__FILE__,__LINE__,'Error#M.1.0',TRUE);
		}
		$Upload=ftp_put($Connect,$To,$From,FTP_ASCII); 
		ftp_close($Connect);
		if(!$Upload){
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	//下载
	public function Down($UnionData){
		$From=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'from','远程路径');
		$To=RootPath.QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'to','本地路径');
		$Timeout=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'timeout','超时时间',FALSE,90);
		
		$To=AddRootPath($To);

		$Connect=ftp_connect($GLOBALS['ModuleConfig_Ftp']['Server'],$GLOBALS['ModuleConfig_Ftp']['Port'],$Timeout);
		$Login=ftp_login($Connect,$GLOBALS['ModuleConfig_Ftp']['User'],$GLOBALS['ModuleConfig_Ftp']['Password']);
		if((!$Connect)||(!$Login)){
			Wrong::Report(__FILE__,__LINE__,'Error#M.1.0',TRUE);
		}
		$Download=ftp_get($Connect,$To,$From,FTP_ASCII); 
		ftp_close($Connect);
		if(!$Download){
			return FALSE;
		}
		else{
			return TRUE;
		}
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}