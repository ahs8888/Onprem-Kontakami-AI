<?php
namespace App\Enum;

enum ProcessType: string
{
     case RecordingScoring = "recording-scoring";
     case AgentScoring = "agent-scoring";
     case AgentProfiling = "agent-profiling";

     public function label(){
          return match($this){
               self::AgentProfiling => "Analysis Profiling",
               self::AgentScoring => "Agent Scoring",
               default => "Analysis Scoring"
          };
     }
}