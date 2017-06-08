<?php
error_reporting(0);
$jumpUrl = "";
$timeOut = 3600 * 24 * 7;
$fileName = "shortUrl.json";
$host = $_SERVER['HTTP_HOST'];
$rUrl = "http://".$host."/j.php?c=";
$c = $_GET['c'];
$u =  $_GET['u'];
if(strpos($u,$host) > 0 || strpos($u,'"') > 0)
{
    echo logE(null);
    return;
}

$shortUrl = fopen($fileName,"r") or die("Unable to open file!");
$fileData = fread($shortUrl,filesize($fileName));
fclose($shortUrl);
if(!$fileData)
{
    echo logE(null);
    return;
}

$json = json_decode($fileData);
$data = $json -> urlData;
for($i=0;$i<count($data);$i++)
{
    $urlLong = $data[$i] -> url;
    $urlCode = $data[$i] -> code;
    $urlTime = $data[$i] -> rtime;
    $interval = time() - $urlTime;
    if($interval > $timeOut)
    {
        //echo "del:".$urlLong,$urlCode,$urlTime," ",strpos($urlLong, "acger"),"<br />";
        $shortUrl = fopen($fileName, "w") or die("Unable to open file!");
        $url = '{"url":"'.$urlLong.'","code":"'.$urlCode.'","rtime":"'.$urlTime.'"},';
        if(strpos($urlLong, "kw") > 0 || strpos($urlLong, "acger") > 0)
        {
            $fileData = str_replace($url,'{"url":"'.$urlLong.'","code":"'.$urlCode.'","rtime":"'.time().'"}',$fileData);
        }
        else
        {
            $fileData = str_replace($url,'',$fileData);
        }
        fwrite($shortUrl,$fileData);
    }

    if($c == $urlCode || $urlLong == $u)
    {
        $jumpUrl = $urlLong;
        $jumpCode = $urlCode;
        $jumpTime = $urlTime;
        break;
    }
}
fclose($shortUrl);
//跳转链接不存在写入
if(!$jumpUrl && $u)
{
    $rCode = getRandomString(10);
    $shortUrl = fopen($fileName, "w") or die("Unable to open file!");
    $fileData = str_replace('}]}','},{"url":"'.$u.'","code":"'.$rCode.'","rtime":"'.time().'"}]}',$fileData);
    fwrite($shortUrl,$fileData);
    fclose($shortUrl);
    echo logE($rUrl.$rCode);
}
elseif(!$c && $jumpCode)
{
    echo logE($rUrl.$jumpCode);
}
elseif($c && $jumpUrl && $interval < $timeOut || strpos($jumpUrl, "kw") > 0 || strpos($jumpUrl, "acger") > 0)
{
    echo '<script>window.location.href = "'.$jumpUrl.'";</script>';
}
else
{
    echo '<script>window.location.href = "http://'.$host.'/";</script>';
}
function getRandomString($len, $chars=null)
{
    if (is_null($chars))
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-/_";
    }  
    mt_srand(10000000*(double)microtime());
    for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++)
    {
        $str .= $chars[mt_rand(0, $lc)];  
    }
    return $str;
}
function logE($text)
{
    $Ret = array(
        "shortUrl" => $text
    );
    $Ret = json_encode($Ret);
    return $Ret;
}
//{"urlData":[{"url":"https://acger.moe","code":"38L4u6eA","rtime":"1495873038"}]}