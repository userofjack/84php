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

require(RootPath."/Config/Receive.php");

class Receive{

	public function __construct(){
		if(!empty($_SESSION['ModuleSetting'][__CLASS__])&&is_array($_SESSION['ModuleSetting'][__CLASS__])){
			foreach($_SESSION['ModuleSetting'][__CLASS__] as $ModuleSettingKey => $ModuleSettingVal){
				$GLOBALS['ModuleConfig_Receive'][$ModuleSettingKey]=$ModuleSettingVal;
			}
		}
	}

	//来源检测
	public function FromCheck($UnionData){
		$TokenCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'token_check','token检查',FALSE,FALSE);
		$UnsetToken=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'unset_token','清除token',FALSE,TRUE);

		if (!isset($_SERVER['HTTP_REFERER'])){
			Wrong::Report(__FILE__,__LINE__,'Error#B.1.0',TRUE,400);
		}
		if ($GLOBALS['ModuleConfig_Receive']['BeforeDomainCheck']&&parse_url($_SERVER['HTTP_REFERER'])['host']!=$_SERVER['SERVER_NAME']){
			Wrong::Report(__FILE__,__LINE__,'Error#B.1.1',TRUE,400);
		}
		if($TokenCheck){
			if(isset($_POST['Token'],$_GET['Token'])){
				Wrong::Report(__FILE__,__LINE__,'Error#B.1.2',TRUE,400);
			}
			if(!isset($_SESSION['Token'])){
				Wrong::Report(__FILE__,__LINE__,'Error#B.1.3',TRUE,400);
			}
			if((isset($_POST['Token'])&&$_POST['Token']!=$_SESSION['Token']['token'])||(isset($_GET['Token'])&&$_GET['Token']!=$_SESSION['Token']['token'])||$_SESSION['Token']['time']+$GLOBALS['ModuleConfig_Receive']['TokenExpTime']<time()){
				Wrong::Report(__FILE__,__LINE__,'Error#B.1.4',TRUE,401);
			}
			if($UnsetToken){
				unset($_SESSION['Token']);
			}
		}
	}

	//安全检测模块
	public function SafeCheck($UnionData) {
		$WillCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'string','字符串');

		$Return=$WillCheck;
		foreach ($GLOBALS['ModuleConfig_Receive']['DangerChar'] as $Key=>$Val) {
			$Return=str_replace($Key,$Val,$Return);
		}
		if($GLOBALS['ModuleConfig_Receive']['KillEmoji']){
			$Return=preg_replace_callback('/./u',function($TempArray){
				if(strlen($TempArray[0])>=4){
					return NULL;
				}
				return $TempArray[0];
			},$Return);
		}
		return $Return;
	}
	
	//Post接收
	public function Post($UnionData){
		$TokenCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'token_check','token检查',FALSE,FALSE);
		$FieldCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$FromCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'from_check','来源检查',FALSE,TRUE);
		$SafeCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'safe_check','安全检查',FALSE,TRUE);
		$Decode=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'decode','解码',FALSE,TRUE);
		
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				if((!isset($_POST[$TempOp[0]]))||(isset($TempOp[1])&&strtoupper($TempOp[1])=='TRUE'&&empty($_POST[$TempOp[0]]))){
					Wrong::Report(__FILE__,__LINE__,'Error#B.1.5 @ '.$TempOp[0],FALSE,400);
				}
				if((isset($TempOp[2])&&mb_strlen($_POST[$TempOp[0]])<intval($TempOp[2]))||(isset($TempOp[3])&&mb_strlen($_POST[$TempOp[0]])>intval($TempOp[3]))){
					Wrong::Report(__FILE__,__LINE__,'Error#B.1.6 @ '.$TempOp[0],FALSE,400);
				}
			}
		}
		if($FromCheck==TRUE){
			$this->FromCheck(array('token_check'=>$TokenCheck));
		}
		
		$Return=array();
		if($SafeCheck){
			foreach ($_POST as $Key=>$Val) {
				if($Decode){
					$Return[$Key]=$this->SafeCheck(array('string'=>urldecode($Val)));
				}
				else{
					$Return[$Key]=$this->SafeCheck(array('string'=>$Val));
				}
			}
		}
		else{
			$Return=$_POST;
		}
		return $Return;
	}
	
	//Get接收
	public function Get($UnionData){
		$TokenCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'token_check','token检查',FALSE,FALSE);
		$FieldCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$FromCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'from_check','来源检查',FALSE,TRUE);
		$SafeCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'safe_check','安全检查',FALSE,TRUE);
		$Decode=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'decode','解码',FALSE,TRUE);
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				if((!isset($_GET[$TempOp[0]]))||(isset($TempOp[1])&&strtoupper($TempOp[1])=='TRUE'&&empty($_GET[$TempOp[0]]))){
					Wrong::Report(__FILE__,__LINE__,'Error#B.1.5 @ '.$TempOp[0],FALSE,400);
				}
				if((isset($TempOp[2])&&mb_strlen($_GET[$TempOp[0]])<intval($TempOp[2]))||(isset($TempOp[3])&&mb_strlen($_GET[$TempOp[0]])>intval($TempOp[3]))){
					Wrong::Report(__FILE__,__LINE__,'Error#B.1.6 @ '.$TempOp[0],FALSE,400);
				}
			}
		}
		if($FromCheck==TRUE){
			$this->FromCheck(array('token_check'=>$TokenCheck));
		}
		$Return=array();
		if($SafeCheck){
			foreach ($_GET as $Key=>$Val) {
				if($Decode){
					$Return[$Key]=$this->SafeCheck(array('string'=>urldecode($Val)));
				}
				else{
					$Return[$Key]=$this->SafeCheck(array('string'=>$Val));
				}
			}
		}
		else{
			$Return=$_GET;
		}
		return $Return;
	}
	
	//Header接收过滤
	public function Header($UnionData){
		$FieldCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$SafeCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'safe_check','安全检查',FALSE,TRUE);

		$Return=array();
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				if((!isset($_SERVER['HTTP_'.str_replace('-','_',strtoupper($TempOp[0]))]))||(isset($TempOp[1])&&strtoupper($TempOp[1])=='TRUE'&&empty($_SERVER['HTTP_'.str_replace('-','_',strtoupper($TempOp[0]))]))){
					Wrong::Report(__FILE__,__LINE__,'Error#B.1.5 @ '.$TempOp[0],FALSE,400);
				}
				$ReturnValue=$_SERVER['HTTP_'.str_replace('-','_',strtoupper($TempOp[0]))];
				if($SafeCheck){
					$Return[$TempOp[0]]=$this->SafeCheck(array('string'=>$ReturnValue));
				}
				else{
					$Return=$ReturnValue;
				}
			}
		}
		return $Return;
	}
	
	//Cookie过滤接收
	public function Cookie($UnionData){
		$FieldCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$SafeCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'safe_check','安全检查',FALSE,TRUE);

		$Return=array();
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				if((!isset($_COOKIE[$TempOp[0]]))||(isset($TempOp[1])&&strtoupper($TempOp[1])=='TRUE'&&empty($_COOKIE[$TempOp[0]]))){
					Wrong::Report(__FILE__,__LINE__,'Error#B.1.5 @ '.$TempOp[0],FALSE,400);
				}
			}
		}
		if($SafeCheck){
			foreach ($_COOKIE as $Key=>$Val) {
				$Return[$Key]=$this->SafeCheck(array('string'=>$Val));
			}
		}
		else{
			$Return=$_COOKIE;
		}
		return $Return;
	}

	//Json过滤
	public function Json($UnionData){
		$JsonString=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'srting','字符串');
		$FieldCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$SafeCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'safe_check','安全检查',FALSE,TRUE);

		$TempArray=@json_decode($JsonString,TRUE);
		if($TempArray==NULL){
			return FALSE;
		}
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				if((!isset($TempArray[$TempOp[0]]))||(isset($TempOp[1])&&strtoupper($TempOp[1])=='TRUE'&&empty($TempArray[$TempOp[0]]))){
					Wrong::Report(__FILE__,__LINE__,'Error#B.1.5 @ '.$TempOp[0],FALSE,400);
				}
			}
		}
		$Return=array();
		if($SafeCheck){
			foreach ($TempArray as $Key=>$Val) {
				$Return[$Key]=$this->SafeCheck(array('string'=>$Val));
			}
		}
		else{
			$Return=$TempArray;
		}
		return $Return;
	}

	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}