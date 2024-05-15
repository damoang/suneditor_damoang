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