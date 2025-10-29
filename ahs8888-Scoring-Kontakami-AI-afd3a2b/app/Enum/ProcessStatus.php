<?php
namespace App\Enum;

enum ProcessStatus: string
{
     case Queue = "queue";
     case Progress = "progress";
     case Finish = "finish";
     case Done = "done";
     case Failed = "failed";
}