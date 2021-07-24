<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.1.0
*/

require(RootPath.'/Config/Filter.php');

class Filter{
	
	//非空检查
	private static function EmptyCheck($OpArray,$Value){
		if(isset($OpArray[0])&&strtoupper($OpArray[0])=='TRUE'&&($Value===''||$Value===NULL||$Value===[])){
			return FALSE;
		}
		return TRUE;
	}
	
	//长度检查
	private static function LengthCheck($OpArray,$Value){
		$Value=strval($Value);
		$StrLen=mb_strlen($Value);
		if(
		(isset($OpArray[1])&&$StrLen<intval($OpArray[1]))||
		(isset($OpArray[2])&&intval($OpArray[2])>0&&$StrLen>intval($OpArray[2])))
		{
			return FALSE;
		}
		return TRUE;
	}
	
	//指定规则检查
	private static function RuleCheck($OpArray,$Value){
		if(empty($OpArray[3])||empty($Value)){
			return TRUE;
		}
		if($OpArray[3]=='email'){
			return filter_var($Value, FILTER_VALIDATE_EMAIL);
		}
		if($OpArray[3]=='ip'){
			return filter_var($Value, FILTER_VALIDATE_IP);
		}
		$RuleName=$OpArray[3];
		if(!empty($_SERVER['84PHP_CONFIG']['Filter']['Rule'][$RuleName])){
			if(preg_match($_SERVER['84PHP_CONFIG']['Filter']['Rule'][$RuleName],$Value)==0){
				return FALSE;
			}
		}
		return TRUE;
	}

	//按模式检查
	public static function ByMode($UnionData=[]){
		$Field=QuickParamet($UnionData,'field','字段');
		$Optional=QuickParamet($UnionData,'optional','可选',FALSE,[]);
		$Mode=QuickParamet($UnionData,'mode','模式');
		$Mode=strtolower($Mode);
		if($Mode!='get'&&$Mode!='post'&&$Mode!='cookie'&&$Mode!='header'){
			Wrong::Report(['detail'=>'Error#M.18.0'."\r\n\r\n @ ".$TempOp[0],'code'=>'M.18.0']);
		}
		foreach($Field as $Key => $Val){
			$TempOp=explode(',',$Val);
			$TempData=FALSE;
			if($Mode=='post'&&isset($_POST[$Key])){
				$TempData=$_POST[$Key];
			}
			else if($Mode=='get'&&isset($_GET[$Key])){
				$TempData=$_GET[$Key];
			}
			else if($Mode=='cookie'&&isset($_COOKIE[$Key])){
				$TempData=$_COOKIE[$Key];
			}
			else if($Mode=='header'){
				$KeyName='HTTP_'.str_replace('-','_',strtoupper($Key));
				if(isset($_SERVER[$KeyName])){
					$TempData=$_SERVER[$KeyName];
				}
			}

			if($TempData===FALSE&&!in_array($Key,$Optional)){
				return FALSE;
			}
			if(!self::EmptyCheck($TempOp,$TempData)||!self::LengthCheck($TempOp,$TempData)||!self::RuleCheck($TempOp,$TempData)){
				return FALSE;
			}

		}
		return TRUE;
	}

	//从数据检查
	public static function ByData($UnionData=[]){
		$Data=QuickParamet($UnionData,'data','数据');
		$Check=QuickParamet($UnionData,'check','校验');
		
		$Operate=explode(',',$Data);

		if(!self::EmptyCheck($Check,$Data)||!self::LengthCheck($Check,$Data)||!self::RuleCheck($Check,$Data)){
			return FALSE;
		}
		return TRUE;
	}

	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}