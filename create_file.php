<?php

$opts = getopt('a::b::p:', array('brief::', 'author::', 'path:'));

//author set
$author = 'kstrwind';
if (isset($opts['author'])){
    $author = empty($opts['author']) ? '' : $opts['author'];
} elseif (isset($opts['a'])){
    $author = empty($opts['a']) ? '' : $opts['a'];
}

//brief set
$brief  = '';
if (!empty($opts['brief'])){
    $brief = $opts['brief'];
} elseif (!empty($opts['b'])){
    $brief = $opts['b'];
}
$format_res = formatStr($brief, ' * @brief   ', ' *          ', 80);

//path set
if (empty($opts['path']) && empty($opts['p'])){
    outMsg('[FATAL] file path not set');
    return 1;
}
if (!empty($opts['path'])){
    $path = $opts['path'];
} elseif (!empty($opts['p'])){
    $path = $opts['p'];
}
if ('/' == $path[0]){
    $r_path = $path;
} else {
    $r_path = __DIR__ . '/' . $path;
}
$p_path = substr($r_path, strlen(__DIR__) + 1);
if (!file_exists(dirname($r_path))){
    outMsg("[WARNING] dir[" . dirname($r_path) . "] not exists for $r_path");
    return 1;
}
if (file_exists($r_path)){
    outMsg("[WARNING] file[$r_path] already exists");
    return 1;
}

$date = date('Y/m/d H:i:s');

$out_contents = <<< FILE_CONTENTS
<?php
/*****************************************************************************
 *                                                                           *       
 *       Copyright (c) 2017 kstrwind. All Rights Reserved                    *  
 *                                                                           *
 *****************************************************************************/

/**
 * @file    $p_path 
 * @author  $author
 * @date    $date
$brief
 **/
FILE_CONTENTS;
file_put_contents($r_path, $out_contents);
//todo；to reconginize chinese zi
function formatStr(&$srcString, $firstLinePrefix, $otherLinePrefix, $lineLimit=0)
{
    $str        = $srcString;
    $fp_num     = strlen($firstLinePrefix);
    $op_num     = strlen($otherLinePrefix);
    $str_length = strlen($str); 

    //check line limit
    if ($fp_num > $lineLimit || $op_num > $lineLimit){
        $arrMsg = array(
            'message'   =>  'str format failed for prefix num overlimit',
            'firstLinePrefix'   =>  $firstLinePrefix,
            'firstLineNum'  =>  $fp_num,
            'otherLinePrefix'   =>  $otherLinePrefix,
            'otherLineNum'  =>  $op_num,
            'lineLimit'     =>  $lineLimit,
        );
        outMsg($arrMsg); 
        return false;
    }
 
    //format line
    $srcString = $firstLinePrefix;
    //one line, one char for \n
    if ($str_length <= $lineLimit - $fp_num - 1){
        $srcString .= $str;
        return true;
    }
    
    $curr_line_num = $fp_num; 
    $curr_word ='';
    for ($i = 0; $i < $str_length; $i++){
        $curr_char = $str[$i];
        $curr_line_num++;
        if ($curr_char == ' ' || $curr_char == "\t" || $curr_char == "\n" || ($i == $str_length - 1)){
            if ($curr_line_num < $lineLimit){
                $srcString .= $curr_word . $curr_char;
                $curr_word ='';
                continue;
            }
            if ($curr_line_num == $lineLimit){
                $srcString .= $curr_word . "\n";
                $curr_word = '';
                $srcString .= $otherLinePrefix . $curr_char;
                $curr_line_num = $op_num + 1; 
                continue;
            }
            if ($curr_line_num + 2 + $otherLinePrefix < 2 * $lineLimit){
                $srcString .= "\n" . $otherLinePrefix . $curr_word . $curr_char;
                $curr_line_num = $op_num + strlen($curr_word) + 1; 
                $curr_word = '';
                continue;
            }
            //else $curr_line_num + 2 + $otherLinePrefix >= 2 * $lineLimit
            $srcString .= "\n" . $otherLinePrefix . $curr_word . $curr_char ."\n";
            $curr_word = '';
            $curr_line_num = 0; 
            if ($i < $str_length - 1){
                $srcString .= $otherLinePrefix;
                $curr_line_num = $op_num; 
            }
        } else {
            $curr_word .= $curr_char;
        }
    }
    return true;
}

function outMsg($mixedInfo)
{
    $o_str = '';
    if (is_string($mixedInfo)){
        $o_str = trim($mixedInfo)."\n";
    } else if (is_array($mixedInfo)){
        foreach ($mixedInfo as $index => $value){
            $o_str .= '[' . trim($index) . '] [' . trim($value) . ']';
        }
        $o_str .= "\n";
    } else {
        $o_str = json_encode($mixedInfo) . "\n";
    }
    echo $o_str;
}
