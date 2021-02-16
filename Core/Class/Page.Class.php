<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

class Page{

	//分页
	public static function Base($UnionData=[]){
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
		
		$Result=['result'=>[],'info'=>[]];
		$NowPage=intval($Page);
		if($NowPage<1){
			$NowPage=1;
		}
		if($Number<1){
			$Number=0;
		}
		$Number=intval($Number);
		$Start=0;
		$TotalNumber=Mysql::Total([
			'table'=>$Table,
			'field'=>$Field,
			'value'=>$Value,
			'condition'=>$Condition,
			'order'=>$Order,
			'desc'=>$Desc,
			'index'=>$Index,
			'sql'=>$Sql
		]);
		$TotalNumber=intval($TotalNumber);
		$TotalPage=intval(ceil($TotalNumber/$Number));
		if($Number>0){
			$Start=($NowPage-1)*$Number;
			$End=$NowPage*$Number;
			$Limit=[$Start,$Number];
		}
		else{
			$End=$TotalNumber;
			$Limit=[0,-1];
		}
		if($TotalPage<$NowPage){
			$Result['info']=[
				'now'=>$NowPage,
				'total'=>$TotalPage,
				'number'=>$TotalNumber,
				'start'=>$Start+1,
				'end'=>$End
			];
			return $Result;
		}
		if($Number==0){
			$TotalPage=1;
		}
		if($End>$TotalNumber){
			$End=$TotalNumber;
		};
		
		$Result['result']=Mysql::SelectMore([
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
		]);
		$Result['info']=[
			'now'=>$NowPage,
			'total'=>$TotalPage,
			'number'=>$TotalNumber,
			'start'=>$Start+1,
			'end'=>$End
		];
		return $Result;
	}
	
	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}