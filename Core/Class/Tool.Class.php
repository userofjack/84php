<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.1.0
*/

require(RootPath.'/Config/Tool.php');

class Tool{

	//随机字符
	public static function Random($UnionData=[]){
		$Mode=QuickParamet($UnionData,'mode','模式',FALSE,'AaN');
		$StringLength=QuickParamet($UnionData,'length','长度',FALSE,32);

		$String=NULL;
		$NWord='0123456789';
		$AUpperWord='QWERTYUIOPASDFGHJKLZXCVBNM';
		$ALowerWord='qwertyuiopasdfghjklzxcvbnm';
		$Word=NULL;
		if(strstr($Mode,'A')){
			$Word.=$AUpperWord;
		}
		if(strstr($Mode,'a')){
			$Word.=$ALowerWord;
		}
		if(strstr($Mode,'N')){
			$Word.=$NWord;
		}
		if(empty($Mode)){
			$Word=$NWord.$ALowerWord.$AUpperWord;
		}
		if(!empty($Word)){
			for($n=0;$n<$StringLength;$n++){
				$Random=mt_rand(0,strlen($Word)-1);
				$String.=$Word[$Random];
			}
		}
		return $String;
	}
	
	//生成UUID
	public static function Uuid($UnionData=[]){
		$MD5=QuickParamet($UnionData,'md5','md5',FALSE,FALSE);
		$Return=md5(memory_get_usage().self::Random().uniqid('', true).mt_rand(1,99999).$_SERVER['REMOTE_ADDR']);
		
		if(!$MD5){
			$Return=
				'{'.
				substr($Return,0,8).'-'.
				substr($Return,8,4).'-'.
				substr($Return,12,4).'-'.
				substr($Return,16,4).'-'.
				substr($Return,20,12).
				'}';
		}
		
		return $Return;
	}

	
	//允许事件的字符还原
	private static function ReTag($WaitReplace){
		$Return=str_replace(['＜','＞','＆','＃'],['<','>','&','#'],$WaitReplace[0]);
		return $Return;
	}
	//不允许事件的字符还原
	private static function SafeReTag($WaitReplace){
		$Return=str_replace(['＜','＞','＆','＃',';','(',')'],['<','>','&','#','；','（','）'],$WaitReplace[0]);
		return $Return;
	}
	
	//还原HTML标记
	public static function Html($UnionData=[]){
		$String=QuickParamet($UnionData,'string','字符串',FALSE,'AaN');
		$Tag_other=QuickParamet($UnionData,'tag_other','其它标记',FALSE,NULL);
		$Event=QuickParamet($UnionData,'event','事件',FALSE,FALSE);

		$AllowTag=$_SERVER['84PHP_CONFIG']['Tool']['HtmlTag'];
		if(!empty($Tag_other)){
			$AllowTag.='|'.$Tag_other;
		}
		
		$StringArray=[
			'（'=>'(',
			'）'=>')',
			'﹡'=>'*',
			'＇'=>'\'',
			'？'=>'?',
			'@＠'=>'@@',
			'＋'=>'+',
			'；'=>';',
			'＝'=>'=',
			'＆＃'=>'&#'
		];
		foreach ($StringArray as $Key=>$Val) {
			$String=str_replace($Key,$Val,$String);
		}
		if($Event){
			$TagFunction='ReTag';
		}
		else{
			$TagFunction='SafeReTag';
		}
		$String=preg_replace_callback('/＜('.$AllowTag.')(.*?)＞/i',[$this,$TagFunction],$String);
		$String=preg_replace_callback('/＜\/('.$AllowTag.')＞/i',[$this,$TagFunction],$String);

		return $String;
	}
	
	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}