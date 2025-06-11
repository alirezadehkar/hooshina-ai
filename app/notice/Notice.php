<?php
namespace HooshinaAi\App\Notice;

class Notice
{
    public static function success($message, $content = null)
    {
        return new NoticeSuccess($message, $content);
    }

    public static function warning($message, $content = null)
    {
        return new NoticeWarning($message, $content);
    }

    public static function error($message, $content = null)
    {
        return new NoticeError($message, $content);
    }
}