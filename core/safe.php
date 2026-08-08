<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

Typecho_Plugin::factory('Widget_Feedback')->comment = function($comment, $obj) {
    $text = $comment['text'];

    // 白名单：data:image 直接放行，不做任何检查！
    if (stripos($text, 'data:image') !== false) {
        return $comment;
    }

    // XSS 拦截（仅针对普通文本）
    $blacklist = [
        '/javascript:/i',
        '/vbscript:/i',
        '/<script/i',
        '/<iframe/i',
        '/<svg/i',
        '/on\w+=/i',
    ];

    foreach ($blacklist as $pattern) {
        if (preg_match($pattern, $text)) {
            throw new Typecho_Widget_Exception('🚫 评论包含不安全内容');
        }
    }

    return $comment;
};