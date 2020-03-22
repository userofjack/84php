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

  框架版本号：4.0.1
*/

class Load{

	private function UpCall($FileError,$FileName,$FileSize,$FileTmpName,$SaveNameInfo,$Pathinfo,$SizeInfo,$TypeInfo,$IgnoreErrorInfo){
		if($FileError>0){
			switch ($FileError){
				case 1:
					$ModuleError='3';
					break;
				case 2:
					$ModuleError='4';  
					break;
				case 3:
					$ModuleError='5'; 
					break;
				case 4:
					$ModuleError='6'; 
					break;
				default:
					$ModuleError='7';
					break;
			}
			if(!$IgnoreErrorInfo){
				Wrong::Report(__FILE__,__LINE__,'Error#M.4.'.$ModuleError);
			}
			else{
				return NULL;
			}
		}
		$Exp=explode('.',$FileName);
		$Suffix=strtolower(end($Exp));
		
		$TypeInfo=explode(',',$TypeInfo);
		foreach($TypeInfo as $Key => $Val){
			$TypeInfo[$Key]=strtoupper($Val);
		}
		if(!in_array(strtoupper($Suffix),$TypeInfo)){
			if(!$IgnoreErrorInfo){
				Wrong::Report(__FILE__,__LINE__,'Error#M.4.8');
			}
			else{
				return NULL;
			}
		}
			
		if($FileSize>$SizeInfo){
			if(!$IgnoreErrorInfo){
				Wrong::Report(__FILE__,__LINE__,'Error#M.4.9');
			}
			else{
				return NULL;
			}
		}
		if(empty($SaveNameInfo)){
		$FileName=md5(date("YmdHis").mt_rand(1000000, 9999999).$_SERVER['REMOTE_ADDR']).'.'.$Suffix;
		}
		else{
			$FileName=$SaveNameInfo.'.'.$Suffix;
		}
		if(!file_exists($Pathinfo)){
			mkdir($Pathinfo,0777,TRUE);
		}
		if(!move_uploaded_file($FileTmpName,$Pathinfo.'/'.$FileName)){
			if(!$IgnoreErrorInfo){
				Wrong::Report(__FILE__,__LINE__,'Error#M.4.10');
			}
			else{
				return NULL;
			}
		}
		return str_replace(RootPath,'',$Pathinfo.'/'.$FileName);

	}
	
	//上传
	public function Up($UnionData=array()){
		$FieldCheck=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段');
		$Path=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'path','路径');
		$Type=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'type','类型');
		$SaveName=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'save_name','保存名称',FALSE,NULL);
		$Size=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'size','大小',FALSE,NULL);
		$Number=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'number','数量',FALSE,NULL);
		$IgnoreError=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'ignore_error','忽略错误',FALSE,FALSE);
		
		$Path=AddRootPath($Path);
		$Return=array();
		if(!empty($FieldCheck)&&is_array($FieldCheck)){
			foreach ($FieldCheck as $Val){
				$TempOp=explode(',',$Val);
				$TempField=str_replace('[]','',$TempOp[0]);
				if((!isset($_FILES[$TempField]))||(isset($TempOp[1])&&strtoupper($TempOp[1])=='TRUE'&&empty($_FILES[$TempField]['tmp_name']))){
					Wrong::Report(__FILE__,__LINE__,'Error#M.4.0 @ '.$TempField,FALSE,400);
				}
				if(is_array($Path)){
					if(empty($Path[$TempField])){
						Wrong::Report(__FILE__,__LINE__,'Error#M.4.1 @ '.$TempField,FALSE,400);
					}
					else{
						$TempPath=$Path[$TempField];
					}
				}
				else{
					$TempPath=$Path;
				}
				if(is_array($Type)){
					if(empty($Type[$TempField])){
						Wrong::Report(__FILE__,__LINE__,'Error#M.4.2 @ '.$TempField,FALSE,400);
					}
					else{
						$TempType=$Type[$TempField];
					}
				}
				else{
					$TempType=$Type;
				}
				
				if(empty($SaveName[$TempField])){
					$TempSaveName=NULL;
				}
				else{
					$TempSaveName=$SaveName[$TempField];
				}

				if(empty($Size[$TempField])||intval($Size[$TempField])<0){
					if(is_int($Size)){
						$TempSize=$Size*1024;
					}
					else{
						$TempSize=10485760;
					}
				}
				else{
					$TempSize=intval($Size[$TempField])*1024;
				}
								
				if(empty($_FILES[$TempField])){
					$Return[$TempField]=array();
				}
				else{
					if(is_string($_FILES[$TempField]['tmp_name'])){
						$Return[$TempField][0]=$this->UpCall($_FILES[$TempField]['error'],
															 $_FILES[$TempField]['name'],
															 $_FILES[$TempField]['size'],
															 $_FILES[$TempField]['tmp_name'],
															 $TempSaveName,
															 $TempPath,
															 $TempSize,
															 $TempType,
															 $IgnoreError
															);
					}
					else if(is_array($_FILES[$TempField]['tmp_name'])){
						if(empty($Number[$TempField])||intval($Number[$TempField])<0){
							if(is_int($Number)){
								$TempNumber=$Number;
							}
							else{
								$TempNumber=1;
							}
						}
						else{
							$TempNumber=intval($Number[$TempField]);
						}
						if(count($_FILES[$TempField]['tmp_name'])<$TempNumber){
							$TempNumber=count($_FILES[$TempField]['tmp_name']);
						}
						for($i=0;$i<$TempNumber;$i++){
							$Return[$TempField][$i]=$this->UpCall($_FILES[$TempField]['error'][$i],
																 $_FILES[$TempField]['name'][$i],
																 $_FILES[$TempField]['size'][$i],
																 $_FILES[$TempField]['tmp_name'][$i],
																 NULL,
																 $TempPath,
																 $TempSize,
																 $TempType,
																 $IgnoreError
																);
						}
					}
				}
			}
		}
		return $Return;
	}
	
	//下载
	public function Down($UnionData=array()){
		$Url=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'url','地址');
		$Path=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'path','路径');
		$Timeout=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'timeout','超时时间',FALSE,86400);

		$Path=AddRootPath($Path);
		
		set_time_limit($Timeout);
		if(!file_exists($Path)){
			mkdir($Path,0777,TRUE);
		}
		$NewName=$Path.'/'.Runtime.mt_rand(111,999).'-'.basename($Url);
		$Handle=@fopen($Url,'rb');
		if($Handle){
			$NewHandle=@fopen($NewName,"wb");
			if(!$NewHandle){
				Wrong::Report(__FILE__,__LINE__,'Error#M.4.11');
			}
			if($NewHandle){
				while(!feof($Handle)){
					if(!fwrite($NewHandle,@fread($Handle,1024*8),1024*8)){
						Wrong::Report(__FILE__,__LINE__,'Error#M.4.12');
					};
				}
				fclose($NewHandle);
			}
			fclose($Handle);
		}
		else{
			Wrong::Report(__FILE__,__LINE__,'Error#M.4.13');
		}
		return $NewName;
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}