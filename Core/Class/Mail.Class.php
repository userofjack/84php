<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.1.0
*/

require(RootPath.'/Config/Mail.php');

class Mail{

	//Jmail发送
	public static function Jsend($UnionData=[]){
		$Address=QuickParamet($UnionData,'address','地址');
		$Title=QuickParamet($UnionData,'title','标题');
		$Content=QuickParamet($UnionData,'content','内容');
		$Timeout=QuickParamet($UnionData,'timeout','超时时间',FALSE,15);

		set_time_limit($Timeout);
		$JmailCOM=new COM("Jmail.Message");
		if(!$JmailCOM){
			Wrong::Report(['detail'=>'Error#M.5.0','code'=>'M.5.0']);
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
	private static function SsendError($Handle){
		fclose($Handle);
		return FALSE;
	}
	
	//Socket发送
	public static function Ssend($UnionData=[]){
		$Address=QuickParamet($UnionData,'address','地址');
		$Title=QuickParamet($UnionData,'title','标题');
		$Content=QuickParamet($UnionData,'content','内容');
		$Timeout=QuickParamet($UnionData,'timeout','超时时间',FALSE,15);

		$Send=NULL;
		$Response='';
		$Handle=fsockopen($_SERVER['84PHP_CONFIG']['Mail']['Server'],$_SERVER['84PHP_CONFIG']['Mail']['Port'],$Errno,$ErrMsg,$Timeout);
		if(!$Handle&&$Errno===0){
			self::SsendError($Handle);
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
			self::SsendError($Handle);
		}
		$Response.=fgets($Handle,512);
		$Send=base64_encode($_SERVER['84PHP_CONFIG']['Mail']['UserName'])."\r\n";
		if(!fwrite($Handle,$Send)){
			self::SsendError($Handle);
		}
		$Response.=fgets($Handle,512);
		$Send=base64_encode($_SERVER['84PHP_CONFIG']['Mail']['PassWord'])."\r\n";
		if(!fwrite($Handle,$Send)){
			self::SsendError($Handle);
		}
		$Response.=fgets($Handle,512);
		$Send='MAIL FROM: <'.$_SERVER['84PHP_CONFIG']['Mail']['FromAddress'].">\r\n";

		if(!fwrite($Handle,$Send)){
			self::SsendError($Handle);
		}
		$Response.=fgets($Handle,512);
		$Send='RCPT TO: <'.$Address."> \r\n";
		if(!fwrite($Handle,$Send)){
			self::SsendError($Handle);
		}
		$Response.=fgets($Handle,512);
		$Send="DATA\r\n";
		if(!fwrite($Handle,$Send)){
			self::SsendError($Handle);
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
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}