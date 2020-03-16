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

class Page{
	
	public function __construct(){
		if(!isset($_SERVER['84PHP_MODULE']['Mysql'])){
			require(RootPath.'/Core/Class/Module/Mysql.Class.php');
			$_SERVER['84PHP_MODULE']['Mysql']=new Mysql;
		}
	}

	//分页
	public function Base($UnionData=array()){
		$Table=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'table','表');
		$Field=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'desc','降序',FALSE,FALSE);
		$Index=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'sql','sql',FALSE,NULL);

		$Page=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'page','页码');		
		$Number=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'number','数量');		

		$FieldLimit=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field_limit','字段限制',FALSE,NULL);		
		
		$Result=array('result'=>array(),'info'=>array());
		$NowPage=intval($Page);
		$Number=intval($Number);
		$Start=0;
		$TotalNumber=$_SERVER['84PHP_MODULE']['Mysql']->Total(array(
			'table'=>$Table,
			'field'=>$Field,
			'value'=>$Value,
			'condition'=>$Condition,
			'order'=>$Order,
			'desc'=>$Desc,
			'index'=>$Index,
			'sql'=>$Sql
		));
		$TotalNumber=intval($TotalNumber);
		$TotalPage=intval(ceil($TotalNumber/$Number));
		if($Number>0){
			if($NowPage>=2&&$Number!=0){
				$Page=$NowPage-1;
				$Start=$Page*$Number;
				$End=$Number;
			}
			else{
				$End=$Number;
			}
			$Limit=array($Start,$Number);
		}
		else{
			$End=$TotalNumber;
			$Limit=array(0,-1);
		}
		if($TotalPage<$NowPage){
			$Result['info']=array(
				'now'=>$NowPage,
				'total'=>$TotalPage,
				'number'=>$TotalNumber,
				'start'=>$Start+1,
				'end'=>$End
			);
			return $Result;
		}
		if($Number==0){
			$TotalPage=1;
		}
		if($End>$TotalNumber){
			$End=$TotalNumber;
		};
		
		$Result['result']=$_SERVER['84PHP_MODULE']['Mysql']->SelectMore(array(
			'table'=>$Table,
			'field'=>$Field,
			'value'=>$Value,
			'condition'=>$Condition,
			'order'=>$Order,
			'desc'=>$Desc,
			'limit'=>$Limit,
			'index'=>$Index,
			'field_limit'=>$FieldLimit,
			'sql'=>$Sql
		));
		$Result['info']=array(
			'now'=>$NowPage,
			'total'=>$TotalPage,
			'number'=>$TotalNumber,
			'start'=>$Start+1,
			'end'=>$End
		);
		return $Result;
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}