<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

Class Send{

	//Post提交
	public static function Post($UnionData=[]){
		$Url=QuickParamet($UnionData,'url','地址');
		$Data=QuickParamet($UnionData,'data','数据',FALSE,[]);
		$Headers=QuickParamet($UnionData,'header','header',FALSE,[]);
		$Timeout=QuickParamet($UnionData,'timeout','超时时间',FALSE,15);

		$Response=NULL;
		if(is_array($Data)){
			$Data=http_build_query($Data);
		}
		$Params=['http'=>[
					'method'=>'POST',
					'content'=>$Data
					]
				];
		$Params['http']['timeout']=floatval($Timeout);
		if(!empty($Headers)){
			$Params['http']['header']=$Headers;
		}
		$Context=stream_context_create($Params);
		$Handle=@fopen($Url,'rb',FALSE,$Context);
		if(!$Handle){
			Wrong::Report(['detail'=>'Error#M.8.0','code'=>'M.8.0']);
		}
		$Response=@stream_get_contents($Handle);
		fclose($Handle);
		return $Response;
	}
	
	//Get提交
	public static function Get($UnionData=[]){
		$Url=QuickParamet($UnionData,'url','地址');
		$Data=QuickParamet($UnionData,'data','数据',FALSE,[]);
		$Headers=QuickParamet($UnionData,'header','header',FALSE,[]);
		$Timeout=QuickParamet($UnionData,'timeout','超时时间',FALSE,15);

		$Response=NULL;
		if(!empty($Data)){
			if(is_array($Data)){
				$Data='?'.http_build_query($Data);
			}
			else{
				$Data='?'.$Data;
			}
		}
		else{
			$Data='';
		}
		$Params=['http'=>['method'=>'GET']];
		$Params['http']['timeout']=floatval($Timeout);
		if(!empty($Headers)) {
			$Params['http']['header']=$Headers;
		}
		$Context=stream_context_create($Params);
		$Handle=@fopen($Url.$Data,'rb',FALSE,$Context);
		if(!$Handle){
			Wrong::Report(['detail'=>'Error#M.8.0','code'=>'M.8.0']);
		}
		$Response=@stream_get_contents($Handle);
		fclose($Handle);
		return $Response;
	}
	
	//Post含文件提交
	public static function Posts($UnionData=[]){
		$Url=QuickParamet($UnionData,'url','地址');
		$Data=QuickParamet($UnionData,'data','数据',FALSE,[]);
		$File=QuickParamet($UnionData,'file','文件',FALSE,[]);
		$Headers=QuickParamet($UnionData,'header','header',FALSE,[]);
		$Timeout=QuickParamet($UnionData,'timeout','超时时间',FALSE,15);

		if(!function_exists('curl_init')){
			Wrong::Report(['detail'=>'Error#M.8.1','code'=>'M.8.1']);
		}
		
		$Response=NULL;
		$SendData=[];
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
			$Val=urlencode($Val);
			$SendData[$Key]=$Val;
		}
		
		foreach($File as $Key=>$Val){
			if(file_exists(DiskPath($Val))){
				$SendData[$Key]=new \CURLFile(DiskPath($Val));
			}
		} 
		
		curl_setopt($Handle,CURLOPT_POSTFIELDS,$SendData);
		$Response=curl_exec($Handle);
		$CurlErrno=curl_errno($Handle);
		curl_close($Handle);
		if($Response===FALSE&&$CurlErrno>0){
			Wrong::Report(['detail'=>'Error#M.8.0'."\r\n\r\n @ ".$CurlErrno,'code'=>'M.8.0']);
		}
		return $Response;
	}
	
	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}