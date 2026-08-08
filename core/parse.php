<?php
/* 过滤短代码 */
require_once('short.php');

// 安全转义
function _safeHtml($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

// 评论显示
function _parseCommentReply($text) {
    if (strpos($text, '{!{data:image') !== false) {
        echo substr($text, 3, -3);
        return;
    }

    // 正常评论处理
    $text = trim(str_replace(['<p>', '</p>'], '', $text));
    $s = _safeHtml($text);
    $s = _parseReply($s);
    echo $s;
}

// 表情解析
function _parseReply($text) {
    $url = _safeHtml(Helper::options()->themeUrl);
    $text = preg_replace('#::\(\s*(呵呵|哈哈|吐舌|滑稽|狗头)\s*\)#i', '<img src="'.$url.'/assets/owo/paopao/$1_2x.png">', $text);
    $text = preg_replace('#::@\(\s*(高兴|小怒|汗)\s*\)#i', '<img src="'.$url.'/assets/owo/aru/$1_2x.png">', $text);
    return $text;
}

// 留言显示
function _parseLeavingReply($text) {
    if (strpos($text, '{!{data:image') !== false) {
        echo substr($text, 3, -3);
        return;
    }
    $s = _safeHtml(strip_tags($text));
    echo _parseReply($s);
}

// 侧边栏
function _parseAsideReply($text, $type = true) {
    echo _safeHtml(strip_tags($text));
}

// 链接处理
function _parseAsideLink($link) {
    echo _safeHtml(str_replace('#', '?scroll=', $link));
}