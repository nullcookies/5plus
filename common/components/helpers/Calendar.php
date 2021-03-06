<?php
namespace common\components\helpers;

class Calendar
{
    public static $weekDays = [
        'Воскресенье',
        'Понедельник',
        'Вторник',
        'Среда',
        'Четверг',
        'Пятница',
        'Суббота',
    ];
    public static $weekDaysShort = [
        'ВС',
        'ПН',
        'ВТ',
        'СР',
        'ЧТ',
        'ПТ',
        'СБ',
    ];
    public static $monthNames = [
        '',
        'Январь',
        'Февраль',
        'Март',
        'Апрель',
        'Май',
        'Июнь',
        'Июль',
        'Август',
        'Сентябрь',
        'Октябрь',
        'Ноябрь',
        'Декабрь',
    ];

    /**
     * @param int $month
     * @return string
     * @throws \Exception
     */
    public static function getMonthForm2(int $month): string
    {
        if (!array_key_exists($month, self::$monthNames)) throw new \Exception('Wrong month: ' . $month);
        $monthName = mb_strtolower(self::$monthNames[$month], 'UTF-8');
        $lastLetter = mb_substr($monthName, mb_strlen($monthName, 'UTF-8') - 1, 1, 'UTF-8');
        if ($lastLetter == 'ь' || $lastLetter == 'й') {
            $monthName = mb_substr($monthName, 0, mb_strlen($monthName, 'UTF-8') - 1, 'UTF-8') . 'я';
        } else {
            $monthName .= 'а';
        }

        return $monthName;
    }
}