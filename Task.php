<?php
/**
 * @author Damir Muratov damir3134@gmail.com
 */

namespace dmuratov\cron;

/**
 * Class Task
 * @package dmuratov\cron
 */
class Task
{
    /**
     * Task name
     *
     * @var string
     */
    public $name;

    /**
     * Command, execute by php yii
     * Example:
     *
     * mail/send
     *
     * @var string
     */
    public $command;

    /**
     * Time settings
     *
     * @var array
     */
    public $time = [
        'minute'        => '*',
        'hour'          => '*',
        'dayOfMonth'    => '*',
        'month'         => '*',
        'dayOfWeek'     => '*'
    ];

    /**
     * Check for need run that task
     *
     * @param $timeParts
     * @return bool
     */
    public function needRun($timeParts)
    {
        $needProcessTimes	= array(
            'minute'		=> $this->parseCronValue($this->time['minute'],		0, 59),
            'hour'			=> $this->parseCronValue($this->time['hour'],		0, 23),
            'dayOfMonth'	=> $this->parseCronValue($this->time['dayOfMonth'],	1, 31),
            'month'			=> $this->parseCronValue($this->time['month'],		1, 12),
            'dayOfWeek'		=> $this->parseCronValue($this->time['dayOfWeek'],	0, 6)
        );

        $isNeedProcess = in_array($timeParts['minute'], $needProcessTimes['minute']) &&
            in_array($timeParts['hour'], $needProcessTimes['hour']) &&
            in_array($timeParts['dayOfMonth'], $needProcessTimes['dayOfMonth']) &&
            in_array($timeParts['month'], $needProcessTimes['month']) &&
            in_array($timeParts['dayOfWeek'], $needProcessTimes['dayOfWeek']);

        return $isNeedProcess;
    }

    /**
     * @param string $value
     * @param int $min
     * @param int $max
     * @return array
     */
    private function parseCronValue($value, $min, $max)
    {
        $resultList = array();

        $explodeByComma = explode(',', $value);
        foreach ($explodeByComma as $currentItem)
        {
            // Check three situations:

            // Number
            if (preg_match('/^\d+$/', $currentItem))
            {
                $resultList[] = $currentItem;
            }

            // Interval of numbers
            if (preg_match('/^\d+\-\d+$/', $currentItem))
            {
                list($start, $end) = explode('-', $currentItem);
                for ($i = $start; $i <= $end; $i++)
                {
                    $resultList[] = $i;
                }
            }

            // If it *
            if ('*' == $currentItem)
            {
                for ($i = $min; $i <= $max; $i++)
                {
                    $resultList[] = $i;
                }
            }

            // (*/1)
            if (preg_match('/^\*\/\d+$/', $currentItem))
            {
                list(, $step) = explode('/', $currentItem);
                for ($i = $min; $i <= $max; $i += $step)
                {
                    $resultList[] = $i;
                }
            }
        }

        // Sort and remove duplicate
        $resultList = array_unique($resultList);
        sort($resultList);

        $filteredList = array();
        foreach ($resultList as $currentValue)
        {
            if ($min <= $currentValue && $currentValue <= $max)
            {
                $filteredList[] = $currentValue;
            }
        }

        return $filteredList;
    }
} 