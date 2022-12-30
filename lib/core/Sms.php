<?php
namespace core;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.1.0
*/

class Sms
{
    
    //阿里云云通信接口特殊编码
    private static function aliyunEncode($WaitEncode)
    {
        $TempEncode=urlencode($WaitEncode);
        $TempEncode=str_replace('+','%20',$TempEncode);
        $TempEncode=str_replace('*','%2A',$TempEncode);
        return str_replace('%7E','~',$TempEncode);
    }
    
    //阿里云云通信接口
    public static function aliyun($UnionData=[]): bool
    {
        $Number=Common::quickParameter($UnionData,'number','号码');
        $Template=Common::quickParameter($UnionData,'template','模板');
        $Param=Common::quickParameter($UnionData,'param','参数',FALSE);
        
        $PhoneNumber=NULL;
        $TempTimestamp=gmdate('Y-m-d\TH:i:s\Z');
        foreach ($Number as $Val) {
            $PhoneNumber=$PhoneNumber.$Val.',';
        }
        $GetArray=[
                'AccessKeyId'=>$_SERVER['84PHP']['Config']['Sms']['aliyunAccessKeyID'],
                'Timestamp'=>$TempTimestamp,
                'SignatureMethod'=>'HMAC-SHA1',
                'SignatureVersion'=>'1.0',
                'SignatureNonce'=>uniqid(mt_rand(0,0xffff),TRUE),
                'Format'=>'JSON',

                'Action'=>'SendSms',
                'Version'=>'2017-05-25',
                'RegionId'=>$_SERVER['84PHP']['Config']['Sms']['aliyunRegionId'],
                'PhoneNumbers'=>$PhoneNumber,
                'SignName'=>$_SERVER['84PHP']['Config']['Sms']['aliyunSignName'],
                'TemplateCode'=>$Template
                ];
        if (!empty($Param)) {
            $JsonArray=['TemplateParam'=>json_encode($Param)];
            $GetArray=array_merge($GetArray,$JsonArray);
        }
        ksort($GetArray);

        $SortString=NULL;
        foreach ($GetArray as $Key => $Val) {
            $SortString.=self::aliyunEncode($Key).'='.self::aliyunEncode($Val).'&';
        }
        $SortString=substr($SortString,0,-1);
        $Signed=base64_encode(hash_hmac('sha1','GET&%2F&'.self::aliyunEncode($SortString),$_SERVER['84PHP']['Config']['Sms']['aliyunAccessKeySecret']."&",TRUE));
        $SignArray=[
                'Signature'=>$Signed
        ];
        $GetArray=array_merge($GetArray,$SignArray);

        $Send=Tool::send([
            'url'=>'https://dysmsapi.aliyuncs.com/',
            'mode'=>'GET',
            'data'=>$GetArray,
            'header'=>'x-sdk-client: php/2.0.0'
        ]);
        $Send=json_decode($Send);
        $Send=$Send->Code;
        if ($Send=='OK') {
            $Send=TRUE;
        }
        else {
            $Send=FALSE;
        }
        return $Send;
    }
    
    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}