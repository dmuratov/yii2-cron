<?php
/**
 * @author Damir Muratov damir3134@gmail.com
 */

namespace dmuratov\cron;

use yii\base\Component;
use yii\helpers\Console;

class Manager extends Component
{
    /**
     * @var string
     */
    public $lockFile = 'cron.lock';

    /**
     * Time for all cron tasks
     *
     * @var int
     */
    public $maxTime = 180;

    /**
     * @var Task[] array
     */
    protected $tasks = [];

    /**
     * Added new task
     *
     * @param $name
     * @param $command
     * @param $time
     * @return $this
     */
    public function addTask($name, $command, $time)
    {
        $task = new Task();

        $task->name = $name;
        $task->command = $command;
        $task->time = $time;

        $this->tasks[] = $task;

        return $this;
    }

    /**
     * Исполнение всех необходимых тасков
     */
    public function run()
    {
        if (file_exists($this->lockFile)) {
            echo Console::ansiFormat("Previous cron doesn't complete\n", [Console::FG_RED]);

            if ((time() - filemtime($this->lockFile)) > $this->maxTime) {
                unlink($this->lockFile);
            }

            return false;
        }

        echo Console::ansiFormat("Cron started\n", [Console::FG_GREEN]);

        file_put_contents($this->lockFile, time());

        $currentTime		= time();
        $currentTimeParts	= array(
            'minute'		=> intval(date('i', $currentTime)),
            'hour'			=> intval(date('G', $currentTime)),
            'dayOfMonth'	=> intval(date('j', $currentTime)),
            'month'			=> intval(date('n', $currentTime)),
            'dayOfWeek'		=> intval(date('w', $currentTime))
        );

        foreach ($this->tasks as $task) {
            if ($task->needRun($currentTimeParts)) {
                echo Console::ansiFormat(sprintf("Task '%s' start running\n", $task->name), [Console::FG_GREEN]);

                shell_exec(PHP_BINDIR . '/php ' . \Yii::getAlias('@console/../yii') . ' ' . $task->command);
            }
        }

        echo Console::ansiFormat("Cron completed\n", [Console::FG_GREEN]);

        unlink($this->lockFile);

        return true;
    }
} 