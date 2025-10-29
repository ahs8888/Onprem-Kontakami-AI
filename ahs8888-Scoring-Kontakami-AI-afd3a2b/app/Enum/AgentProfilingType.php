<?php
namespace App\Enum;

enum AgentProfilingType: string
{
     case ScoringRecording = "scoring-recording";
     case ProfilingTemplate = "profiling-template";

     public function label(){
          return match($this){
               self::ScoringRecording => 'Agent Scoring on Recording',
               self::ProfilingTemplate => 'Agent Profiling Template',
               default => '##'
          };
     }
}