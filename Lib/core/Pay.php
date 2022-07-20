<?php
namespace core;

use core\Common;
use core\Api;
use core\Tool;


/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/

class Pay
{
    
    //获取客户端真实IP
    private static function getClientIp()
    {
        if (getenv('HTTP_CLIENT_IP')&&strcasecmp(getenv('HTTP_CLIENT_IP'),'unknown')) {
            $ClientIp=getenv('HTTP_CLIENT_IP');
        }
        else if (getenv('HTTP_X_FORWARDED_FOR')&&strcasecmp(getenv('HTTP_X_FORWARDED_FOR'),'unknown')) {
            $ClientIp=getenv('HTTP_X_FORWARDED_FOR');
        }
        else if (getenv('REMOTE_ADDR')&&strcasecmp(getenv('REMOTE_ADDR'),'unknown')) {
            $ClientIp=getenv('REMOTE_ADDR');
        }
        else if (isset($_SERVER['REMOTE_ADDR'])&&$_SERVER['REMOTE_ADDR']&&strcasecmp($_SERVER['REMOTE_ADDR'],'unknown')) {
            $ClientIp=$_SERVER['REMOTE_ADDR'];
        }
        else {
            $ClientIp='';
        }
        return preg_match('/[\d\.]{7,15}/',$ClientIp,$Matches)?$Matches[0]:'';
    }
    
    //支付宝支付接口
    public static function aliPay($UnionData=[])
    {
        $Id=Common::quickParamet($UnionData,'id','编号');
        $Title=Common::quickParamet($UnionData,'title','标题');
        $Total=Common::quickParamet($UnionData,'total','金额');
        $QR=Common::quickParamet($UnionData,'qr','二维码',FALSE,FALSE);
        $QRWidth=Common::quickParamet($UnionData,'qr_width','二维码宽度',FALSE,NULL);
        
        $PostArray=[
                'service'=>'create_direct_pay_by_user',
                'partner'=>$_SERVER['84PHP']['Config']['Pay']['aliPid'],
                '_input_charset'=>'utf-8',
                'notify_url'=>$_SERVER['84PHP']['Config']['Pay']['aliNotifyUrl'],
                'return_url'=>$_SERVER['84PHP']['Config']['Pay']['aliReturnUrl'],
                'out_trade_no'=>$Id,
                'subject'=>$Title,    
                'payment_type'=>'1',
                'total_fee'=>intval($Total)/100,
                'seller_id'=>$_SERVER['84PHP']['Config']['Pay']['aliPid'],
                'it_b_pay'=>'1h',
                ];
        if ($QR) {
            if (!empty($QRWidth)) {
                $QRArray=['qr_pay_mode'=>'4','qrcode_width'=>$QRWidth];
                $PostArray=array_merge($PostArray,$QRArray);
            }
            else {
                $QRArray=['qr_pay_mode'=>'3'];
                $PostArray=array_merge($PostArray,$QRArray);
            }
        }
        ksort($PostArray);
        $SortString=NULL;
        foreach ($PostArray as $Key => $Val) {
            $SortString.=$Key.'='.$Val.'&';
        }
        $SortString=substr($SortString, 0, -1);
        $Md5=md5($SortString.$_SERVER['84PHP']['Config']['Pay']['aliKey']);
        $SortString.='&sign='.$Md5.'&sign_type=MD5';
        return 'https://mapi.alipay.com/gateway.do?'.$SortString;
    }
    //微信支付接口
    public static function wxPay($UnionData=[])
    {
        $Id=Common::quickParamet($UnionData,'id','编号');
        $Title=Common::quickParamet($UnionData,'title','标题');
        $Total=Common::quickParamet($UnionData,'total','金额');
        $Mode=Common::quickParamet($UnionData,'mode','模式',FALSE,'NATIVE');
        $Ip=Common::quickParamet($UnionData,'ip','ip地址',FALSE,NULL);
        $OpenID=Common::quickParamet($UnionData,'openid','openid',FALSE,NULL);

        if (empty($Ip)) {
            $Ip=self::getClientIp();
         }
        $String=NULL;
        $Word='0123456789qwertyuiopasdfghjklzxcvbnm';
        for ($n=1;$n<=31;$n++) {
            $Random=mt_rand(0,34);
            $String.=$Word[$Random];
        }
        $ExpireTime=date('YmdHis',__TIME__+3600);
        $PostArray=[
                'appid'=>$_SERVER['84PHP']['Config']['Pay']['wxAppid'],
                'mch_id'=>$_SERVER['84PHP']['Config']['Pay']['wxMchId'],
                'nonce_str'=>$String,
                'body'=>$Title,
                'out_trade_no'=>$Id,
                'total_fee'=>$Total,
                'spbill_create_ip'=>$Ip,
                'time_expire'=>$ExpireTime,
                'notify_url'=>$_SERVER['84PHP']['Config']['Pay']['wxNotifyUrl'],
                'trade_type'=>$Mode,
                ];
        if ($Mode=='JSAPI') {
            $PostArray['openid']=$OpenID;
        }
        if ($Mode=='MWEB') {
            $PostArray['scene_info']=json_encode($_SERVER['84PHP']['Config']['Pay']['wxSceneInfo']);
        }
        ksort($PostArray);
        $SortString=NULL;
        foreach ($PostArray as $Key => $Val) {
            $SortString.=$Key.'='.$Val.'&';
        }
        $Md5=md5($SortString.'key='.$_SERVER['84PHP']['Config']['Pay']['wxKey']);
        
        $Data='<?xml version=\'1.0\'?>'."\r\n".
        '<xml>'."\r\n".
        '<appid>'.$_SERVER['84PHP']['Config']['Pay']['wxAppid'].'</appid>'."\r\n".
        '<mch_id>'.$_SERVER['84PHP']['Config']['Pay']['wxMchId'].'</mch_id>'."\r\n".
        '<nonce_str>'.$String.'</nonce_str>'."\r\n".
        '<body>'.$Title.'</body>'."\r\n".
        '<out_trade_no>'.$Id.'</out_trade_no>'."\r\n".
        '<total_fee>'.$Total.'</total_fee>'."\r\n".
        '<spbill_create_ip>'.$Ip.'</spbill_create_ip>'."\r\n".
        '<time_expire>'.$ExpireTime.'</time_expire>'."\r\n".
        '<notify_url>'.$_SERVER['84PHP']['Config']['Pay']['wxNotifyUrl'].'</notify_url>'."\r\n".
        '<trade_type>'.$Mode.'</trade_type>'."\r\n";
        if ($Mode=='JSAPI') {
            $Data.='<openid>'.$OpenID.'</openid>'."\r\n";
        }
        if ($Mode=='MWEB') {
            $Data.='<scene_info>'.json_encode($_SERVER['84PHP']['Config']['Pay']['wxSceneInfo']).'</scene_info>'."\r\n";
        }
        $Data.='<sign>'.$Md5.'</sign>'."\r\n".
        '</xml>
        ';

        $Send=Tool::send([
            'url'=>'https://api.mch.weixin.qq.com/pay/unifiedorder',
            'mode'=>'POST',
            'ssl'=>TRUE,
            'data'=>$Data,
            'header'=>'Content-Type: text/xml; charset=UTF-8',
            'timeout'=>$_SERVER['84PHP']['Config']['Pay']['timeout']]);
        
        xml_parse_into_struct(xml_parser_create(),$Send,$ReturnArray);
        $Return=FALSE;
        if (empty($ReturnArray)) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.7.0','code'=>'M.7.0']);
        }
        $ReturnResult=TRUE;
        foreach ($ReturnArray as $Val) {
            if ($Val['tag']=='RETURN_CODE'&&$Val['value']!='SUCCESS') {
                $ReturnResult=FALSE;
            }
            if (!$ReturnResult&&$Val['tag']=='RETURN_MSG') {
                Api::wrong(['level'=>'F','detail'=>'Error#M.7.1'."\r\n\r\n @ ".$Val['value'],'code'=>'M.7.1']);
            }
            if ($Val['tag']=='PREPAY_ID'&&$Mode=='JSAPI') {
                $Return=$Val['value'];
            }
            if ($Val['tag']=='CODE_URL'&&$Mode=='NATIVE') {
                $Return=$Val['value'];
            }
            if ($Val['tag']=='MWEB_URL'&&$Mode=='MWEB') {
                $Return=$Val['value'];
            }
        }
        return $Return;
    }
    
    //支付宝支付验签
    public static function aliPayVerify($UnionData=[])
    {
        $PostArray=$_POST;
        if (empty($PostArray)) {
            return FALSE;
        }
        if ($PostArray['trade_status']!='TRADE_SUCCESS') {
            return FALSE;
        }
        ksort($PostArray);
        $WillCheck=NULL;
        foreach ($PostArray as $Key => $Val) {
            if ($Key!='sign'&&$Key!='sign_type'&&!empty($Val)) {
                $WillCheck.=$Key.'=';
                $WillCheck.=$Val.'&';
            }
        }
        $WillCheck=substr($WillCheck, 0, -1);
        $Sign=md5($WillCheck.$_SERVER['84PHP']['Config']['Pay']['aliKey']);
        if ($Sign!=$PostArray['sign']) {
            return FALSE;
        }
        $NotifyResult=Tool::send([
            'url'=>'https://mapi.alipay.com/gateway.do?service=notify_verify&partner='.$_SERVER['84PHP']['Config']['Pay']['aliPid'].'&notify_id='.$PostArray['notify_id'],
            'mode'=>'GET',
            'ssl'=>TRUE,
            'timeout'=>$_SERVER['84PHP']['Config']['Pay']['timeout']]);

        if (strtoupper($NotifyResult)=='TRUE') {
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
    
    //微信支付验签
    public static function wxPayVerify($UnionData=[])
    {
        $String=Common::quickParamet($UnionData,'string','字符串');
        $XmlArray=json_decode(json_encode(simplexml_load_string($String,'SimpleXMLElement',LIBXML_NOCDATA)),TRUE);
        if (empty($XmlArray)) {
            return FALSE;
        }
        ksort($XmlArray);
        $WillCheck=NULL;
        foreach ($XmlArray as $Key => $Val) {
            if ($Key!='sign'&&!empty($Val)&&!is_array($Val)) {
                $WillCheck.=$Key.'='.$Val.'&';
            }
        }
        $Sign=strtoupper(md5($WillCheck.'key='.$_SERVER['84PHP']['Config']['Pay']['wxKey']));
        if ($Sign==$XmlArray['sign']) {
            return $XmlArray;
        }
        else {
            return FALSE;
        }
    }
    
    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}