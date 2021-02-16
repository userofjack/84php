<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

require(RootPath.'/Config/Receive.php');

class Receive{

	//安全检测模块
	public static function SafeCheck($UnionData=[]) {
		$WillCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'string','字符串');

		$Return=$WillCheck;
		foreach ($_SERVER['84PHP_CONFIG']['Receive']['DangerChar'] as $Key=>$Val) {
			$Return=str_replace($Key,$Val,$Return);
		}
		if($_SERVER['84PHP_CONFIG']['Receive']['KillEmoji']){
			$Return=preg_replace_callback('/./u',function($TempArray){
				if(strlen($TempArray[0])>=4){
					return NULL;
				}
				return $TempArray[0];
			},$Return);
		}
		return $Return;
	}
	
	private static function M_12_0_Check($OpArray,$Value){
		if(isset($OpArray[1])&&strtoupper($OpArray[1])=='TRUE'&&(empty($Value)&&$Value!='0')){
			return FALSE;
		}
		return TRUE;
	}

	private static function M_12_1_Check($OpArray,$Value){
		if(isset($OpArray[1])&&strtoupper($OpArray[1])=='TRUE')
		{
			$StrLen=mb_strlen($Value);
			if(
			(isset($OpArray[2])&&$StrLen<intval($OpArray[2]))||
			(isset($OpArray[3])&&$StrLen>intval($OpArray[3])))
			{
				Wrong::Report(__FILE__,__LINE__,'Error#M.12.1'."\r\n\r\n @ ".$OpArray[0],FALSE,400);
			}
		}
	}

	//Post接收
	public static function Post($UnionData=[]){
		$FieldCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$SafeCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'safe_check','安全检查',FALSE,TRUE);
		$Decode=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'decode','解码',FALSE,TRUE);
		
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				if(!isset($_POST[$TempOp[0]])||!self::M_12_0_Check($TempOp,$_POST[$TempOp[0]])){
					Wrong::Report(__FILE__,__LINE__,'Error#M.12.0'."\r\n\r\n @ ".$TempOp[0],FALSE,400);
				}
				self::M_12_1_Check($TempOp,$_POST[$TempOp[0]]);
			}
		}
		
		$Return=[];
		if($SafeCheck){
			foreach ($_POST as $Key=>$Val) {
				if($Decode){
					$Return[$Key]=self::SafeCheck(['string'=>urldecode($Val)]);
				}
				else{
					$Return[$Key]=self::SafeCheck(['string'=>$Val]);
				}
			}
		}
		else{
			$Return=$_POST;
		}
		return $Return;
	}
	
	//Get接收
	public static function Get($UnionData=[]){
		$FieldCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$SafeCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'safe_check','安全检查',FALSE,TRUE);
		$Decode=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'decode','解码',FALSE,TRUE);
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				if(!isset($_GET[$TempOp[0]])||!self::M_12_0_Check($TempOp,$_GET[$TempOp[0]])){
					Wrong::Report(__FILE__,__LINE__,'Error#M.12.0'."\r\n\r\n @ ".$TempOp[0],FALSE,400);
				}
				self::M_12_1_Check($TempOp,$_GET[$TempOp[0]]);				
			}
		}
		$Return=[];
		if($SafeCheck){
			foreach ($_GET as $Key=>$Val) {
				if($Decode){
					$Return[$Key]=self::SafeCheck(['string'=>urldecode($Val)]);
				}
				else{
					$Return[$Key]=self::SafeCheck(['string'=>$Val]);
				}
			}
		}
		else{
			$Return=$_GET;
		}
		return $Return;
	}
	
	//Header接收过滤
	public static function Header($UnionData=[]){
		$FieldCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$SafeCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'safe_check','安全检查',FALSE,TRUE);

		$Return=[];
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				$KeyName='HTTP_'.str_replace('-','_',strtoupper($TempOp[0]));
				
				if(!isset($_SERVER[$KeyName])||!self::M_12_0_Check($TempOp,$_SERVER[$KeyName])){
					Wrong::Report(__FILE__,__LINE__,'Error#M.12.0'."\r\n\r\n @ ".$KeyName,FALSE,400);
				}
				self::M_12_1_Check($TempOp,$_SERVER[$KeyName]);
				
				if($SafeCheck){
					$Return[$TempOp[0]]=self::SafeCheck(['string'=>$_SERVER[$KeyName]]);
				}
				else{
					$Return[$TempOp[0]]=$_SERVER[$KeyName];
				}
			}
		}
		return $Return;
	}
	
	//Cookie过滤接收
	public static function Cookie($UnionData=[]){
		$FieldCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$SafeCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'safe_check','安全检查',FALSE,TRUE);

		$Return=[];
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				
				if(!isset($_COOKIE[$TempOp[0]])||!self::M_12_0_Check($TempOp,$_COOKIE[$TempOp[0]])){
					Wrong::Report(__FILE__,__LINE__,'Error#M.12.0'."\r\n\r\n @ ".$TempOp[0],FALSE,400);
				}
				self::M_12_1_Check($TempOp,$_COOKIE[$TempOp[0]]);
			}
		}
		if($SafeCheck){
			foreach ($_COOKIE as $Key=>$Val) {
				$Return[$Key]=self::SafeCheck(['string'=>$Val]);
			}
		}
		else{
			$Return=$_COOKIE;
		}
		return $Return;
	}

	//Json过滤
	public static function Json($UnionData=[]){
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
				
				if(!isset($TempArray[$TempOp[0]])||!self::M_12_0_Check($TempOp,$TempArray[$TempOp[0]])){
					Wrong::Report(__FILE__,__LINE__,'Error#M.12.0'."\r\n\r\n @ ".$TempOp[0],FALSE,400);
				}
				self::M_12_1_Check($TempOp,$TempArray[$TempOp[0]]);
			}
		}
		$Return=[];
		if($SafeCheck){
			foreach ($TempArray as $Key=>$Val) {
				$Return[$Key]=self::SafeCheck(['string'=>$Val]);
			}
		}
		else{
			$Return=$TempArray;
		}
		return $Return;
	}

	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}