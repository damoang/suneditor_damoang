<?php
include_once "./_common.php";

/***************************************************
 * Only these origins are allowed to upload images *
 ***************************************************/
if (!function_exists('_get_hostname')) {
    /**
     *  사이트 URL
     */
    function _get_hostname()
    {
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        //cloudflare 사용시 처리
        if (isset($_SERVER['HTTP_CF_VISITOR']) && $_SERVER['HTTP_CF_VISITOR']) {
            if (json_decode($_SERVER['HTTP_CF_VISITOR'])->scheme == 'https')
                $_SERVER['HTTPS'] = 'on';
            $protocol = 'https://';
        }

        $domainName = $_SERVER['HTTP_HOST'];
        return $protocol . $domainName;
    }
}
$accepted_origins = array(_get_hostname());

# 이미지 저장 폴더
$imageFolder = G5_DATA_PATH . '/' . 'editor/';
$imageurl =  G5_DATA_URL . '/' . 'editor/';
@mkdir($imageFolder, G5_DIR_PERMISSION);

reset($_FILES);
//$temp = current($_FILES);
$result = array();
foreach ($_FILES as $temp){
    if (isset($temp['tmp_name']) && is_uploaded_file($temp['tmp_name'])) {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // same-origin requests won't set an origin. If the origin is set, it must be valid.
            if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
                header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            } else {
                header("HTTP/1.1 403 Origin Denied");
                return;
            }
        }
    
        // Don't attempt to process the upload on an OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header("Access-Control-Allow-Methods: POST, OPTIONS");
            return;
        }
    
        // Sanitize input
        if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).가-힣])|([\.]{2,})/u", $temp['name'])) {
            //header("HTTP/1.1 400 Invalid file name.");
            echo json_encode(array('errorMessage' => '사용할 수 없는 파일명 입니다'));
            return;
        }
    
    
        // Verify extension
        if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "jpeg", "png", 'webp', 'svg'))) {
            //header("HTTP/1.1 400 Invalid extension.");
            echo json_encode(array('errorMessage' => 'gif, jpg, jpeg, png, webp, svg 파일만 업로드 할 수 있습니다'));
            return;
        }
    } else {
        // Notify editor that the upload failed
        header("HTTP/1.1 500 Server Error");
    }
}

foreach ($_FILES as $temp){
    //파일명 변경
    $upload = cut_str(md5(sha1($_SERVER['REMOTE_ADDR'])), 5, '-') . uniqid() . '-' . replace_filename($temp['name']);

    //이미지 SERVER PATH
    $filetowrite = $imageFolder . $upload;
    //이미지 URL
    $imageurl_full = $imageurl . $upload;
    move_uploaded_file($temp['tmp_name'], $filetowrite);
    // Respond to the successful upload with JSON.
    // Use a location key to specify the path to the saved image resource.
    // { location : '/your/uploaded/image/file'}
    $size = @filesize($filetowrite);
    array_push($result, array('url' => $imageurl_full, 'name' => $upload, 'size' => $size));
}

echo json_encode(array('result' => $result));