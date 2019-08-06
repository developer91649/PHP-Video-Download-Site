<?php
require_once('common/constant.php');
require_once('common/languages.php');
$data = array();
$vimeoID = $_POST['vimeoID'];
$url = $_POST['n'];
$vimeoIDLength =strlen($vimeoID);
$checkVimeoID =0;
if($vimeoIDLength >=8){
    if(is_numeric($vimeoIDLength)){
        $realVimeoID = $vimeoID;
    }else{
        if (preg_match("/https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/", $url, $id)) {
            $realVimeoID = $id[3];
            $vimeoIDLength =strlen($realVimeoID);
            if($vimeoIDLength >=8){
                if(is_numeric($vimeoIDLength)){
                    $realVimeoID = $realVimeoID;
                    $checkVimeoID =1;
                }
               else{
                   $data['result'] ="failedRead";
                   $data['message'] =$language_could_not_download_from_this_url;
                   echo json_encode($data);
                   exit();
               }
            }else{
                $data['result'] ="failedRead";
                $data['message'] =$language_could_not_download_from_this_url;
                echo json_encode($data);
                exit();
            }
        }else{
            $data['result'] ="failedRead";
            $data['message'] =$language_could_not_download_from_this_url;
            echo json_encode($data);
            exit();
        }
    }

}else{
    if (preg_match("/https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/", $url, $id)) {
        $realVimeoID = $id[3];
        $vimeoIDLength =strlen($realVimeoID);
        if($vimeoIDLength >=8){
            if(is_numeric($vimeoIDLength)){
                $realVimeoID = $realVimeoID;
                $checkVimeoID =1;
            }
            else{
                $data['result'] ="failedRead";
                $data['message'] =$language_could_not_download_from_this_url;
                echo json_encode($data);
                exit();
            }
        }else{
            $data['result'] ="failedRead";
            $data['message'] =$language_could_not_download_from_this_url;
            echo json_encode($data);
            exit();
        }
    }else{
        $data['result'] ="failedRead";
        $data['message'] =$language_could_not_download_from_this_url;
        echo json_encode($data);
        exit();
    }
}
$time=mktime();
$deletePath = "../vimeo";
function rrmdir($dir) {
    foreach(glob($dir . '/' . '*') as $file) {
        if(is_dir($file)){
            rrmdir($file);
        }else{
            unlink($file);
        }
    }
    rmdir($dir);
}
if(is_dir($deletePath)){

    $objects = scandir($deletePath);
    foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
            if (filetype($deletePath."/".$object) == "dir"){
               if($time-$object >600){
                    rrmdir($deletePath."/".$object);
               }
            }
            else { unlink($deletePath."/".$object);}
        }
    }
    reset($objects);
}

mkdir('../vimeo/'.$time,0777);
chmod('../vimeo/'.$time,0777);

$realUrl = 'https://vimeo.com/api/oembed.json?url='.$url;
$getTitleList = (json_decode(file_get_contents($realUrl)));
$myTitle = $getTitleList->title;
if($checkVimeoID == 0){
    $url = "https://vimeo.com/".$realVimeoID;
}elseif($checkVimeoID == 1){
    $url ="https://player.vimeo.com/video/".$realVimeoID;
}
$fileFolder = '"/opt/lampp/htdocs/youtube/vimeo/'.$time.'/%(id)s.%(ext)s "' .$url;
$statement =   '/opt/lampp/htdocs/youtube/vimeo1.sh  '.$fileFolder ;
$result = array();
$status = '';
exec($statement, $result, $status);

if($status == 0) {
    $files= scandir($deletePath."/".$time ,1);
    $convertFileName = $files[0];
    $fileUrl =  "http://" . $_SERVER['SERVER_NAME']."/youtube/vimeoDownload.php?title=".$time."/".$convertFileName;
    $data['result'] ="success";
    $data['title'] = $myTitle;
    $data['vimeoID'] = $vimeoID;
    $data['url'] = $fileUrl;
}else{
    $data['result'] ="failed";
    $data['title'] = $myTitle;
    $data['vimeoID'] = $vimeoID;

}
echo json_encode($data);
exit();
?>