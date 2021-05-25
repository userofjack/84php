<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.1.0
*/

require(RootPath.'/Config/Receive.php');

class Receive{
	
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
				Wrong::Report(['detail'=>'Error#M.12.1'."\r\n\r\n @ ".$OpArray[0],'code'=>'M.12.1']);
			}
		}
	}

	//Post接收
	public static function Post($UnionData=[]){
		$FieldCheck=QuickParamet($UnionData,'field','字段',FALSE,NULL);
		$Decode=QuickParamet($UnionData,'decode','解码',FALSE,TRUE);
		
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				if(!isset($_POST[$TempOp[0]])||!self::M_12_0_Check($TempOp,$_POST[$TempOp[0]])){
					Wrong::Report(['detail'=>'Error#M.12.0'."\r\n\r\n @ ".$TempOp[0],'code'=>'M.12.0']);
				}
				self::M_12_1_Check($TempOp,$_POST[$TempOp[0]]);
			}
		}
		return $_POST;
	}
	
	//Get接收
	public static function Get($UnionData=[]){
		$FieldCheck=QuickParamet($UnionData,'field','字段',FALSE,NULL);
		$Decode=QuickParamet($UnionData,'decode','解码',FALSE,TRUE);
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				if(!isset($_GET[$TempOp[0]])||!self::M_12_0_Check($TempOp,$_GET[$TempOp[0]])){
					Wrong::Report(['detail'=>'Error#M.12.0'."\r\n\r\n @ ".$TempOp[0],'code'=>'M.12.0']);
				}
				self::M_12_1_Check($TempOp,$_GET[$TempOp[0]]);				
			}
		}
		return $_GET;
	}
	
	//Header接收过滤
	public static function Header($UnionData=[]){
		$FieldCheck=QuickParamet($UnionData,'field','字段',FALSE,NULL);

		$Return=[];
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				$KeyName='HTTP_'.str_replace('-','_',strtoupper($TempOp[0]));
				
				if(!isset($_SERVER[$KeyName])||!self::M_12_0_Check($TempOp,$_SERVER[$KeyName])){
					Wrong::Report(['detail'=>'Error#M.12.0'."\r\n\r\n @ ".$KeyName,'code'=>'M.12.0']);
				}
				self::M_12_1_Check($TempOp,$_SERVER[$KeyName]);
				
				$Return[$TempOp[0]]=$_SERVER[$KeyName];
			}
		}
		return $Return;
	}
	
	//Cookie过滤接收
	public static function Cookie($UnionData=[]){
		$FieldCheck=QuickParamet($UnionData,'field','字段',FALSE,NULL);

		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				
				if(!isset($_COOKIE[$TempOp[0]])||!self::M_12_0_Check($TempOp,$_COOKIE[$TempOp[0]])){
					Wrong::Report(['detail'=>'Error#M.12.0'."\r\n\r\n @ ".$TempOp[0],'code'=>'M.12.0']);
				}
				self::M_12_1_Check($TempOp,$_COOKIE[$TempOp[0]]);
			}
		}
		return $_COOKIE;
	}

	//Json过滤
	public static function Json($UnionData=[]){
		$JsonString=QuickParamet($UnionData,'srting','字符串');
		$FieldCheck=QuickParamet($UnionData,'field','字段',FALSE,NULL);
		
		$Return=@json_decode($JsonString,TRUE);
		if($Return==NULL){
			return FALSE;
		}
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				
				if(!isset($Return[$TempOp[0]])||!self::M_12_0_Check($TempOp,$Return[$TempOp[0]])){
					Wrong::Report(['detail'=>'Error#M.12.0'."\r\n\r\n @ ".$TempOp[0],'code'=>'M.12.0']);
				}
				self::M_12_1_Check($TempOp,$Return[$TempOp[0]]);
			}
		}

		return $Return;
	}

	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}