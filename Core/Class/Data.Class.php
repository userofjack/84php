<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.1.0
*/

require(RootPath.'/Config/Data.php');

class Data{
	private static $Handle;
	private static $Connect;

	public static function ClassInitial(){
		self::$Handle=strtolower($_SERVER['84PHP_CONFIG']['Data']['Handle']);
		
		if(self::$Handle=='redis'){
			self::RedisConnect();
		}
	}

	//设置
	public static function Set($UnionData=[]){
		$Key=QuickParamet($UnionData,'key','键');
		$Value=QuickParamet($UnionData,'value','值');
		$Time=QuickParamet($UnionData,'time','时间',FALSE,3600);
		$Prefix=QuickParamet($UnionData,'prefix','前缀',FALSE,'');
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
		if(self::$Handle=='file'){
			return self::SetByFile($Prefix,$Key,$Value,$Time);
		}
		if(self::$Handle=='redis'){
			return self::SetByRedis($Prefix,$Key,$Value,$Time);
		}
		
	}
	
	//获取
	public static function Get($UnionData=[]){
		$Key=QuickParamet($UnionData,'key','键');		
		$Prefix=QuickParamet($UnionData,'prefix','前缀',FALSE,'');
		$Callback=QuickParamet($UnionData,'callback','回调',FALSE,NULL);
		if($Key==''){
			return NULL;
		}
		if(self::$Handle=='file'){
			$Result=self::GetByFile($Prefix,$Key);
		}
		else if(self::$Handle=='redis'){
			$Result=self::GetByRedis($Prefix,$Key);
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
		$Level=intval($_SERVER['84PHP_CONFIG']['Data']['Connect']['file']['level']);
		if($_SERVER['84PHP_CONFIG']['Data']['Connect']['file']['level']<1){
			$Level=0;
		}
		if($_SERVER['84PHP_CONFIG']['Data']['Connect']['file']['level']>15){
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
		$FileHandle=fopen(self::GetFilePath($Prefix,$Key,TRUE),'w');
		if(!$FileHandle){
			Wrong::Report(['detail'=>'Error#M.16.0','code'=>'M.16.0']);
		}
		fwrite($FileHandle,$Cache);
		fclose($FileHandle);
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
			if (mt_rand(1,$_SERVER['84PHP_CONFIG']['Data']['Connect']['file']['clean'])==1){
				self::DeleteByFile($Key,$Prefix,$FilePath);
			}
			return NULL;
		}
		return self::StrToVar(strtok("\r\n"));
	}
	
	//连接Redis
	private static function RedisConnect(){
		self::$Connect=new Redis();
		try
		{
			self::$Connect->connect($_SERVER['84PHP_CONFIG']['Data']['Connect']['redis']['address'],$_SERVER['84PHP_CONFIG']['Data']['Connect']['redis']['port'],$_SERVER['84PHP_CONFIG']['Data']['Connect']['redis']['timeout']);
		}
		catch (Throwable $t)
		{
			Wrong::Report(['detail'=>'Error#M.16.1','code'=>'M.16.1']);
		}
		if($_SERVER['84PHP_CONFIG']['Data']['Connect']['redis']['password']!=''){
			self::$Connect->auth($_SERVER['84PHP_CONFIG']['Data']['Connect']['redis']['password']) ?:Wrong::Report(['detail'=>'Error#M.16.2','code'=>'M.16.2']);
		}
		self::$Connect->select($_SERVER['84PHP_CONFIG']['Data']['Connect']['redis']['dbnumber'])?:Wrong::Report(['detail'=>'Error#M.16.3','code'=>'M.16.3']);
		$_SERVER['84PHP_LastWork']['Data']='CloseRedisConnect';
	}

	//关闭Redis缓存
	public static function CloseRedisConnect(){
		self::$Connect->close();
	}

	//设置Redis緩存
	private static function SetByRedis($Prefix,$Key,$Value,$Time){
		$MD5=md5($Key);
		if($Prefix!=''){
			$Prefix.='_';
		}
		if($Time<1){
			self::$Connect->delete($MD5);
			return TRUE;
		}
		$Cache=self::VarToStr($Value);
		self::$Connect->set($Prefix.$MD5,$Cache);
		self::$Connect->expire($Prefix.$MD5,$Time);
		return TRUE;
	}
	
	//获取Redis缓存
	private static function GetByRedis($Prefix,$Key){
		$MD5=md5($Key);
		if($Prefix!=''){
			$Prefix.='_';
		}
		$Cache=self::$Connect->get($Prefix.$MD5);

		if($Cache==FALSE){
			return NULL;
		}
		return self::StrToVar($Cache);
	}

	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}
Data::ClassInitial();