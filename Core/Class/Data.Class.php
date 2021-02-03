<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

require(RootPath.'/Config/Data.php');

class Data{

	//设置
	public static function Set($UnionData=[]){
		$Key=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'key','键');
		$Value=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'value','值');
		$Time=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'time','时间',FALSE,3600);
		$Prefix=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'prefix','前缀',FALSE,'');
		if($Key==''){
			return FALSE;
		}
		if($Key==''){
			return FALSE;
		}
		if($Value==NULL){
			$Value='';
		}
		if(!is_bool($Value)&&!is_array($Value)&&!is_int($Value)&&!is_float($Value)&&!is_string($Value)&&!is_object($Value)){
			return FALSE;
		}
		$Time=intval($Time);
		if(strtolower($_SERVER['84PHP_CONFIG']['Data']['Handle'])=='file'){
			return self::SetByFile($Prefix,$Key,$Value,$Time);
		}
		
	}
	
	//获取
	public static function Get($UnionData=[]){
		$Key=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'key','键');		
		$Prefix=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'prefix','前缀',FALSE,'');
		$Callback=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'callback','回调',FALSE,NULL);
		if($Key==''){
			return NULL;
		}
		if(strtolower($_SERVER['84PHP_CONFIG']['Data']['Handle'])=='file'){
			$Result=self::GetByFile($Prefix,$Key);
		}
		if($Result===NULL&&is_object($Callback)){
			return $Callback();
		}
		else{
			return $Result;
		}
	}
	
	//变量转字符串
	private static function VarToStr($Value){
		return serialize($Value);
	}
	
	//字符串转变量
	private static function StrToVar($String){
		return unserialize($String);
	}
	
	//获取文件缓存路径
	private static function GetFilePath($Prefix,$Key,$Mkdir=FALSE){
		$MD5=md5($Key);
		$Path=RootPath.'/Temp/Data/'.$Prefix;
		$Level=intval($_SERVER['84PHP_CONFIG']['Data']['Connect']['File']['Level']);
		if($_SERVER['84PHP_CONFIG']['Data']['Connect']['File']['Level']<1){
			$Level=0;
		}
		if($_SERVER['84PHP_CONFIG']['Data']['Connect']['File']['Level']>15){
			$Level=15;
		}
		for($i=0;$i<$Level;$i++){
			$Path.='/'.$MD5[0].$MD5[1];
			$MD5=substr($MD5,2);
		}
		$Path=str_replace(['\\','//'],['/','/'],$Path);
		if($Mkdir){
			if(!is_dir($Path)){
				mkdir($Path,0777,TRUE);
			}
		}
		
		$Path.='/'.$MD5.'.tmp';
		if($Path[0]=='/'){
			$Path=substr($Path,1);
		}
		return $Path;
	}

	//设置文件緩存
	private static function SetByFile($Prefix,$Key,$Value,$Time){
		if($Time<1){
			return self::DeleteByFile($Key,$Prefix);
		}
		$Cache=strval(intval(Runtime)+$Time)."\r\n".self::VarToStr($Value);
		$Handle=@fopen(self::GetFilePath($Prefix,$Key,TRUE),'w');
		if(!$Handle){
			Wrong::Report(__FILE__,__LINE__,'Error#M.9.4');//change type
		}
		fwrite($Handle,$Cache);
		fclose($Handle);
        return TRUE;
	}
	
	//删除文件緩存
	private static function DeleteByFile($Key,$Prefix,$Path=''){
		if($Path==''){
			$Path=self::GetFilePath($Prefix,$Key);
		}
		if(file_exists($Path)){
			$Result=unlink($Path);
		}
		else{
			$Result=TRUE;
		}
		return $Result;
	}

	//获取文件缓存
	private static function GetByFile($Prefix,$Key){
		$FilePath=self::GetFilePath($Prefix,$Key);
		if(!file_exists($FilePath)){
			return NULL;
		}
		$Cache=file_get_contents($FilePath);
		$ExpTime=intval(strtok($Cache, "\r\n"));
		if($ExpTime<=0||$ExpTime<intval(Runtime)){
			if (mt_rand(1,$_SERVER['84PHP_CONFIG']['Data']['Connect']['File']['Clean'])==1){
				  self::DeleteByFile($Key,$Prefix,$FilePath);
			}
			return NULL;
		}
        return self::StrToVar(strtok("\r\n"));
	}

	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}