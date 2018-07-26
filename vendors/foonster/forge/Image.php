<?php
/**
 * @author Nicolas Colbert <nicolas@foonster.com>
 * @copyright 2005 Foonster Technology
 */
/**
 * 
 */
class Image
{
    /**
     * this function uses the gdimage library to reduce an image and save it to a path 
     * or replace the existing file. 
     * 
     * @param  string  $cInput   [file path for the source image]
     * @param  string  $cOutput  [the output path]
     * @param  integer $nH       [the image height]
     * @param  integer $nW       [the image width]
     * @param  string  $xType    [alternate ways to crop image]
     * @param  integer $nQuality [image quality]
     * 
     */
    public function reduceImage($cInput, $cOutput, $nH = 1600, $nW = 2560, $xType = 'normal', $nQuality = 100)
    {
        if (function_exists('imagecreatefromgif')) {
            $src_img = '';
            $nH == $nW ? $xType = 'square' : false;
            $cOutput == null ? $cOutput = $cInput : false;
            $cType = strtolower(substr(stripslashes($cInput), strrpos(stripslashes($cInput), '.')));

            if ($cType == '.gif' || $cType == 'image/gif') {
                $src_img = imagecreatefromgif($cInput); /* Attempt to open */
                $cType = 'image/gif';
            } elseif ($cType == '.png' || $cType == 'image/png' || $cType == 'image/x-png') {
                $src_img = imagecreatefrompng($cInput); /* Attempt to open */
                $cType = 'image/x-png';
            } elseif ($cType == '.bmp' || $cType == 'image/bmp') {
                $src_img = imagecreatefrombmp($cInput); /* Attempt to open */
                $cType = 'image/bmp';
            } elseif ($cType == '.jpg' || $cType == '.jpeg' || $cType == 'image/jpg' || $cType == 'image/jpeg' || $cType == 'image/pjpeg') {
                $src_img = imagecreatefromjpeg($cInput); /* Attempt to open */
                $cType = 'image/jpeg';
            } else {
            }

            if (!$src_img) {
                $src_img = imagecreatefromgif(_TYFOON . '/images/widget.gif'); /* Attempt to open */
                $cType = 'image/gif';
            } else {

                $tmp_img;
                list($width, $height) = getimagesize($cInput);
                if ($xType == 'square' && $width != $height) {
                    $biggestSide = '';
                    $cropPercent = .5;
                    $cropWidth   = 0;
                    $cropHeight  = 0;
                    $c1 = array();
                    if ($width > $height) {
                        $biggestSide = $width;
                        $cropWidth   = round($biggestSide*$cropPercent);
                        $cropHeight  = round($biggestSide*$cropPercent);
                        $c1 = array("x"=>($width-$cropWidth)/2, "y"=>($height-$cropHeight)/2);
                    } else {
                        $biggestSide = $height;
                        $cropWidth   = round($biggestSide*$cropPercent);
                        $cropHeight  = round($biggestSide*$cropPercent);
                        $c1 = array("x"=>($width-$cropWidth)/2, "y"=>($height-$cropHeight)/7);
                    }
                    $thumbSize = $nH;

                    if ($cType == 'image/gif') {
                        $tmp_img = imagecreate($thumbSize, $thumbSize);
                        imagecolortransparent($tmp_img, imagecolorallocate($tmp_img, 0, 0, 0));
                        imagecopyresized($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    } elseif ($cType == 'image/x-png') {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    } elseif ($cType == 'image/bmp') {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);

                    } elseif ($cType == 'image/jpeg') {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    } else {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    }

                    imagedestroy($src_img);
                    $src_img = $tmp_img;
                } else {
                    $ow = imagesx($src_img);
                    $oh = imagesy($src_img);
                    if ($nH == 0 && $nW == 0) {
                        $nH = $oh;
                        $nW = $ow;
                    }
                    if ($nH == 0) {
                        $nH = $nW;
                    }
                    if ($nW == 0) {
                        $nW = $nH;
                    }
                    if ($nH > $oh && $nW > $ow) {
                        $width  = $ow;
                        $height = $oh;
                    } else {

                        if ($nW && ($ow < $oh)) {
                            $nW = ($nH / $oh) * $ow;
                        } else {
                            $nH = ($nW / $ow) * $oh;
                        }
                        $width  = $nW;
                        $height = $nH;
                    }
                    if ($cType == 'image/gif') {
                        $tmp_img = imagecreate($width, $height);
                        imagecolortransparent($tmp_img, imagecolorallocate($tmp_img, 0, 0, 0));
                        imagecopyresized($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } elseif ($cType == 'image/x-png') {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } elseif ($cType == 'image/bmp') {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } elseif ($cType == 'image/jpeg') {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } else {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    }
                    imagedestroy($src_img);
                    $src_img = $tmp_img;
                }
            }
            // set the output
            if ($cType == 'image/gif') {
                imageGIF($src_img, $cOutput);
            } elseif ($cType == 'image/x-png') {
                imagePNG($src_img, $cOutput);
            } elseif ($cType == 'image/bmp') {
                imageJPEG($src_img, $cOutput, $nQuality);
            } elseif ($cType == 'image/jpeg') {
                imageJPEG($src_img, $cOutput, $nQuality);
            } else {
                imageJPEG($src_img, $cOutput, $nQuality);
            }
        }
    }
    /**
     * this function uses the gdimage library to resize an image and save it to a path 
     * or replace the existing file.
     * 
     * @param  string  $cInput   [file path for the source image]
     * @param  string  $cOutput  [the output path]
     * @param  integer $nH       [the image height]
     * @param  integer $nW       [the image width]
     * @param  string  $xType    [alternate ways to crop image]
     * @param  integer $nQuality [image quality]
     * 
     */
    public function resizeImage($cInput, $cOutput, $nH = 1600, $nW = 2560, $xType = 'normal', $nQuality = 100)
    {
        if (function_exists('imagecreatefromgif')) {
            $src_img = '';
            $nH == $nW ? $xType = 'square' : false;
            $cOutput == null ? $cOutput = $cInput : false;
            $cType = strtolower(substr(stripslashes($cInput), strrpos(stripslashes($cInput), '.')));

            if ($cType == '.gif' || $cType == 'image/gif') {
                $src_img = imagecreatefromgif($cInput); /* Attempt to open */
                $cType = 'image/gif';
            } elseif ($cType == '.png' || $cType == 'image/png' || $cType == 'image/x-png') {
                $src_img = imagecreatefrompng($cInput); /* Attempt to open */
                $cType = 'image/x-png';
            } elseif ($cType == '.bmp' || $cType == 'image/bmp') {
                $src_img = imagecreatefrombmp($cInput); /* Attempt to open */
                $cType = 'image/bmp';
            } elseif ($cType == '.jpg' || $cType == '.jpeg' || $cType == 'image/jpg' || $cType == 'image/jpeg' || $cType == 'image/pjpeg') {
                $src_img = imagecreatefromjpeg($cInput); /* Attempt to open */
                $cType = 'image/jpeg';
            } else {
            }

            if (!$src_img) {
                $src_img = imagecreatefromgif(_TYFOON . '/images/widget.gif'); /* Attempt to open */
                $cType = 'image/gif';
            } else {

                $tmp_img;
                list($width, $height) = getimagesize($cInput);
                if ($xType == 'square' && $width != $height) {
                    $biggestSide = '';
                    $cropPercent = .5;
                    $cropWidth   = 0;
                    $cropHeight  = 0;
                    $c1 = array();
                    if ($width > $height) {
                        $biggestSide = $width;
                        $cropWidth   = round($biggestSide*$cropPercent);
                        $cropHeight  = round($biggestSide*$cropPercent);
                        $c1 = array("x"=>($width-$cropWidth)/2, "y"=>($height-$cropHeight)/2);
                    } else {
                        $biggestSide = $height;
                        $cropWidth   = round($biggestSide*$cropPercent);
                        $cropHeight  = round($biggestSide*$cropPercent);
                        $c1 = array("x"=>($width-$cropWidth)/2, "y"=>($height-$cropHeight)/7);
                    }
                    $thumbSize = $nH;

                    if ($cType == 'image/gif') {
                        $tmp_img = imagecreate($thumbSize, $thumbSize);
                        imagecolortransparent($tmp_img, imagecolorallocate($tmp_img, 0, 0, 0));
                        imagecopyresized($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    } elseif ($cType == 'image/x-png') {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    } elseif ($cType == 'image/bmp') {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);

                    } elseif ($cType == 'image/jpeg') {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    } else {
                        $tmp_img = imagecreatetruecolor($thumbSize, $thumbSize);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);
                    }

                    imagedestroy($src_img);
                    $src_img = $tmp_img;
                } else {
                    $ow = imagesx($src_img);
                    $oh = imagesy($src_img);
                    if ($nH == 0 && $nW == 0) {
                        $nH = $oh;
                        $nW = $ow;
                    }
                    if ($nH == 0) {
                        $nH = $nW;
                    }
                    if ($nW == 0) {
                        $nW = $nH;
                    }
                    if ($nH > $oh && $nW > $ow) {
                        $width  = $ow;
                        $height = $oh;
                    } else {

                        if ($nW && ($ow < $oh)) {
                            $nW = ($nH / $oh) * $ow;
                        } else {
                            $nH = ($nW / $ow) * $oh;
                        }
                        $width  = $nW;
                        $height = $nH;
                    }
                    if ($cType == 'image/gif') {
                        $tmp_img = imagecreate($width, $height);
                        imagecolortransparent($tmp_img, imagecolorallocate($tmp_img, 0, 0, 0));
                        imagecopyresized($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } elseif ($cType == 'image/x-png') {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } elseif ($cType == 'image/bmp') {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } elseif ($cType == 'image/jpeg') {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    } else {
                        $tmp_img = imagecreatetruecolor($width, $height);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, $off_w, $off_h, $width, $height, $ow, $oh);
                    }
                    imagedestroy($src_img);
                    $src_img = $tmp_img;
                }
            }
            // set the output
            if ($cType == 'image/gif') {
                imageGIF($src_img, $cOutput);
            } elseif ($cType == 'image/x-png') {
                imagePNG($src_img, $cOutput);
            } elseif ($cType == 'image/bmp') {
                imageJPEG($src_img, $cOutput, $nQuality);
            } elseif ($cType == 'image/jpeg') {
                imageJPEG($src_img, $cOutput, $nQuality);
            } else {
                imageJPEG($src_img, $cOutput, $nQuality);
            }
        }
    }
}
