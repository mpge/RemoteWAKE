#!/usr/bin/env python 

# RemoteWAKE Python Server Logic
# This server receives responses from the website, authenticates them, parses them, and if everything is in working
# order, creates a alarm of annoyingness set by yourself. Good Luck!
# For disclaimer, please see RemoteWake.py descriptive comments at the top of the file.
# !-- Massive Comments End!
# !-- Software.... COMMENCE...
# File Description:
# logic.py - Contains logic for the RemoteWAKE server (utilized by RemoteWake.py)

from sys import platform as _platform
from sound import Sounds
from config import SystemPath
import sys

# colors -> sourced: http://stackoverflow.com/questions/287871/print-in-terminal-with-colors-using-python
class bcolors:
    HEADER = '\033[95m'
    OKBLUE = '\033[94m'
    OKGREEN = '\033[92m'
    WARNING = '\033[93m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'

    def disable(self):
        self.HEADER = ''
        self.OKBLUE = ''
        self.OKGREEN = ''
        self.WARNING = ''
        self.FAIL = ''
        self.ENDC = ''

def run_sound(sound_id):
  sound_file = SystemPath + Sounds.get(sound_id)
  # check operating system because sounds have to played in different ways for different operating systems.
  if _platform == "linux" or _platform == "linux2":
    # linux
    # this is going to be a bit more complex
    from wave import open as waveOpen
    from ossaudiodev import open as ossOpen
    s = waveOpen(sound_file,'rb')
    (nc,sw,fr,nf,comptype, compname) = s.getparams( )
    dsp = ossOpen('/dev/dsp','w')
    try:
      from ossaudiodev import AFMT_S16_NE
    except ImportError:
      if byteorder == "little":
        AFMT_S16_NE = ossaudiodev.AFMT_S16_LE
      else:
        AFMT_S16_NE = ossaudiodev.AFMT_S16_BE
    dsp.setparameters(AFMT_S16_NE, nc, fr)
    data = s.readframes(nf)
    s.close()
    dsp.write(data)
    dsp.close()
    return True

  elif _platform == "darwin":
    # OS X 10.5
    import subprocess
  
    return_code = subprocess.call(["afplay", sound_file])
    
    return True

  elif _platform == "win32":
    # Windows...
    import winsound
    winsound.PlaySound('%s.wav' % sound_file, winsound.SND_FILENAME)
    return True

  return False
    
def run_alarm(alarm_type, how_long, from_name, from_message):
    if run_sound(alarm_type):
      print >>sys.stderr, bcolors.OKGREEN + 'ALARM STARTED BY ' + from_name + ' WITH MESSAGE "' + from_message + '"' + 
        + bcolors.ENDC
      print >>sys.stderr, 'Press "Ctrl-C" to stop the alarm sound and restart the listening server'
      return True
    else:
      print >>sys.stderr, 'Could not start Alarm and Sound!'
      return False
      
