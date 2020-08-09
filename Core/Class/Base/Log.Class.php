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

require(RootPath.'/Config/Log.php');
Class Log{
	private $FilePath;
	
	public function __construct(){
		if(!isset($_SERVER['84PHP_AccessInfo'])){
			$_SERVER['84PHP_AccessInfo']='';
		}
	}
	
	//访问信息
	private function Access(){
		$_SERVER['84PHP_AccessInfo']=
			'[access] IP:'.$_SERVER['REMOTE_ADDR'].
			' | DOMAIN:'.$_SERVER['SERVER_NAME'].
			' | METHOD:'.$_SERVER['REQUEST_METHOD'].
			' | REFERER:'.((empty($_SERVER['HTTP_REFERER']))?'':$_SERVER['HTTP_REFERER']).
			' | UA:'.((empty($_SERVER['HTTP_USER_AGENT']))?'':$_SERVER['HTTP_USER_AGENT']).
			"\r\n";
	}
	
	//添加记录
	public function Add($UnionData=array(),$Return=FALSE){
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
	private function Output($NewContent=NULL){
		if(empty($NewContent)&&empty($_SERVER['84PHP_LOG'])){
			return FALSE;
		}
		if(strlen(FrameworkConfig['SafeCode'])<10){
			Wrong::Report(__FILE__,__LINE__,'Error#B.3.0');
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
			$this->Access();
		}
		
		$FilePath='/Temp/Log/'.FrameworkConfig['SafeCode'].date('/Y-m/d',Runtime);
		if(!file_exists(RootPath.$FilePath)){
			mkdir(RootPath.$FilePath,0777,TRUE);
		}
		
		$Microtime=explode('.',strval(Runtime));
		$RequestUri='';
		if((FrameworkConfig['Route']=='BASE'||FrameworkConfig['Route']=='MIX')&&isset($_GET['p_a_t_h'])){
			$RequestUri=$_GET['p_a_t_h'];
		}
		if(FrameworkConfig['Route']=='PATH'||(FrameworkConfig['Route']=='MIX'&&!isset($_GET['p_a_t_h']))){
			$RequestUri=str_ireplace($_SERVER['SCRIPT_NAME'],'',$_SERVER['PHP_SELF']);
		}
		if($RequestUri==''||$RequestUri=='/'){
			$RequestUri='/index';
		}

		$NewContent='###'."\r\n[path] ".$RequestUri."\r\n[time] ".date('Y-m-d H:i:s',Runtime).'.'.$Microtime[1].' <'.Runtime.">\r\n".$_SERVER['84PHP_AccessInfo'].$_SERVER['84PHP_LOG']."\r\n";
		$Handle=fopen(RootPath.$FilePath.'/'.$LogFileName.'.txt','a');
		if($Handle){
			if(flock($Handle,LOCK_EX)){
				fwrite($Handle,$NewContent);
			}
			fclose($Handle);
		}
	}
	
	//实时写入
	public function Write($UnionData=array()){
		$Info=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'info','内容',FALSE,'');
		$Level=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'level','等级',FALSE,'');
		$Return=$this->Add(array('info'=>$Info,'level'=>$Level),TRUE);
		$this->Output($Return);
	}
		
	public function __destruct(){
		$this->Output();
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}