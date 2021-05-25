<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.1.0
*/

require(RootPath.'/Config/Log.php');
Class Log{
	private static $FilePath;
	
	public static function ClassInitial(){
		if(!isset($_SERVER['84PHP_AccessInfo'])){
			$_SERVER['84PHP_AccessInfo']='';
		}
	}
	
	//访问信息
	private static function Access(){
		$_SERVER['84PHP_AccessInfo']=
			'[access] IP:'.$_SERVER['REMOTE_ADDR'].
			' | DOMAIN:'.$_SERVER['SERVER_NAME'].
			' | METHOD:'.$_SERVER['REQUEST_METHOD'].
			' | REFERER:'.((empty($_SERVER['HTTP_REFERER']))?'':$_SERVER['HTTP_REFERER']).
			' | UA:'.((empty($_SERVER['HTTP_USER_AGENT']))?'':$_SERVER['HTTP_USER_AGENT']).
			"\r\n";
	}
	
	//添加记录
	public static function Add($UnionData=[]){
		$Info=QuickParamet($UnionData,'info','内容',FALSE,'');
		$Level=QuickParamet($UnionData,'level','等级',FALSE,'');
		if(strtoupper($Level)=='E'){
			$Level='error';
		}
		else if(strtoupper($Level)=='N'){
			$Level='notice';
		}
		else if(strtoupper($Level)=='S'){
			$Level='sql';
		}
		else if(strtoupper($Level)=='I'){
			$Level='info';
		}
		else{
			$Level='log';
		}
		$_SERVER['84PHP_LOG'].='['.$Level.'] '.$Info.' <'.strval((intval(microtime(TRUE)*1000)-intval(Runtime*1000))/1000)."s>\r\n";
	}

	//写入文件
	public static function Output(){
		if(strlen(FrameworkConfig['SafeCode'])<10){
			Wrong::Report(['detail'=>'Error#M.14.0','code'=>'M.14.0','log'=>FALSE]);
		}
		
		if(strtoupper($_SERVER['84PHP_CONFIG']['Log']['Interval'])=='H'){
			$LogFileName=date('H\H',Runtime);
		}
		else if(strtoupper($_SERVER['84PHP_CONFIG']['Log']['Interval'])=='M'){
			$LogFileName=date('H\H_i',Runtime);
		}
		else if(strtoupper($_SERVER['84PHP_CONFIG']['Log']['Interval'])=='HM'){
			$LogFileName=date('H\H_i',Runtime);
			if(Runtime%60<30){
				$LogFileName.='_(1)';
			}
			else{
				$LogFileName.='_(2)';
			}
		}
		else{
			$LogFileName='applog';
		}
		if($_SERVER['84PHP_CONFIG']['Log']['Access']){
			self::Access();
		}
		
		$FilePath='/Temp/Log/'.FrameworkConfig['SafeCode'].date('/Y-m/d',Runtime);
		if(!file_exists(RootPath.$FilePath)){
			mkdir(RootPath.$FilePath,0777,TRUE);
		}
		
		$Microtime=explode('.',strval(Runtime));

		$Content=$_SERVER['84PHP_LOG'];
		$_SERVER['84PHP_LOG']='';
		$Content='###'."\r\n[path] ".URI."\r\n[time] ".date('Y-m-d H:i:s',Runtime).'.'.$Microtime[1].' <'.Runtime.">\r\n".$_SERVER['84PHP_AccessInfo'].$Content."\r\n";
		$Handle=fopen(RootPath.$FilePath.'/'.$LogFileName.'.txt','a');
		if($Handle){
			if(flock($Handle,LOCK_EX)){
				fwrite($Handle,$Content);
			}
			fclose($Handle);
		}
	}
	
	//清空日志
	public static function Clean(){
		$_SERVER['84PHP_LOG']='';
	}
	
	//获取累积日志
	public static function Get(){
		return $_SERVER['84PHP_LOG'];
	}
	
	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}
Log::ClassInitial();