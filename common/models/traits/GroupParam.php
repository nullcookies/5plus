<?php

namespace common\models\traits;

use common\components\helpers\Money;

/**
 * Trait GroupParam
 * @package common\models\traits
 * @property string $schedule
 * @property string[] $scheduleData
 * @property int $lesson_price
 * @property int $lesson_price_discount
 * @property int $priceMonth
 * @property int $price3Month
 * @property int $classesPerWeek
 * @property int $classesPerMonth
 */
trait GroupParam
{
    /**
     * @return string[]
     */
    public function getScheduleData(): array
    {
        $data = json_decode($this->getAttribute('schedule'), true);
        if (empty($data)) $data = array_fill(0, 7, '');
        return $data;
    }

    /**
     * @param string[] $value
     */
    public function setScheduleData(array $value)
    {
        $valid = true;
        if (count($value) != 7) $valid = false;
        else {
            foreach ($value as $item) {
                if (!is_string($item) || (!empty($item) && !preg_match('#^\d{2}:\d{2}$#', $item))) $valid = false;
            }
        }
        if (!$valid) $value = array_fill(0, 7, '');
        $this->setAttribute('schedule', json_encode($value));
    }

    /**
     * @return int
     */
    public function getPriceMonth(): int
    {
        return Money::roundThousand($this->lesson_price * $this->getClassesPerMonth());
    }

    /**
     * @return int
     */
    public function getPrice3Month(): int
    {
        return Money::roundThousand(($this->lesson_price_discount ?: $this->lesson_price) * $this->getClassesPerMonth() * 3);
    }

    /**
     * @return int
     */
    public function getClassesPerWeek(): int
    {
        $count = 0;
        foreach ($this->scheduleData as $elem) {
            if (!empty($elem)) $count++;
        };
        return $count;
    }

    /**
     * @return int
     */
    public function getClassesPerMonth(): int
    {
        return $this->getClassesPerWeek() * 4;
    }

    /**
     * @param \DateTime $day
     * @return string
     */
    public function getLessonTime(\DateTime $day): string
    {
        if (!$this->isHasLesson($day)) return '';
        return $this->scheduleData[(7 + intval($day->format('w')) - 1) % 7] . ':00';
    }

    /**
     * @param \DateTime $day
     * @return string
     */
    public function getLessonDateTime(\DateTime $day): string
    {
        if (!$this->isHasLesson($day)) return '';
        return $day->format('Y-m-d') . ' ' . $this->getLessonTime($day);
    }

    /**
     * @param \DateTime $day
     * @return bool
     */
    public function isHasLesson(\DateTime $day): bool
    {
        return !empty($this->scheduleData[(7 + intval($day->format('w')) - 1) % 7]);
    }
}