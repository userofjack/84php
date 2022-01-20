<?php
namespace core;

use core\Common;
use core\Db;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/

class Page
{

    //分页
    public static function base($UnionData=[])
    {
        $Table=Common::quickParamet($UnionData,'table','表');
        $Field=Common::quickParamet($UnionData,'field','字段',FALSE,[]);
        $Value=Common::quickParamet($UnionData,'value','值',FALSE,[]);
        $Condition=Common::quickParamet($UnionData,'condition','条件',FALSE,'=');
        $Order=Common::quickParamet($UnionData,'order','顺序',FALSE,NULL);
        $Desc=Common::quickParamet($UnionData,'desc','降序',FALSE,FALSE);
        $Index=Common::quickParamet($UnionData,'index','索引',FALSE,NULL);        
        $Sql=Common::quickParamet($UnionData,'sql','sql',FALSE,NULL);

        $Page=Common::quickParamet($UnionData,'page','页码');        
        $Number=Common::quickParamet($UnionData,'number','数量');        

        $FieldLimit=Common::quickParamet($UnionData,'field_limit','字段限制',FALSE,NULL);        
        
        $Result=['result'=>[],'info'=>[]];
        $NowPage=intval($Page);
        if ($NowPage<1) {
            $NowPage=1;
        }
        if ($Number<1) {
            $Number=0;
        }
        $Number=intval($Number);
        $Start=0;
        $TotalNumber=Db::total([
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
        if ($Number>0) {
            $Start=($NowPage-1)*$Number;
            $End=$NowPage*$Number;
            $Limit=[$Start,$Number];
        }
        else {
            $End=$TotalNumber;
            $Limit=[0,-1];
        }
        if ($TotalPage<$NowPage) {
            $Result['info']=[
                'now'=>$NowPage,
                'total'=>$TotalPage,
                'number'=>$TotalNumber,
                'start'=>$Start+1,
                'end'=>$End
            ];
            return $Result;
        }
        if ($Number==0) {
            $TotalPage=1;
        }
        if ($End>$TotalNumber) {
            $End=$TotalNumber;
        };
        
        $Result['result']=Db::selectMore([
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
    public static function __callStatic($Method,$Parameters)
    {
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}