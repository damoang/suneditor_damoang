<?php

function _get_hostname()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
    //cloudflare 사용시 처리
    if (isset($_SERVER['HTTP_CF_VISITOR']) && $_SERVER['HTTP_CF_VISITOR']) {
        if (json_decode($_SERVER['HTTP_CF_VISITOR'])->scheme == 'https') {
            $_SERVER['HTTPS'] = 'on';
        }
        $protocol = 'https://';
    }

    $domainName = $_SERVER['HTTP_HOST'];
    return $protocol . $domainName;
}


define('FT_NONCE_UNIQUE_KEY', sha1($_SERVER['SERVER_SOFTWARE'] . session_id() . G5_TABLE_PREFIX));

define('FT_NONCE_SESSION_KEY', hash('sha256', FT_NONCE_UNIQUE_KEY));

const UPLOAD_NONCE_TOKEN_NAME = 'image_upload';
const FT_NONCE_DURATION = 60 * 30; // 초단위
const FT_NONCE_KEY = '_nonce';

function ft_get_secret_key($secret)
{
    return md5(FT_NONCE_UNIQUE_KEY . $secret);
}

// This method creates an nonce. It should be called by one of the previous two functions.
function ft_nonce_create($action = '', $user = '', $timeoutSeconds = FT_NONCE_DURATION)
{
    $secret = ft_get_secret_key($action . $user);

    $salt = ft_nonce_generate_hash();
    $time = time();
    $maxTime = $time + $timeoutSeconds;
    $nonce = $salt . "|" . $maxTime . "|" . sha1($salt . $secret . $maxTime);

    set_session('nonce_' . FT_NONCE_SESSION_KEY, $nonce);

    return $nonce;
}

// This method validates an nonce
function ft_nonce_is_valid($nonce, $action = '', $user = '')
{
    $secret = ft_get_secret_key($action . $user);

    if (is_string($nonce) == false) {
        return false;
    }
    $a = explode('|', $nonce);
    if (count($a) != 3) {
        return false;
    }
    $salt = $a[0];
    $maxTime = (int)$a[1];
    $hash = $a[2];
    $back = sha1($salt . $secret . $maxTime);
    if ($back != $hash) {
        return false;
    }
    if (time() > $maxTime) {
        return false;
    }
    return true;
}

// This method generates the nonce timestamp
function ft_nonce_generate_hash()
{
    $length = 10;
    $chars = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
    $ll = strlen($chars) - 1;
    $o = '';
    while (strlen($o) < $length) {
        $o .= $chars[rand(0, $ll)];
    }
    return $o;
}
