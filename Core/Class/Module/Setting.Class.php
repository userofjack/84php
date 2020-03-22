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

  框架版本号：4.0.1
*/

Class Setting{
	
	//检查配置文件
	private function FileCheck($Module){
		$FilePath=RootPath.'/Config/'.ucfirst($Module).'.php';
		if(file_exists($FilePath)){
			return TRUE;
		}
		Wrong::Report(__FILE__,__LINE__,'Error#M.9.1');
	}
		
	//数组转字符串
	private function ArrayToStr($Array){
		$TempText='array('."\r\n";
		foreach($Array as $Key => $Val){
			if(is_string($Key)){
				$TempText.='\''.str_replace("'",'"',$Key).'\'=>';
			}
			else{
				$TempText.=$Key.'=>';
			}
			if(is_string($Val)){
				$TempText.='\''.str_replace("'",'"',$Val).'\','."\r\n";
			}
			else if(is_bool($Val)){
				if($Val){
					$TempText.='TRUE,'."\r\n";
				}
				else{
					$TempText.='FALSE,'."\r\n";
				}
			}
			else if(is_array($Val)){
				$TempText.=$this->ArrayToStr($Val).','."\r\n";
			}
			else if(is_int($Val)||is_float($Val)){
				$TempText.=$Val.','."\r\n";
			}
			else{
				$TempText.='\'\','."\r\n";
			}
		}
		$TempText=str_replace("\r\n","\r\n	",$TempText);
		$TempText=rtrim($TempText,'	'); 
		$TempText.=')';
		$TempText=str_replace(",\r\n)","\r\n)",$TempText);
		return $TempText;
	}
	
	//变量转字符串
	private function VarToStr($ValueName,$Value){
		if(is_string($Value)){
			return '\''.$ValueName.'\'=>\''.str_replace("'",'\\\'',$Value).'\','."\r\n";
		}
		else if(is_bool($Value)){
			if($Value){
				return '\''.$ValueName.'\'=>TRUE,'."\r\n";
			}
			else{
				return '\''.$ValueName.'\'=>FALSE,'."\r\n";
			}
		}
		else if(is_array($Value)){
			return '\''.$ValueName.'\'=>'.$this->ArrayToStr($Value).','."\r\n";
		}
		else if(is_int($Value)||is_float($Value)){
			return '\''.$ValueName.'\'=>'.$Value.','."\r\n";
		}
		else{
			return '\''.$ValueName.'\'=>\'\','."\r\n";
		}
	}

	//获取配置项的值
	public function Get($UnionData=array()){
		$Module=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'module','模块');
		$Name=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'name','名称');
		
		$this->FileCheck($Module);
		require_once(RootPath.'/Config/'.ucfirst($Module).'.php');
		if(isset($_SERVER['84PHP_CONFIG'][$Module][$Name])){
			return $_SERVER['84PHP_CONFIG'][$Module][$Name];
		}
		Wrong::Report(__FILE__,__LINE__,'Error#M.9.2');
	}
	
	//写入配置项项
	public function Set($UnionData=array()){
		$Module=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'module','模块');
		$Name=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'name','名称');
		$Value=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'value','值');

		$CodeText=$this->FileCheck($Module);
		$OldValue=$this->Get($Module,$Name);
		require_once(RootPath.'/Config/'.ucfirst($Module).'.php');
		if(gettype($OldValue)!=gettype($Value)){
			Wrong::Report(__FILE__,__LINE__,'Error#M.9.3');
		}
		$CodeText='<?php'."\r\n".'$_SERVER['84PHP_CONFIG']['Pay']=array('."\r\n";
		foreach($_SERVER['84PHP_CONFIG'][$Module] as $Key => $Val){
			$CodeText.='	';
			if($Key!=$Name){
				$CodeText.=$this->VarToStr($Name,$Val);
			}
			else{
				$CodeText.=$this->VarToStr($Name,$Value);
			}
		}
		$CodeText.="\r\n);";
		$Handle=@fopen(RootPath.'/Config/'.ucfirst($Module).'.php','w');
		if(!$Handle){
			Wrong::Report(__FILE__,__LINE__,'Error#M.9.4');
		}
		fwrite($Handle,$CodeText);
		fclose($Handle);
	}
	
	//临时改变配置项
	public function Change($UnionData=array()){
		$Module=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'module','模块');
		$Name=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'name','名称');
		$Value=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'value','值');
				
		if(!isset($_SERVER['84PHP_CONFIG'][$Module])){
			Wrong::Report(__FILE__,__LINE__,'Error#M.9.4');
		}
		if(!isset($_SERVER['84PHP_CONFIG'][$Module][$Name])){
			Wrong::Report(__FILE__,__LINE__,'Error#M.9.2');
		}
		if(gettype($_SERVER['84PHP_CONFIG'][$Module][$Name])!=gettype($Value)){
			Wrong::Report(__FILE__,__LINE__,'Error#M.9.3');
		}
		$_SERVER['84PHP_CONFIG'][$Module][$Name]=$Value;
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}