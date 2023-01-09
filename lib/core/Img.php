<?php

namespace core;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.1.0
*/

class Img
{

    //颜色转换
    private static function hexRGB($HexColor): array
    {
        $Hex = hexdec(str_replace('#', '', $HexColor));
        return ["red" => 0xFF & ($Hex >> 0x10), "green" => 0xFF & ($Hex >> 0x8), "blue" => 0xFF & $Hex];
    }

    //图片支持检测
    private static function mimeCheck($MIME)
    {
        $FileType = str_replace('image/', '', $MIME);
        $Support = array_key_exists(
            $FileType,
            [
                'bmp' => '',
                'gd2' => '',
                'gd' => '',
                'gif' => '',
                'jpeg' => '',
                'png' => '',
                'vnd.wap.wbmp' => '',
                'webp' => '',
                'xbm' => ''
            ]
        );

        if ($FileType == 'vnd.wap.wbmp') {
            $FileType = 'wbmp';
        }

        $FunExists = function_exists('imagecreatefrom' . $FileType);

        if (!$Support || !$FunExists) {
            Api::wrong(['level' => 'F', 'detail' => 'Error#M.2.0', 'code' => 'M.2.0']);
        }
    }

    //打开图片
    private static function getImage($From, $DataType)
    {
        if ($DataType == 'path') {
            if (!file_exists($From)) {
                Api::wrong(['level' => 'F', 'detail' => 'Error#M.2.1', 'code' => 'M.2.1']);
            }
            $Exp = explode('.', $From);
            $MIME = end($Exp);
            if (strtolower($MIME) == 'wbmp') {
                $MIME = 'vnd.wap.wbmp';
            }
            if (strtolower($MIME) == 'jpg') {
                $MIME = 'jpeg';
            }
            self::mimeCheck(strtolower($MIME));
            if ($MIME == 'vnd.wap.wbmp') {
                $MIME = 'wbmp';
            }

            $ImgInfo = @getimagesize($From);

            $ImgData = call_user_func('imagecreatefrom' . $MIME, $From);
        } else {
            $ImgInfo = @getimagesizefromstring($From);
            $ImgData = call_user_func('imagecreatefromstring', $From);
        }

        if (!$ImgInfo || $ImgData === false) {
            Api::wrong(['level' => 'F', 'detail' => 'Error#M.2.2', 'code' => 'M.2.2']);
        }

        $Return = $ImgInfo;
        $Return['Data'] = $ImgData;

        return $Return;
    }

    //输出图片
    private static function outputImage($ImgData, $To, $Quality, $MIME)
    {
        if (empty($To)) {
            $To = null;
            header('Content-Type: image/' . $MIME);
        } else {
            if (!is_dir(dirname($To))) {
                mkdir(dirname($To), 0777, true);
            }
            $Exp = explode('.', $To);
            $MIME = end($Exp);
            if (strtolower($MIME) == 'wbmp') {
                $MIME = 'vnd.wap.wbmp';
            }
            if (strtolower($MIME) == 'jpg') {
                $MIME = 'jpeg';
            }
        }
        self::mimeCheck(strtolower($MIME));
        if ($MIME == 'png') {
            $Quality = intval($Quality / 10);
        }

        $OutPut = false;

        if (array_key_exists($MIME, ['jpeg' => '', 'png' => '', 'webp' => ''])) {
            $OutPut = call_user_func('image' . $MIME, $ImgData, $To, $Quality);
        }

        if (array_key_exists(
            $MIME,
            ['bmp' => '', 'gd2' => '', 'gd' => '', 'gif' => '', 'vnd.wap.wbmp' => '', 'xbm' => '']
        )) {
            if ($MIME == 'vnd.wap.wbmp') {
                $MIME = 'wbmp';
            }
            $OutPut = call_user_func('image' . $MIME, $ImgData, $To);
        }

        if (!$OutPut) {
            Api::wrong(['level' => 'F', 'detail' => 'Error#M.2.3', 'code' => 'M.2.3']);
        }
    }

    //伸缩和水印
    public static function change($UnionData = [])
    {
        $From = Common::quickParameter($UnionData, 'image', '源图片');
        $DataType = strtolower(Common::quickParameter($UnionData, 'data_type', '资源类型', false, 'path'));
        $To = Common::quickParameter($UnionData, 'to', '目标路径', false);
        $Width = Common::quickParameter($UnionData, 'width', '宽度', false);
        $Height = Common::quickParameter($UnionData, 'height', '高度', false);
        $Scale = Common::quickParameter($UnionData, 'scale', '缩放', false, 1.0);
        $Word = Common::quickParameter($UnionData, 'word', '文字', false);
        $WordSize = Common::quickParameter($UnionData, 'word_size', '文字大小', false);
        $WordColor = Common::quickParameter($UnionData, 'word_color', '文字颜色', false, '#333333');
        $WordMarginX = Common::quickParameter($UnionData, 'word_margin_x', '文字左边距', false, 0);
        $WordMarginY = Common::quickParameter($UnionData, 'word_margin_y', '文字顶边距', false, 0);
        $Quality = Common::quickParameter($UnionData, 'quality', '质量', false, 75);
        $MIME = strtolower(Common::quickParameter($UnionData, 'mime', '图片格式', false, 'jpeg'));

        if ($DataType == 'path') {
            $From = Common::diskPath($From);
        }

        if (!empty($To)) {
            $To = Common::diskPath($To);
        }

        $WordColorArray = ["red" => 80, "green" => 80, "blue" => 80];
        $ImgInfo = self::getImage($From, $DataType);

        if (empty($Width) && empty($Height)) {
            $NewWidth = round($ImgInfo[0] * $Scale);
            $NewHeight = round($ImgInfo[1] * $Scale);
        } else {
            if (empty($Width)) {
                $NewWidth = round($ImgInfo[0] * ($Height / $ImgInfo[1]));
            } else {
                $NewWidth = $Width;
            }
            if (empty($Height)) {
                $NewHeight = round($ImgInfo[1] * ($Width / $ImgInfo[0]));
            } else {
                $NewHeight = $Height;
            }
        }
        $NewImg = imagecreatetruecolor($NewWidth, $NewHeight);
        if (!$NewImg) {
            Api::wrong(['level' => 'F', 'detail' => 'Error#M.2.4', 'code' => 'M.2.4']);
        }
        imagecopyresampled($NewImg, $ImgInfo['Data'], 0, 0, 0, 0, $NewWidth, $NewHeight, $ImgInfo[0], $ImgInfo[1]);
        if (!empty($Word)) {
            if (empty($WordSize)) {
                $WordSize = $NewHeight * 0.12;
            }
            if ($WordColor != null) {
                $WordColorArray = self::hexRGB($WordColor);
            }
            $TextColor = imagecolorallocate(
                $NewImg,
                $WordColorArray['red'],
                $WordColorArray['green'],
                $WordColorArray['blue']
            );
            if (!imagettftext(
                $NewImg,
                $WordSize,
                0,
                $WordMarginX,
                $WordMarginY,
                $TextColor,
                Common::diskPath($_SERVER['84PHP']['Config']['Img']['fontFile']),
                $Word
            )) {
                Api::wrong(['level' => 'F', 'detail' => 'Error#M.2.5', 'code' => 'M.2.5']);
            }
        }

        self::outputImage($NewImg, $To, $Quality, $MIME);

        imagedestroy($ImgInfo['Data']);
        imagedestroy($NewImg);
    }

    //合并图片
    public static function merge($UnionData = [])
    {
        $Background = Common::quickParameter($UnionData, 'background', '背景');
        $Foreground = Common::quickParameter($UnionData, 'foreground', '前景');
        $DataType = strtolower(Common::quickParameter($UnionData, 'data_type', '资源类型', false, 'path'));
        $To = Common::quickParameter($UnionData, 'to', '目标路径', false);
        $ImageX = Common::quickParameter($UnionData, 'image_x', '起始X', false, 0);
        $ImageY = Common::quickParameter($UnionData, 'image_y', '起始Y', false, 0);
        $Scale = Common::quickParameter($UnionData, 'scale', '缩放', false, 1.0);
        $Quality = Common::quickParameter($UnionData, 'quality', '质量', false, 75);
        $MIME = strtolower(Common::quickParameter($UnionData, 'mime', '图片类型', false, 'jpeg'));

        if (!empty($To)) {
            $To = Common::diskPath($To);
        }

        if ($DataType != 'path') {
            $DataType = 'string';
        } else {
            $Background = Common::diskPath($Background);
            $Foreground = Common::diskPath($Foreground);
        }


        $BgImageInfo = self::getImage($Background, $DataType);
        $FgImageInfo = self::getImage($Foreground, $DataType);

        imagecopyresampled(
            $BgImageInfo['Data'],
            $FgImageInfo['Data'],
            $ImageX,
            $ImageY,
            0,
            0,
            intval($FgImageInfo[0] * $Scale),
            intval($FgImageInfo[1] * $Scale),
            $FgImageInfo[0],
            $FgImageInfo[1]
        );

        self::outputImage($BgImageInfo['Data'], $To, $Quality, $MIME);

        imagedestroy($BgImageInfo['Data']);
        imagedestroy($FgImageInfo['Data']);
    }

    //调用方法不存在
    public static function __callStatic($Method, $Parameters)
    {
        Common::unknownStaticMethod(__CLASS__, $Method);
    }
}