<?php

namespace core;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.1.0
*/

class Page
{

    //分页
    public static function get($UnionData = []): array
    {
        $Page = Common::quickParameter($UnionData, 'page', '页码');
        $Number = Common::quickParameter($UnionData, 'number', '数量');

        $Result = ['result' => []];
        $NowPage = intval($Page);
        if ($NowPage < 1) {
            $NowPage = 1;
        }
        if ($Number < 1) {
            $Number = 0;
        }
        $Number = intval($Number);
        $Start = 0;
        $TotalNumber = Db::total($UnionData);
        $TotalNumber = intval($TotalNumber);
        $TotalPage = intval(ceil($TotalNumber / $Number));
        if ($Number > 0) {
            $Start = ($NowPage - 1) * $Number;
            $End = $NowPage * $Number;
            $Limit = [$Start, $Number];
        } else {
            $End = $TotalNumber;
            $Limit = [0, -1];
        }
        if ($TotalPage < $NowPage) {
            $Result['info'] = [
                'now' => $NowPage,
                'total' => $TotalPage,
                'number' => $TotalNumber,
                'start' => $Start + 1,
                'end' => $End
            ];
            return $Result;
        }
        if ($Number == 0) {
            $TotalPage = 1;
        }
        if ($End > $TotalNumber) {
            $End = $TotalNumber;
        }

        $UnionData['limit'] = $Limit;

        $Result['result'] = Db::selectMore($UnionData);
        $Result['info'] = [
            'now' => $NowPage,
            'total' => $TotalPage,
            'number' => $TotalNumber,
            'start' => $Start + 1,
            'end' => $End
        ];
        return $Result;
    }

    //调用方法不存在
    public static function __callStatic($Method, $Parameters)
    {
        Common::unknownStaticMethod(__CLASS__, $Method);
    }
}