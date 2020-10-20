<?php
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('禁止访问') ;

/**
* 判断是否SSL协议
* @return boolean
*/
function is_ssl() {

    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
        return true;
    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
        return true;
    }
    return false;

}

/**
 *获得当前完整的网址
 * @return string
 */
function GetCurUrl()
{
    $http =is_ssl()?'https://':'http://';
    $host = $_SERVER['HTTP_HOST'];
    if(!empty($_SERVER["REQUEST_URI"]))
    {
        $scriptName = $_SERVER["REQUEST_URI"];
        $nowurl = $scriptName;
    }
    else
    {
        $scriptName = $_SERVER["PHP_SELF"];
        if(empty($_SERVER["QUERY_STRING"]))
        {
            $nowurl = $scriptName;
        }
        else
        {
            $nowurl = $scriptName."?".$_SERVER["QUERY_STRING"];
        }
    }
    return $http.$host.$nowurl;
}
//定义静态页面输出目录
define ('html_path', __DIR__.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR."static".DIRECTORY_SEPARATOR);

//加密网址
$md5 = md5(GetCurUrl());

//获取静态文件路径
$filepath = html_path.substr($md5,0,2).DIRECTORY_SEPARATOR.substr($md5,2).'.html';
if (file_exists($filepath)){
    header("Content-Type: text/html; charset=utf-8");
    die (file_get_contents($filepath));
}