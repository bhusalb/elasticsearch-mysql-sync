<?php
/**
 * Created by PhpStorm.
 * User: proshore
 * Date: 11/28/17
 * Time: 12:08 PM
 */

namespace Bhusalb;


class ExecutionTime
{
    private $startTime;
    private $endTime;

    public function start()
    {
        $this->startTime = getrusage();
    }

    public function end()
    {
        $this->endTime = getrusage();
    }

    public function __toString()
    {
        return "This process used " . $this->runTime($this->endTime, $this->startTime, "utime") .
            " ms for its computations\nIt spent " . $this->runTime($this->endTime, $this->startTime, "stime") .
            " ms in system calls\n";
    }

    private function runTime($ru, $rus, $index)
    {
        return ($ru["ru_$index.tv_sec"] * 1000 + intval($ru["ru_$index.tv_usec"] / 1000))
            - ($rus["ru_$index.tv_sec"] * 1000 + intval($rus["ru_$index.tv_usec"] / 1000));
    }
}