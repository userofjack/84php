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

require(RootPath.'/Config/Tool.php');

class Tool{

	//随机字符
	public function Random($UnionData=array()){
		$Mode=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'mode','模式',FALSE,'AaN');
		$StringLength=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'length','长度',FALSE,32);

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
	public function Uuid($UnionData=array()){
		$MD5=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'md5','md5',FALSE,FALSE);
		$Return=md5(memory_get_usage().$this->Random().uniqid('', true).mt_rand(1,99999).$_SERVER['REMOTE_ADDR']);
		
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

	
	//设置Token
	public function Token($UnionData=array()){
		if(!isset($_SESSION)){
			session_start();
		}
		$Token=$this->Uuid(array('md5'=>TRUE));
		
		$NowTime=Runtime;

		$_SESSION['Token']=array(
								'token'=>$Token,
								'time'=>$NowTime
							);
		return $Token;
	}
	
	//允许事件的字符还原
	private function ReTag($WaitReplace){
		$Return=str_replace(array('＜','＞','＆','＃'),array('<','>','&','#'),$WaitReplace[0]);
		return $Return;
	}
	//不允许事件的字符还原
	private function SafeReTag($WaitReplace){
		$Return=str_replace(array('＜','＞','＆','＃',';','(',')'),array('<','>','&','#','；','（','）'),$WaitReplace[0]);
		return $Return;
	}
	
	//还原HTML标记
	public function Html($UnionData=array()){
		$String=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'string','字符串',FALSE,'AaN');
		$Tag_other=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'tag_other','其它标记',FALSE,NULL);
		$Event=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'event','事件',FALSE,FALSE);

		$AllowTag=$_SERVER['84PHP_CONFIG']['Tool']['HtmlTag'];
		if(!empty($Tag_other)){
			$AllowTag.='|'.$Tag_other;
		}
		
		$StringArray=array(
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
		);
		foreach ($StringArray as $Key=>$Val) {
			$String=str_replace($Key,$Val,$String);
		}
		if($Event){
			$TagFunction='ReTag';
		}
		else{
			$TagFunction='SafeReTag';
		}
		$String=preg_replace_callback('/＜('.$AllowTag.')(.*?)＞/i',array($this,$TagFunction),$String);
		$String=preg_replace_callback('/＜\/('.$AllowTag.')＞/i',array($this,$TagFunction),$String);

		return $String;
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}