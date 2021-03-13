<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

require(RootPath.'/Config/Sms.php');

class Sms{
	
	//阿里云云通信接口特殊编码
	private static function AliyunEncode($WaitEncode)
	{
		$TempEncode=urlencode($WaitEncode);
		$TempEncode=str_replace('+','%20',$TempEncode);
		$TempEncode=str_replace('*','%2A',$TempEncode);
		$TempEncode=str_replace('%7E','~',$TempEncode);
		return $TempEncode;
	}
	
	//阿里云云通信接口
	public static function Aliyun($UnionData=[]){
		$Number=QuickParamet($UnionData,'number','号码');
		$Template=QuickParamet($UnionData,'template','模板');
		$Param=QuickParamet($UnionData,'param','参数',FALSE,NULL);

		$PhoneNumber=NULL;
		$TempTimestamp=gmdate('Y-m-d\TH:i:s\Z');
		foreach ($Number as $Key => $Val) {
			$PhoneNumber=$PhoneNumber.$Val.',';
		}
		$RecNum=substr($PhoneNumber,0,-1);
		$GetArray=[
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
				];
		if(!empty($Param)){
			$JsonArray=['TemplateParam'=>json_encode($Param)];
			$GetArray=array_merge($GetArray,$JsonArray);
		}
		ksort($GetArray);

		$SortString=NULL;
		foreach ($GetArray as $Key => $Val) {
			$SortString.=self::AliyunEncode($Key).'='.self::AliyunEncode($Val).'&';
		}
		$SortString=substr($SortString,0,-1);
		$Signed=base64_encode(hash_hmac('sha1','GET&%2F&'.self::AliyunEncode($SortString),$_SERVER['84PHP_CONFIG']['Sms']['AliyunAccessKeySecret']."&",TRUE));
		$SignArray=[
				'Signature'=>$Signed
		];
		$GetArray=array_merge($GetArray,$SignArray);

		$Send=Send::Get([
			'url'=>'http://dysmsapi.aliyuncs.com/',
			'data'=>$GetArray,
			'header'=>'x-sdk-client: php/2.0.0'
		]);
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
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}