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

require(RootPath."/Config/Ip.php");

Class Ip{
	private $BlackListFile;
	private $WhiteListFile;
	private $BlackList;
	private $WhiteList;


	public function __construct(){
		if(!empty($_SESSION['ModuleSetting'][__CLASS__])&&is_array($_SESSION['ModuleSetting'][__CLASS__])){
			foreach($_SESSION['ModuleSetting'][__CLASS__] as $ModuleSettingKey => $ModuleSettingVal){
				$GLOBALS['ModuleConfig_Ip'][$ModuleSettingKey]=$ModuleSettingVal;
			}
		}

		if(!isset($_SESSION)){
			session_start();
		}
		
		$this->BlackListFile=RootPath.'/Temp/ip-blacklist.php';
		$this->WhiteListFile=RootPath.'/Temp/ip-whitelist.php';
		if(!file_exists($this->BlackListFile)){
			if(!file_put_contents($this->BlackListFile,'<?php exit; ?>')){
				Wrong::Report(__FILE__,__LINE__,'Error#M.3.0',TRUE);
			}
		}
		if(!file_exists($this->WhiteListFile)){
			if(!file_put_contents($this->WhiteListFile,'<?php exit; ?>')){
				Wrong::Report(__FILE__,__LINE__,'Error#M.3.0',TRUE);
			}
		}
		$BlackListText=file_get_contents($this->BlackListFile);
		$WhiteListText=file_get_contents($this->WhiteListFile);
		if(!$BlackListText||!WhiteListText){
			Wrong::Report(__FILE__,__LINE__,'Error#M.3.1',TRUE);
		}
		
		$this->BlackList=$this->TextToArray($BlackListText);
		$this->WhiteList=$this->TextToArray($WhiteListText);
	}
	
	//格式检测
	private function IpCheck($Str){
		if(preg_match('/(?=(\b|\D))((\*\.)|(\*)|(25[0-5]|2[0-4]\d|[01]?\d\d?)($|(?!\.$)\.)){4}/',$Str)){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	
	//转换
	private function Transform($Str,$Start=TRUE){
		$StrLen=strlen($Str);
		if(ctype_digit($Str)&&$StrLen>=10&&$StrLen<=12){
			return substr($Str,0,$StrLen-9).'.'.intval(substr($Str,-9,3)).'.'.intval(substr($Str,-6,3)).'.'.intval(substr($Str,-3,3));
		}
		else if($this->IpCheck($Str)){
			$TempArray=explode('.',$Str);
			for($i=0;$i<4;$i++){
				if($TempArray[$i]=='*'){
					if($Start){
						$TempArray[$i]='000';
					}
					else{
						$TempArray[$i]='255';
					}
				}
				$TempLen=strlen($TempArray[$i]);
				if($TempLen==1){
					$TempArray[$i]='00'.$TempArray[$i];
				}
				if($TempLen==2){
					$TempArray[$i]='0'.$TempArray[$i];
				}
			}

			return floatval($TempArray[0].$TempArray[1].$TempArray[2].$TempArray[3]);
		}
		else{
			return FALSE;
		}
	}
	
	//文本转数组
	private function TextToArray($Str){
		$Str=preg_replace('/[^0-9.\-\*,&]/','',$Str);;
		$FirstStep=explode('&',$Str);
		$SecondStep=array();
		foreach($FirstStep as $Key => $Val){
			if(!empty($Val)){
				$SecondStep[$Key]=explode(',',$Val);
				if(isset($SecondStep[$Key][1])){
					$SecondStep[$Key][2]=$SecondStep[$Key][1];
					$TempArray=explode('-',$SecondStep[$Key][0]);
					if(isset($TempArray[1])){
						$SecondStep[$Key][0]=$TempArray[0];
						$SecondStep[$Key][1]=$TempArray[1];
					}
				}
			}
		}
		return $SecondStep;
	}
	
	//数组转文本
	private function ArrayToText($Array){
		$Return='';
		foreach($Array as $Val){
			if(isset($Val[0])){
				if(!isset($Val[1])){
					$Val[1]=$Val[0];
				}
				if(!isset($Val[2])){
					$Val[2]='';
				}
				if($Val[0]>$Val[1]){
					$Return.=$Val[1].'-'.$Val[0].','.$Val[2].'&';
				}
				else{
					$Return.=$Val[0].'-'.$Val[1].','.$Val[2].'&';
				}
			}
		}
		return $Return;
	}
	
	//移除
	private function Remove($Type,$StartIPNumber,$EndIPNumber){
		if(strtolower($Type)=='b'){
			$ListArray=$this->BlackList;
		}
		else{
			$ListArray=$this->WhiteList;
		}
		foreach($ListArray as $Key=>$Val){
			if($StartIPNumber==$Val[0]&&$EndIPNumber==$Val[1]){
				if(strtolower($Type)=='b'){
					unset($this->BlackList[$Key]);
				}
				else{
					unset($this->WhiteList[$Key]);
				}
			}
		}
		return TRUE;
	}	
	
	//写入文件
	private function Save($UnionData){
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型',FALSE,'b');
		if(strtolower($Type)=='b'){
			$ListText=$this->ArrayToText($this->BlackList);
			$Handle=@fopen($this->BlackListFile,'w');
			if(!$Handle){
				Wrong::Report(__FILE__,__LINE__,'Error#M.3.2',TRUE);
			}
		}
		else{
			$ListText=$this->ArrayToText($this->WhiteList);
			$Handle=@fopen($this->WhiteListFile,'w');
			if(!$Handle){
				Wrong::Report(__FILE__,__LINE__,'Error#M.3.2',TRUE);
			}
		}
		fwrite($Handle,'<?php exit; ?>'.$ListText);
		fclose($Handle);
	}
	
	//添加
	public function Add($UnionData){
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型');
		$StartIP=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'ip_start','起始ip');
		$EndIP=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'ip_end','结束ip',FALSE,NULL);
		$ExpTime=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'exp_time','过期时间',FALSE,NULL);
		if(empty($StartIP)){
			return FALSE;
		}
		if(!$this->IpCheck($StartIP)){
			return FALSE;
		}
		if(empty($EndIP)){
			$EndIP=$StartIP;
		}
		if(!$this->IpCheck($EndIP)){
			return FALSE;
		}
		if(!empty($ExpTime)&&intval($ExpTime)<time()){
			return FALSE;
		}
		$StartIPNumber=$this->Transform($StartIP);
		$EndIPNumber=$this->Transform($EndIP,FALSE);
		$this->Remove($Type,$StartIPNumber,$EndIPNumber);
		if(strtolower($Type)=='b'){
			$this->BlackList[]=array($StartIPNumber,$EndIPNumber,$ExpTime);
		}
		else{
			$this->WhiteList[]=array($StartIPNumber,$EndIPNumber,$ExpTime);
		}
		$this->Save($Type);
		return TRUE;
	}
	
	//移除
	public function Delete($UnionData){
		$StartIP=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'ip_start','起始ip');
		$EndIP=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'ip_end','结束ip',FALSE,NULL);
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型');
		if(empty($StartIP)){
			return FALSE;
		}
		if(!$this->IpCheck($StartIP)){
			return FALSE;
		}
		if(empty($EndIP)){
			$EndIP=$StartIP;
		}
		if(!$this->IpCheck($EndIP)){
			return FALSE;
		}
		$StartIPNumber=$this->Transform($StartIP);
		$EndIPNumber=$this->Transform($EndIP,FALSE);
		$this->Remove($Type,$StartIPNumber,$EndIPNumber);
		$this->Save($Type);
		return TRUE;
	}
	
	//IP黑名单检测
	public function Check(){
		if(!$this->Find(2,$_SERVER['REMOTE_ADDR'])&&$this->Find(1,$_SERVER['REMOTE_ADDR'])){
			if($GLOBALS['ModuleConfig_Ip']['ExitProgream']){
				Wrong::Report(__FILE__,__LINE__,'Error#M.3.3',TRUE);
			}
			else{
				return FALSE;
			}
		}
		return FALSE;
	}
	
	//导出全部记录
	public function GetAll($UnionData){
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型');
		$Return=array();
		if(strtolower($Type)=='b'){
			$ListArray=$this->BlackList;
		}
		else{
			$ListArray=$this->WhiteList;
		}
		foreach($ListArray as $Val){
			$Return[]=array(
					$this->Transform($Val[0]),
					$this->Transform($Val[1]),
					$Val[2]
				);
		}
		return $Return;
	}
	
	//查找
	public function Find($UnionData){
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型');
		$IP=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'ip','ip地址');
		if(empty($IP)){
			return FALSE;
		}
		if(!$this->IpCheck($IP)){
			return FALSE;
		}
		$IPNumber=$this->Transform($IP);
		if(strtolower($Type)=='b'){
			$ListArray=$this->BlackList;
		}
		else{
			$ListArray=$this->WhiteList;
		}
		foreach($ListArray as $Val){
			if(($IPNumber==$Val[0]||($IPNumber>$Val[0]&&$IPNumber<$Val[1]))&&(time()<=$Val[2]||empty($Val[2]))){
				return TRUE;
			}
		}
		return FALSE;
	}
	
	//清理
	public function Clean($UnionData){
		$Reset=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'reset','重置',FALSE,FALSE);
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型');
		if($Reset){
			if(strtolower($Type)=='b'||empty($Type)){
				$this->BlackList=array();
			}
			if(strtolower($Type)=='w'||empty($Type)){
				$this->WhiteList=array();
			}
		}
		else{
			if(strtolower($Type)=='b'||empty($Type)){
				foreach($this->BlackList as $Key=>$Val){
					if(!empty($Val[2])&&intval($Val[2])<time()){
						unset($this->BlackList[$Key]);
					}
				}
			}
			if(strtolower($Type)=='w'||empty($Type)){
				foreach($this->WhiteList as $Key=>$Val){
					if(!empty($Val[2])&&intval($Val[2])<time()){
						unset($this->WhiteList[$Key]);
					}
				}
			}
		}
		if(strtolower($Type)=='b'||empty($Type)){
			$this->Save('b');
		}
		if(strtolower($Type)=='w'||empty($Type)){
			$this->Save('w');
		}
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}