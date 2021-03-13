<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

require(RootPath.'/Config/Ftp.php');

class Ftp{

	//上传
	public static function Up($UnionData=[]){
		$From=QuickParamet($UnionData,'from','本地路径');
		$To=QuickParamet($UnionData,'to','远程路径');
		$Timeout=QuickParamet($UnionData,'timeout','超时时间',FALSE,90);
		
		$From=DiskPath($From);

		$Connect=ftp_connect($_SERVER['84PHP_CONFIG']['Ftp']['Server'],$_SERVER['84PHP_CONFIG']['Ftp']['Port'],$Timeout);
		$Login=ftp_login($Connect,$_SERVER['84PHP_CONFIG']['Ftp']['User'],$_SERVER['84PHP_CONFIG']['Ftp']['Password']);
		if((!$Connect)||(!$Login)){
			Wrong::Report(['detail'=>'Error#M.1.0','code'=>'M.1.0']);
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
	public static function Down($UnionData=[]){
		$From=QuickParamet($UnionData,'from','远程路径');
		$To=RootPath.QuickParamet($UnionData,'to','本地路径');
		$Timeout=QuickParamet($UnionData,'timeout','超时时间',FALSE,90);
		
		$To=DiskPath($To);

		$Connect=ftp_connect($_SERVER['84PHP_CONFIG']['Ftp']['Server'],$_SERVER['84PHP_CONFIG']['Ftp']['Port'],$Timeout);
		$Login=ftp_login($Connect,$_SERVER['84PHP_CONFIG']['Ftp']['User'],$_SERVER['84PHP_CONFIG']['Ftp']['Password']);
		if((!$Connect)||(!$Login)){
			Wrong::Report(['detail'=>'Error#M.1.0','code'=>'M.1.0']);
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
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}