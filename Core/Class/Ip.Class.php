<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

require(RootPath.'/Config/Ip.php');

Class Ip{
	private static $BlackListFile;
	private static $WhiteListFile;
	private static $BlackList;
	private static $WhiteList;


	public static function ClassInitial(){
		
		self::$BlackListFile=RootPath.'/Temp/ip-blacklist.php';
		self::$WhiteListFile=RootPath.'/Temp/ip-whitelist.php';
		if(!file_exists(self::$BlackListFile)){
			if(!file_put_contents(self::$BlackListFile,'<?php exit; ?>')){
				Wrong::Report(__FILE__,__LINE__,'Error#M.3.0');
			}
		}
		if(!file_exists(self::$WhiteListFile)){
			if(!file_put_contents(self::$WhiteListFile,'<?php exit; ?>')){
				Wrong::Report(__FILE__,__LINE__,'Error#M.3.0');
			}
		}
		$BlackListText=file_get_contents(self::$BlackListFile);
		$WhiteListText=file_get_contents(self::$WhiteListFile);
		if($BlackListText===FALSE||$WhiteListText===FALSE){
			Wrong::Report(__FILE__,__LINE__,'Error#M.3.1');
		}
		
		self::$BlackList=self::TextToArray($BlackListText);
		self::$WhiteList=self::TextToArray($WhiteListText);
	}
	
	//格式检测
	private static function IpCheck($Str){
		if(preg_match('/(?=(\b|\D))((\*\.)|(\*)|(25[0-5]|2[0-4]\d|[01]?\d\d?)($|(?!\.$)\.)){4}/',$Str)){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	
	//转换
	private static function Transform($Str,$Start=TRUE){
		if(ctype_digit($Str)){
			return long2ip($Str);
		}
		if($Start){
			$Str=str_replace('*','0',$Str);
		}
		else{
			$Str=str_replace('*','255',$Str);
		}
		$IntIP=ip2long($Str);
		if($IntIP===FALSE){
			return FALSE;
		}
		return sprintf('%u',$IntIP);
	}
	
	//文本转数组
	private static function TextToArray($Str){
		$Str=preg_replace('/[^0-9.\-\*,&]/','',$Str);;
		$FirstStep=explode('&',$Str);
		$SecondStep=[];
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
	private static function ArrayToText($Array){
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
	private static function Remove($Type,$StartIPNumber,$EndIPNumber){
		if(strtolower($Type)=='b'){
			$ListArray=self::$BlackList;
		}
		else{
			$ListArray=self::$WhiteList;
		}
		foreach($ListArray as $Key=>$Val){
			if($StartIPNumber==$Val[0]&&$EndIPNumber==$Val[1]){
				if(strtolower($Type)=='b'){
					unset(self::$BlackList[$Key]);
				}
				else{
					unset(self::$WhiteList[$Key]);
				}
			}
		}
		return TRUE;
	}	
	
	//写入文件
	private static function Save($UnionData=[]){
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型',FALSE,'b');
		if(strtolower($Type)=='b'){
			$ListText=self::ArrayToText(self::$BlackList);
			$Handle=@fopen(self::$BlackListFile,'w');
			if(!$Handle){
				Wrong::Report(__FILE__,__LINE__,'Error#M.3.2');
			}
		}
		else{
			$ListText=self::ArrayToText(self::$WhiteList);
			$Handle=@fopen(self::$WhiteListFile,'w');
			if(!$Handle){
				Wrong::Report(__FILE__,__LINE__,'Error#M.3.2');
			}
		}
		fwrite($Handle,'<?php exit; ?>'.$ListText);
		fclose($Handle);
	}
	
	//添加
	public static function Add($UnionData=[]){
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型');
		$StartIP=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'ip_start','起始ip');
		$EndIP=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'ip_end','结束ip',FALSE,NULL);
		$ExpTime=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'exp_time','过期时间',FALSE,NULL);
		if(empty($StartIP)){
			return FALSE;
		}
		if(ip2long($StartIP)===FALSE){
			return FALSE;
		}
		if(empty($EndIP)){
			$EndIP=$StartIP;
		}
		if(ip2long($EndIP)===FALSE){
			return FALSE;
		}
		if(!empty($ExpTime)&&intval($ExpTime)<Runtime){
			return FALSE;
		}
		$StartIPNumber=self::Transform($StartIP);
		$EndIPNumber=self::Transform($EndIP,FALSE);
		self::Remove($Type,$StartIPNumber,$EndIPNumber);
		if(strtolower($Type)=='b'){
			self::$BlackList[]=[$StartIPNumber,$EndIPNumber,$ExpTime];
		}
		else{
			self::$WhiteList[]=[$StartIPNumber,$EndIPNumber,$ExpTime];
		}
		self::Save($Type);
		return TRUE;
	}
	
	//移除
	public static function Delete($UnionData=[]){
		$StartIP=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'ip_start','起始ip');
		$EndIP=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'ip_end','结束ip',FALSE,NULL);
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型');
		if(empty($StartIP)){
			return FALSE;
		}
		if(ip2long($StartIP)===FALSE){
			return FALSE;
		}
		if(empty($EndIP)){
			$EndIP=$StartIP;
		}
		if(ip2long($EndIP)===FALSE){
			return FALSE;
		}
		$StartIPNumber=self::Transform($StartIP);
		$EndIPNumber=self::Transform($EndIP,FALSE);
		self::Remove($Type,$StartIPNumber,$EndIPNumber);
		self::Save($Type);
		return TRUE;
	}
	
	//IP黑名单检测
	public static function Check($UnionData=[]){
		if(!self::Find(2,$_SERVER['REMOTE_ADDR'])&&self::Find(1,$_SERVER['REMOTE_ADDR'])){
			if($_SERVER['84PHP_CONFIG']['Ip']['ExitProgream']){
				Wrong::Report(__FILE__,__LINE__,'Error#M.3.3');
			}
			else{
				return FALSE;
			}
		}
		return FALSE;
	}
	
	//导出全部记录
	public static function GetAll($UnionData=[]){
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型');
		$Return=[];
		if(strtolower($Type)=='b'){
			$ListArray=self::$BlackList;
		}
		else{
			$ListArray=self::$WhiteList;
		}
		foreach($ListArray as $Val){
			$Return[]=[
					self::Transform($Val[0]),
					self::Transform($Val[1]),
					$Val[2]
				];
		}
		return $Return;
	}
	
	//查找
	public static function Find($UnionData=[]){
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型');
		$IP=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'ip','ip地址');
		if(empty($IP)){
			return FALSE;
		}
		if(ip2long($IP)===FALSE){
			return FALSE;
		}
		$IPNumber=self::Transform($IP);
		if(strtolower($Type)=='b'){
			$ListArray=self::$BlackList;
		}
		else{
			$ListArray=self::$WhiteList;
		}
		foreach($ListArray as $Val){
			if(($IPNumber==$Val[0]||($IPNumber>$Val[0]&&$IPNumber<$Val[1]))&&(Runtime<=$Val[2]||empty($Val[2]))){
				return TRUE;
			}
		}
		return FALSE;
	}
	
	//清理
	public static function Clean($UnionData=[]){
		$Reset=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'reset','重置',FALSE,FALSE);
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型');
		if($Reset){
			if(strtolower($Type)=='b'||empty($Type)){
				self::$BlackList=[];
			}
			if(strtolower($Type)=='w'||empty($Type)){
				self::$WhiteList=[];
			}
		}
		else{
			if(strtolower($Type)=='b'||empty($Type)){
				foreach(self::$BlackList as $Key=>$Val){
					if(!empty($Val[2])&&intval($Val[2])<Runtime){
						unset(self::$BlackList[$Key]);
					}
				}
			}
			if(strtolower($Type)=='w'||empty($Type)){
				foreach(self::$WhiteList as $Key=>$Val){
					if(!empty($Val[2])&&intval($Val[2])<Runtime){
						unset(self::$WhiteList[$Key]);
					}
				}
			}
		}
		if(strtolower($Type)=='b'||empty($Type)){
			self::Save('b');
		}
		if(strtolower($Type)=='w'||empty($Type)){
			self::Save('w');
		}
	}
	
	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}
Ip::ClassInitial();