<?php
/*****************************************************/
/*****************************************************/
/*                                                   */
/*			   84PHP-www.84php.com			  */
/*                                                   */
/*****************************************************/
/*****************************************************/

/*
  本框架为免费开源、遵循Apache2开源协议的框架，但不得删除此文件的版权信息，违者必究。
  This framework is free and open source, following the framework of Apache2 open source protocol, but the copyright information of this file is not allowed to be deleted,violators will be prosecuted to the maximum extent possible.

  ©2017-2020 Bux. All rights reserved.

  框架版本号：3.0.0
*/

require(RootPath."/Config/Pay.php");
require_once(RootPath."/Core/Class/Module/Send.Class.php");

class Pay{

	public function __construct(){
		if(!empty($_SESSION['ModuleSetting'][__CLASS__])&&is_array($_SESSION['ModuleSetting'][__CLASS__])){
			foreach($_SESSION['ModuleSetting'][__CLASS__] as $ModuleSettingKey => $ModuleSettingVal){
				$GLOBALS['ModuleConfig_Pay'][$ModuleSettingKey]=$ModuleSettingVal;
			}
		}
	}

	//获取客户端真实IP
	private function GetClientIp(){
		if(getenv('HTTP_CLIENT_IP')&&strcasecmp(getenv('HTTP_CLIENT_IP'),'unknown')){
			$ClientIp=getenv('HTTP_CLIENT_IP');
		}
		else if(getenv('HTTP_X_FORWARDED_FOR')&&strcasecmp(getenv('HTTP_X_FORWARDED_FOR'),'unknown')){
			$ClientIp=getenv('HTTP_X_FORWARDED_FOR');
		}
		else if(getenv('REMOTE_ADDR')&&strcasecmp(getenv('REMOTE_ADDR'),'unknown')){
			$ClientIp=getenv('REMOTE_ADDR');
		}
		else if(isset($_SERVER['REMOTE_ADDR'])&&$_SERVER['REMOTE_ADDR']&&strcasecmp($_SERVER['REMOTE_ADDR'],'unknown')){
			$ClientIp=$_SERVER['REMOTE_ADDR'];
		}
		else{
			$ClientIp='';
		}
		return preg_match('/[\d\.]{7,15}/',$ClientIp,$Matches)?$Matches[0]:'';
	}
	
	//支付宝支付接口
	public function Alipay($UnionData){
		$Id=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'id','编号');
		$Title=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'title','标题');
		$Total=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'total','金额');
		$QR=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'qr','二维码',FALSE,FALSE);
		$QRWidth=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'qr_width','二维码宽度',FALSE,NULL);
		
		$PostArray=array(
				'service'=>'create_direct_pay_by_user',
				'partner'=>$GLOBALS['ModuleConfig_Pay']['AliPid'],
				'_input_charset'=>'utf-8',
				'notify_url'=>$GLOBALS['ModuleConfig_Pay']['AliNotifyUrl'],
				'return_url'=>$GLOBALS['ModuleConfig_Pay']['AliReturnUrl'],
				'out_trade_no'=>$Id,
				'subject'=>$Title,	
				'payment_type'=>'1',
				'total_fee'=>intval($Total)/100,
				'seller_id'=>$GLOBALS['ModuleConfig_Pay']['AliPid'],
				'it_b_pay'=>'1h',
				);
		if($QR){
			if(!empty($QRWidth)){
				$QRArray=array('qr_pay_mode'=>'4','qrcode_width'=>$QRWidth);
				$PostArray=array_merge($PostArray,$QRArray);
			}
			else{
				$QRArray=array('qr_pay_mode'=>'3');
				$PostArray=array_merge($PostArray,$QRArray);
			}
		}
		ksort($PostArray);
		$SortString=NULL;
		foreach ($PostArray as $Key => $Val){
			$SortString.=$Key.'='.$Val.'&';
		}
		$SortString=substr($SortString, 0, -1);
		$Md5=md5($SortString.$GLOBALS['ModuleConfig_Pay']['AliKey']);
		$SortString.='&sign='.$Md5.'&sign_type=MD5';
		return 'https://mapi.alipay.com/gateway.do?'.$SortString;
	}
	//微信支付接口
	public function Wxpay($UnionData){
		$Id=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'id','编号');
		$Title=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'title','标题');
		$Total=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'total','金额');
		$Mode=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'mode','模式',FALSE,'NATIVE');
		$Ip=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'ip','ip地址',FALSE,NULL);
		$OpenID=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'openid','openid',FALSE,NULL);

		if(empty($Ip)){
			$Ip=$this->GetClientIp();
 		}
		$String=NULL;
		$Word='0123456789qwertyuiopasdfghjklzxcvbnm';
		for($n=1;$n<=31;$n++){
			$Random=mt_rand(0,34);
			$String.=$Word[$Random];
		}
		$ExpireTime=date('YmdHis',time()+3600);
		$PostArray=array(
				'appid'=>$GLOBALS['ModuleConfig_Pay']['WxAppid'],
				'mch_id'=>$GLOBALS['ModuleConfig_Pay']['WxMchId'],
				'nonce_str'=>$String,
				'body'=>$Title,
				'out_trade_no'=>$Id,
				'total_fee'=>$Total,
				'spbill_create_ip'=>$Ip,
				'time_expire'=>$ExpireTime,
				'notify_url'=>$GLOBALS['ModuleConfig_Pay']['WxNotifyUrl'],
				'trade_type'=>$Mode,
				);
		if($Mode=='JSAPI'){
			$PostArray['openid']=$OpenID;
		}
		if($Mode=='MWEB'){
			$PostArray['scene_info']=json_encode($GLOBALS['ModuleConfig_Pay']['WxSceneInfo']);
		}
		ksort($PostArray);
		$SortString=NULL;
		foreach ($PostArray as $Key => $Val){
			$SortString.=$Key.'='.$Val.'&';
		}
		$Md5=md5($SortString.'key='.$GLOBALS['ModuleConfig_Pay']['WxKey']);
		
		$Data='<?xml version=\'1.0\'?>'."\r\n".
		'<xml>'."\r\n".
		'<appid>'.$GLOBALS['ModuleConfig_Pay']['WxAppid'].'</appid>'."\r\n".
		'<mch_id>'.$GLOBALS['ModuleConfig_Pay']['WxMchId'].'</mch_id>'."\r\n".
		'<nonce_str>'.$String.'</nonce_str>'."\r\n".
		'<body>'.$Title.'</body>'."\r\n".
		'<out_trade_no>'.$Id.'</out_trade_no>'."\r\n".
		'<total_fee>'.$Total.'</total_fee>'."\r\n".
		'<spbill_create_ip>'.$Ip.'</spbill_create_ip>'."\r\n".
		'<time_expire>'.$ExpireTime.'</time_expire>'."\r\n".
		'<notify_url>'.$GLOBALS['ModuleConfig_Pay']['WxNotifyUrl'].'</notify_url>'."\r\n".
		'<trade_type>'.$Mode.'</trade_type>'."\r\n";
		if($Mode=='JSAPI'){
			$Data.='<openid>'.$OpenID.'</openid>'."\r\n";
		}
		if($Mode=='MWEB'){
			$Data.='<scene_info>'.json_encode($GLOBALS['ModuleConfig_Pay']['WxSceneInfo']).'</scene_info>'."\r\n";
		}
		$Data.='<sign>'.$Md5.'</sign>'."\r\n".
		'</xml>
		';
		$Send=new Send;
		$Send=$Send->Post(array(
			'url'=>'https://api.mch.weixin.qq.com/pay/unifiedorder',
			'data'=>$Data,
			'header'=>'Content-Type: text/xml; charset=UTF-8',
			'encode'=>TRUE,
			'timeout'=>$GLOBALS['ModuleConfig_Pay']['Timeout']));
		
		xml_parse_into_struct(xml_parser_create(),$Send,$ReturnArray);
		$Return=FALSE;
		if(empty($ReturnArray)){
			Wrong::Report(__FILE__,__LINE__,'Error#M.7.0',TRUE);
		}
		$ReturnResult=TRUE;
		foreach($ReturnArray as $Val){
			if($Val['tag']=='RETURN_CODE'&&$Val['value']!='SUCCESS'){
				$ReturnResult=FALSE;
			}
			if(!$ReturnResult&&$Val['tag']=='RETURN_MSG'){
				Wrong::Report(__FILE__,__LINE__,'Error#M.7.1 @ '.$Val['value']);
			}
			if($Val['tag']=='PREPAY_ID'&&$Mode=='JSAPI'){
				$Return=$Val['value'];
			}
			if($Val['tag']=='CODE_URL'&&$Mode=='NATIVE'){
				$Return=$Val['value'];
			}
			if($Val['tag']=='MWEB_URL'&&$Mode=='MWEB'){
				$Return=$Val['value'];
			}
		}
		return $Return;
	}
	
	//支付宝支付验签
	public function AlipayVerify(){
		$PostArray=$_POST;
		if(empty($PostArray)){
			return FALSE;
		}
		if($PostArray['trade_status']!='TRADE_SUCCESS'){
			return FALSE;
		}
		ksort($PostArray);
		$WillCheck=NULL;
		foreach($PostArray as $Key => $Val){
			if($Key!='sign'&&$Key!='sign_type'&&!empty($Val)){
				$WillCheck.=$Key.'=';
				$WillCheck.=$Val.'&';
			}
		}
		$WillCheck=substr($WillCheck, 0, -1);
		$Sign=md5($WillCheck.$GLOBALS['ModuleConfig_Pay']['AliKey']);
		if($Sign!=$PostArray['sign']){
			return FALSE;
		}
		$Send=new Send;
		$NotifyResult=$Send->Get();

		$Send=$Send->Post(array(
			'url'=>'https://mapi.alipay.com/gateway.do?service=notify_verify&partner='.$GLOBALS['ModuleConfig_Pay']['AliPid'].'&notify_id='.$PostArray['notify_id'],
			'timeout'=>$GLOBALS['ModuleConfig_Pay']['Timeout']));

		if(strtoupper($NotifyResult)=='TRUE'){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	//微信支付验签
	public function WxpayVerify($UnionData){
		$String=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'string','字符串');
		$XmlArray=json_decode(json_encode(simplexml_load_string($String,'SimpleXMLElement',LIBXML_NOCDATA)),TRUE);
		if(empty($XmlArray)){
			return FALSE;
		}
		ksort($XmlArray);
		$WillCheck=NULL;
		foreach($XmlArray as $Key => $Val){
			if($Key!='sign'&&!empty($Val)&&!is_array($Val)){
				$WillCheck.=$Key.'='.$Val.'&';
			}
		}
		$Sign=strtoupper(md5($WillCheck.'key='.$GLOBALS['ModuleConfig_Pay']['WxKey']));
		if($Sign==$XmlArray['sign']){
			return $XmlArray;
		}
		else{
			return FALSE;
		}
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}