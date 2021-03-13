<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

class Page{

	//分页
	public static function Base($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Field=QuickParamet($UnionData,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);

		$Page=QuickParamet($UnionData,'page','页码');		
		$Number=QuickParamet($UnionData,'number','数量');		

		$FieldLimit=QuickParamet($UnionData,'field_limit','字段限制',FALSE,NULL);		
		
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