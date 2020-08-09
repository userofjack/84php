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

require(RootPath.'/Config/Sms.php');

class Sms{
	
	public function __construct(){
		if(!isset($_SERVER['84PHP_MODULE']['Send'])){
			require(RootPath.'/Core/Class/Module/Send.Class.php');
			$_SERVER['84PHP_MODULE']['Send']=new Send;
		}
	}

	//阿里云云通信接口特殊编码
	private function AliyunEncode($WaitEncode)
	{
		$TempEncode=urlencode($WaitEncode);
		$TempEncode=str_replace('+','%20',$TempEncode);
		$TempEncode=str_replace('*','%2A',$TempEncode);
		$TempEncode=str_replace('%7E','~',$TempEncode);
		return $TempEncode;
	}
	
	//阿里云云通信接口
	public function Aliyun($UnionData=array()){
		$Number=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'number','号码');
		$Template=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'template','模板');
		$Param=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'param','参数',FALSE,NULL);

		$PhoneNumber=NULL;
		$TempTimestamp=gmdate('Y-m-d\TH:i:s\Z');
		foreach ($Number as $Key => $Val) {
			$PhoneNumber=$PhoneNumber.$Val.',';
		}
		$RecNum=substr($PhoneNumber,0,-1);
		$GetArray=array(
				'AccessKeyId'=>$_SERVER['84PHP_CONFIG']['Sms']['AliyunAccessKeyID'],
				'Timestamp'=>$TempTimestamp,
				'SignatureMethod'=>'HMAC-SHA1',
				'SignatureVersion'=>'1.0',
				'SignatureNonce'=>uniqid(mt_rand(0,0xffff),TRUE),
				'Format'=>'JSON',

				'Action'=>'SendSms',
				'Version'=>'2017-05-25',
				'RegionId'=>$_SERVER['84PHP_CONFIG']['Sms']['AliyunRegionId'],
				'PhoneNumbers'=>$PhoneNumber,
				'SignName'=>$_SERVER['84PHP_CONFIG']['Sms']['AliyunSignName'],
				'TemplateCode'=>$Template
				);
		if(!empty($Param)){
			$JsonArray=array('TemplateParam'=>json_encode($Param));
			$GetArray=array_merge($GetArray,$JsonArray);
		}
		ksort($GetArray);

		$SortString=NULL;
		foreach ($GetArray as $Key => $Val) {
			$SortString.=$this->AliyunEncode($Key).'='.$this->AliyunEncode($Val).'&';
		}
		$SortString=substr($SortString,0,-1);
		$Signed=base64_encode(hash_hmac('sha1','GET&%2F&'.$this->AliyunEncode($SortString),$_SERVER['84PHP_CONFIG']['Sms']['AliyunAccessKeySecret']."&",TRUE));
		$SignArray=array(
				'Signature'=>$Signed
		);
		$GetArray=array_merge($GetArray,$SignArray);

		$Send=$_SERVER['84PHP_MODULE']['Send']->Get(array(
			'url'=>'http://dysmsapi.aliyuncs.com/',
			'data'=>$GetArray,
			'header'=>'x-sdk-client: php/2.0.0'));
		$Send=json_decode($Send);
		$Send=$Send->Code;
		if($Send=='OK'){
			$Send=TRUE;
		}
		else{
			$Send=FALSE;
		}
		return $Send;
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}