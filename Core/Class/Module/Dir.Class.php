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

class Dir{
	//目录文件属性
	public function State($UnionData=array()){
		$Path=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'path','路径');
		
		if(!is_array($Path)){
			$PathArray=array($Path);
		}
		else{
			$PathArray=$Path;
		}

		clearstatcache();
		$Return=array();
		foreach ($PathArray as $Key => $Val){
			$TempArray=array();

			if(file_exists(AddRootPath($Val))){
				if (is_readable(AddRootPath($Val))){
					$TempArray['R']='Y';
				}
				else{
					$TempArray['R']='N';
				}
				
				if (is_writable(AddRootPath($Val))){
					$TempArray['W']='Y';
				}
				else{
					$TempArray['W']='N';
				}
				
				if(is_dir(AddRootPath($Val))){
					if (is_executable(AddRootPath($Val))){
						$TempArray['Ex']='Y';
					}
					else{
						$TempArray['Ex']='N';
					}
				}
			}
			else{
				$TempArray=array();
			}
			$Return[$Val]=$TempArray;
		}
		return $Return;
	}
	
	//目录大小调用
	private function SizeCall($Path){
		$DirSize=0;
		if(file_exists($Path)&&$DirHandle=@opendir($Path)){
			while($FileName=readdir($DirHandle)){
				if($FileName!="."&&$FileName!=".."){
					$SubFile=$Path."/".$FileName;
					if(is_dir($SubFile))
						$DirSize+=$this->SizeCall($SubFile);
					if(is_file($SubFile))
						$DirSize+=filesize($SubFile);
				}
			}
			closedir($DirHandle);
			return $DirSize;
		}
		else{
			Wrong::Report(__FILE__,__LINE__,'Error#M.0.0');
		}
	}
	
	//目录大小
	public function Size($UnionData=array()){
		$Path=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'path','路径');
		$Unit=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'unit','单位',FALSE,NULL);

		$DirSize=$this->SizeCall(AddRootPath($Path));
		
		if($Unit=='KB'){
			$DirSize=round($DirSize/pow(1024,1),2);
			return $DirSize;
		}
		elseif($Unit=='MB'){
			$DirSize=round($DirSize/pow(1024,2),2);
			return $DirSize;
		}
		elseif($Unit=='GB'){
			$DirSize=round($DirSize/pow(1024,3),2);
			return $DirSize;
		}
		else{
			return $DirSize;
		}
	}
	
	//删除目录调用
	private function DeleteCall($Dir){
		if(file_exists($Dir)){
			if($DirHandle=@opendir($Dir)){
				while($FileName=readdir($DirHandle)){
					if($FileName!="."&&$FileName!=".."){
						$SubFile=$Dir."/".$FileName;
						if(is_dir($SubFile)){
							$this->DelCall($SubFile);
						}
						if(is_file($SubFile)){
							@unlink($SubFile);
						}
					}
				}
				closedir($DirHandle);
				rmdir($Dir);
			}
			else{
				Wrong::Report(__FILE__,__LINE__,'Error#M.0.1');
			}
		}
	}
	
	//删除目录
	public function Delete($UnionData=array()){
		$Path=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'path','路径');

		if(!is_array($Path)){
			$this->DeleteCall(AddRootPath($Path));
		}
		else{
			foreach($Path as $Val){
				$this->DeleteCall(AddRootPath($Path));
			}
		}
	}
	
	//复制目录调用
	private function CopyCall($From,$To){
		if(!file_exists($From)){
			Wrong::Report(__FILE__,__LINE__,'Error#M.0.0');
		}
		if(is_file($To)){
			exit;
		}
		if(!file_exists($To)){
			mkdir($To,0777,TRUE);
		}
		if($DirHandle=@opendir($From)){
			while($FileName=readdir($DirHandle)){
				if($FileName!="." && $FileName!=".."){
					$FromPath=$From."/".$FileName;
					$ToPath=$To."/".$FileName;
					if(is_dir($FromPath)){
						$this->CopyCall($FromPath,$ToPath);
					}
					if(is_file($FromPath)){
						copy($FromPath,$ToPath);
					}
				}
			}
			closedir($DirHandle);
		}
		else{
			Wrong::Report(__FILE__,__LINE__,'Error#M.0.1');
		}
	}
	
	//复制目录
	public function Copy($UnionData=array()){
		$From=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'from','源路径');
		$To=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'to','目标路径');

		$this->CopyCall(AddRootPath($Path), AddRootPath($Path));
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}