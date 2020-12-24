<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

require(RootPath.'/Config/Log.php');
Class Log{
	private $FilePath;
	
	public function __construct(){
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
	public static function Add($UnionData=[],$Return=FALSE){
		$Info=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'info','内容',FALSE,'');
		$Level=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'level','等级',FALSE,'');
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
		if($Return){
			return '['.$Level.'] '.$Info."\r\n";
		}
		$_SERVER['84PHP_LOG'].='['.$Level.'] '.$Info.' <'.strval((intval(microtime(TRUE)*1000)-intval(Runtime*1000))/1000)."s>\r\n";
	}

	//写入文件
	private static function Output($NewContent=NULL){
		if(empty($NewContent)&&empty($_SERVER['84PHP_LOG'])){
			return FALSE;
		}
		if(strlen(FrameworkConfig['SafeCode'])<10){
			Wrong::Report(__FILE__,__LINE__,'Error#M.14.0');
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
		if(empty($NewContent)){
			$NewContent=$_SERVER['84PHP_LOG'];
		}
		if($_SERVER['84PHP_CONFIG']['Log']['Access']){
			self::Access();
		}
		
		$FilePath='/Temp/Log/'.FrameworkConfig['SafeCode'].date('/Y-m/d',Runtime);
		if(!file_exists(RootPath.$FilePath)){
			mkdir(RootPath.$FilePath,0777,TRUE);
		}
		
		$Microtime=explode('.',strval(Runtime));

		$NewContent='###'."\r\n[path] ".URI."\r\n[time] ".date('Y-m-d H:i:s',Runtime).'.'.$Microtime[1].' <'.Runtime.">\r\n".$_SERVER['84PHP_AccessInfo'].$_SERVER['84PHP_LOG']."\r\n";
		$Handle=fopen(RootPath.$FilePath.'/'.$LogFileName.'.txt','a');
		if($Handle){
			if(flock($Handle,LOCK_EX)){
				fwrite($Handle,$NewContent);
			}
			fclose($Handle);
		}
	}
	
	//实时写入
	public static function Write($UnionData=[]){
		$Info=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'info','内容',FALSE,'');
		$Level=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'level','等级',FALSE,'');
		$Return=self::Add(['info'=>$Info,'level'=>$Level],TRUE);
		self::Output($Return);
	}
		
	public function __destruct(){
		self::Output();
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}