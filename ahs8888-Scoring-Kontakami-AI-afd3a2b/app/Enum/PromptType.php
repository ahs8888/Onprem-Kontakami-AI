<?php
namespace App\Enum;

enum PromptType: string
{
     case RecordingScoring = 'recording-scoring';
     case AgentProfiling = 'agent-profiling';

     public function label()
     {
          return match ($this) {
               self::RecordingScoring => 'Recording Scoring',
               self::AgentProfiling => 'Agent Profiling',
               default => '##'
          };
     }
}