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

require(RootPath.'/Config/Mail.php');

class Mail{

	//Jmail发送
	public function Jsend($UnionData=array()){
		$Address=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'address','地址');
		$Title=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'title','标题');
		$Content=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'content','内容');
		$Timeout=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'timeout','超时时间',FALSE,15);

		set_time_limit($Timeout);
		$JmailCOM=new COM("Jmail.Message");
		if(!$JmailCOM){
			Wrong::Report(__FILE__,__LINE__,'Error#M.5.0');
		}
		$JmailCOM->Silent=TRUE;
		$JmailCOM->Logging=TRUE;
		$JmailCOM->CharSet='utf-8';
		$JmailCOM->ContentType="Text/html";
		$JmailCOM->MailServerUsername=$_SERVER['84PHP_CONFIG']['Mail']['UserName'];
		$JmailCOM->MailServerPassword=$_SERVER['84PHP_CONFIG']['Mail']['PassWord'];
		
		$JmailCOM->FromName=$_SERVER['84PHP_CONFIG']['Mail']['FromName'];
		$JmailCOM->From=$_SERVER['84PHP_CONFIG']['Mail']['FromAddress'];
		
		$JmailCOM->AddRecipient($Address);
		$JmailCOM->Subject=$Title;
		$JmailCOM->Body=$Content;
		$JmailState=$JmailCOM->Send($_SERVER['84PHP_CONFIG']['Mail']['Server']);
		if($JmailState){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	
	//SocketError
	private function SsendError($Handle){
		fclose($Handle);
		return FALSE;
	}
	
	//Socket发送
	public function Ssend($UnionData=array()){
		$Address=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'address','地址');
		$Title=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'title','标题');
		$Content=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'content','内容');
		$Timeout=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'timeout','超时时间',FALSE,15);

		$Send=NULL;
		$Response='';
		$Handle=fsockopen($_SERVER['84PHP_CONFIG']['Mail']['Server'],$_SERVER['84PHP_CONFIG']['Mail']['Port'],$Errno,$ErrMsg,$Timeout);
		if(!$Handle&&$Errno===0){
			$this->SsendError($Handle);
		}
		stream_set_blocking($Handle,1);
		$Response.=fgets($Handle,512);
		$Send='EHLO '.'=?utf-8?B?'.base64_encode($_SERVER['84PHP_CONFIG']['Mail']['FromName']).'?='."\r\n";
		if(!fwrite($Handle,$Send)){
			return FALSE;
		}
		$Response.=fgets($Handle,512);
		while(TRUE){
			$Response.=fgets($Handle,512);
			if(substr($Response,3,1)!='-'||empty($Response)){
				break;
			}
		}
		$Send="AUTH LOGIN\r\n";
		if(!fwrite($Handle,$Send)){
			$this->SsendError($Handle);
		}
		$Response.=fgets($Handle,512);
		$Send=base64_encode($_SERVER['84PHP_CONFIG']['Mail']['UserName'])."\r\n";
		if(!fwrite($Handle,$Send)){
			$this->SsendError($Handle);
		}
		$Response.=fgets($Handle,512);
		$Send=base64_encode($_SERVER['84PHP_CONFIG']['Mail']['PassWord'])."\r\n";
		if(!fwrite($Handle,$Send)){
			$this->SsendError($Handle);
		}
		$Response.=fgets($Handle,512);
		$Send='MAIL FROM: <'.$_SERVER['84PHP_CONFIG']['Mail']['FromAddress'].">\r\n";

		if(!fwrite($Handle,$Send)){
			$this->SsendError($Handle);
		}
		$Response.=fgets($Handle,512);
		$Send='RCPT TO: <'.$Address."> \r\n";
		if(!fwrite($Handle,$Send)){
			$this->SsendError($Handle);
		}
		$Response.=fgets($Handle,512);
		$Send="DATA\r\n";
		if(!fwrite($Handle,$Send)){
			$this->SsendError($Handle);
		}
		$Response.=fgets($Handle,512);
		if(!empty($NewFromAddress)){
			$Head='From: =?utf-8?B?'.base64_encode($_SERVER['84PHP_CONFIG']['Mail']['FromName']).'?= <'.$NewFromAddress.">\r\n";
		}
		else{
			$Head='From: =?utf-8?B?'.base64_encode($_SERVER['84PHP_CONFIG']['Mail']['FromName']).'?= <'.$_SERVER['84PHP_CONFIG']['Mail']['FromAddress'].">\r\n";
		}
		$Head.='To: '.$Address."\r\n";
		$Head.='Subject: =?utf-8?B?'.base64_encode($Title)."?=\r\n";
		$Head.="Content-Type: text/html; charset=utf-8\r\nContent-Transfer-Encoding:8bit\r\n";
		$Content=$Head."\r\n".$Content;
		$Content.="\r\n.\r\n";
		if(!fwrite($Handle,$Content)){
			return FALSE;
		}
		$Send="QUIT\r\n";
		$Response.=fgets($Handle,512);
		
		if(strstr($Response,'535 Authentication')){
			return FALSE;
		}
		
		fclose($Handle);
		return TRUE;
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}