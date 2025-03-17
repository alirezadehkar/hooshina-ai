<?php
namespace HooshinaAi\App\Notice;

class Notice
{
    public static function displaySuccess($message)
    {
        return new NoticeSuccess($message);
    }

    public static function displayWarning($message)
    {
        return new NoticeWarning($message);
    }

    public static function displayError($message)
    {
        return new NoticeError($message);
    }
}