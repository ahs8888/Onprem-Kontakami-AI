export const tabMenuAgentAnalysis = () => {
     return [
          {
               id: 'prompt',
               url: route('setup.agent-analysis.prompt.index'),
               label: 'Setup Prompt',
          },
          {
               id: 'profiling',
               url: route('setup.agent-analysis.profiling.index'),
               label: 'Agent Profiling',
          }
     ]
}

export const tabMenuSettings = () => {
     return [
          {
               id: 'cloud-location',
               url: route('setting.cloud-location.index'),
               label: 'Clouds Location',
          },
          {
               id: 'personal-setting',
               url: route('setting.personal.index'),
               label: 'Personal Setting',
          }
     ]
}

export const getTabMenuRecordingAnalysis = (is_admin = false) => {
     if (is_admin) {
          return []
     }
     return [
          {
               id: 'prompt',
               url: route('setup.recording-analysis.prompt.index'),
               label: 'Prompt Setup',
          },
          {
               id: 'recording-text',
               url: route('setup.recording-analysis.recording-text.index'),
               label: 'Recording Text',
          },
          {
               id: 'recording-scoring',
               url: route('setup.recording-analysis.recording-scoring.index'),
               label: 'Recording Scoring',
          },
          {
               id: 'agent-scoring',
               url: route('setup.recording-analysis.agent-scoring.index'),
               label: 'Agent Scoring',
          }
     ]
}