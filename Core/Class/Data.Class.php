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
		if($Time<1){
			return TRUE;
		}
	}
	
	//获取
	public static function Get($UnionData=[]){
		$Key=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'key','键');		
		if($Key==''){
			return NULL;
		}
	}
	
	//删除
	public static function Delete($UnionData=[]){
		$Key=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'key','键');		
		
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
	private static function GetFilePath($Key,$Prefix){
		$MD5=md5($Key);
		$Path='';
		$Level=0;
		if($_SERVER['84PHP_CONFIG']['Data']['Level']<1){
			$Level=0;
		}
		if($_SERVER['84PHP_CONFIG']['Data']['Level']>15){
			$Level=15;
		}
		for($i=0;$i<$Level;$i++){
			$Path.='/'.$MD5[0].$MD5[1];
			$MD5=substr($MD5,2);
		}
		$Path.='/'.$MD5.'.tmp';
		$Path=str_replace(['\\','//'],['/','/'],$Prefix.$Path);
		if($Path[0]=='/'){
			$Path=substr($Path,1);
		}
		return $Path;
	}

	//设置文件緩存
	private static function SetByFile($Prefix,$Key,$Value,$Time){
		$Cache=strval(intval(Runtime)+$Time)."\r\n".self::VarToStr($Value);
		$Handle=@fopen(RootPath.'/Temp/Data/'.self::GetFilePath($Key,$Prefix),'w');
		if(!$Handle){
			Wrong::Report(__FILE__,__LINE__,'Error#M.9.4');//change type
		}
		fwrite($Handle,$Cache);
		fclose($Handle);
	}
	
	//删除文件緩存
	private static function DeleteByFile($Prefix,$Key){
		$Result=unlink(RootPath.'/Temp/Data/'.self::GetFilePath($Key,$Prefix));
		if(!$Result){
			return FALSE;
		}
		return TRUE;
	}

	//获取文件缓存
	private static function GetByFile($UnionData=[]){
		$FilePath=RootPath.'/Temp/Data/'.self::GetFilePath($Key,$Prefix);
		if(!file_exists($FilePath)){
			return NULL;
		}
		$Cache=file_get_contents($FilePath);
		$ExpTime=intval(strtok($Cache, "\r\n"));
		if($ExpTime==0||$ExpTime<intval(Runtime)){
			return NULL;
		}
	}

	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}