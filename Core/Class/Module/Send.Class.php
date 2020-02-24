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

Class Send{

	public function __construct(){
		if(!empty($_SESSION['ModuleSetting'][__CLASS__])&&is_array($_SESSION['ModuleSetting'][__CLASS__])){
			foreach($_SESSION['ModuleSetting'][__CLASS__] as $ModuleSettingKey => $ModuleSettingVal){
				$GLOBALS['ModuleConfig_Send'][$ModuleSettingKey]=$ModuleSettingVal;
			}
		}
	}

	//Post提交
	public function Post($UnionData){
		$Url=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'url','地址');
		$Data=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'data','数据',FALSE,array());
		$Headers=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'header','header',FALSE,array());
		$BuildQuery=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'encode','编码',FALSE,TRUE);
		$Timeout=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'timeout','超时时间',FALSE,15);

		$Response=NULL;
		$SendData='';
		if (is_array($Data)&&$BuildQuery){
			$SendData=http_build_query($Data);
		}
		$Params=array('http'=>array(
					'method'=>'POST',
					'content'=>$SendData
		));
		$Params['http']['timeout']=floatval($Timeout);
		if(!empty($Headers)){
			$Params['http']['header']=$Headers;
		}
		$Context=stream_context_create($Params);
		$Handle=@fopen($Url,'rb',FALSE,$Context);
		if(!$Handle){
			Wrong::Report(__FILE__,__LINE__,'Error#M.8.0',TRUE);
		}
		$Response=@stream_get_contents($Handle);
		fclose($Handle);
		return $Response;
	}
	
	//Get提交
	public function Get($UnionData){
		$Url=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'url','地址');
		$Data=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'data','数据',FALSE,array());
		$Headers=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'header','header',FALSE,array());
		$Timeout=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'timeout','超时时间',FALSE,15);

		$Response=NULL;
		$SendData='';
		if(!empty($Data)){
			$SendData='?'.http_build_query($Data);
		}
		$Params=array('http'=>array('method'=>'GET'));
		$Params['http']['timeout']=floatval($Timeout);
		if(!empty($Headers)) {
			$Params['http']['header']=$Headers;
		}
		$Context=stream_context_create($Params);
		$Handle=@fopen($Url.$SendData,'rb',FALSE,$Context);
		if(!$Handle){
			Wrong::Report(__FILE__,__LINE__,'Error#M.8.0',TRUE);
		}
		$Response=@stream_get_contents($Handle);
		fclose($Handle);
		return $Response;
	}
	
	//Post含文件提交
	public function Posts($UnionData){
		$Url=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'url','地址');
		$Data=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'data','数据',FALSE,array());
		$File=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'file','文件',FALSE,array());
		$Headers=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'header','header',FALSE,array());
		$BuildQuery=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'encode','编码',FALSE,TRUE);
		$Timeout=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'timeout','超时时间',FALSE,15);

		if(!function_exists('curl_init')){
			Wrong::Report(__FILE__,__LINE__,'Error#M.8.1',TRUE);
		}
		
		$Response=NULL;
		$SendData=array();
		$Handle=curl_init();
		
		curl_setopt($Handle,CURLOPT_URL,$Url);
		curl_setopt($Handle,CURLOPT_CONNECTTIMEOUT,$Timeout);
		curl_setopt($Handle,CURLOPT_HEADER,FALSE);
		curl_setopt($Handle,CURLOPT_HTTPHEADER,$Headers);
		
		curl_setopt($Handle,CURLOPT_AUTOREFERER,TRUE);
		curl_setopt($Handle,CURLOPT_FOLLOWLOCATION,TRUE);
		curl_setopt($Handle,CURLOPT_MAXREDIRS,20);
		curl_setopt($Handle,CURLOPT_POST,TRUE);
		curl_setopt($Handle,CURLOPT_RETURNTRANSFER,TRUE);
		
		foreach($Data as $Key=>$Val){
			if($BuildQuery){
				$Val=urlencode($Val);
			}
			$SendData[$Key]=$Val;
		}
		
		foreach($File as $Key=>$Val){
			if(file_exists(AddRootPath($Val))){
				$SendData[$Key]=new \CURLFile(AddRootPath($Val));
			}
		} 
		
		curl_setopt($Handle,CURLOPT_POSTFIELDS,$SendData);
		$Response=curl_exec($Handle);
		$CurlErrno=curl_errno($Handle);
		curl_close($Handle);
		if($Response===FALSE&&$CurlErrno>0){
			Wrong::Report(__FILE__,__LINE__,'Error#M.8.0 @ '.$CurlErrno,TRUE);
		}
		return $Response;
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}