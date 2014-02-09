#!/usr/bin/env python 

# RemoteWAKE Python Server
# This server receives responses from the website, authenticates them, parses them, and if everything is in working
# order, creates a alarm of annoyingness set by yourself. Good Luck!
# Now I have to put a disclaimer... Kind of ridiculous... I know...
# Disclaimer: This software is provided as is. The developers, contributors and/or maintainers of this open source project
# are not AND will not be held responsible for any physical damage or harm caused this application/software. If you 
# have any heart, nerve conditions and disabilities or high blood pressure, PLEASE DO NOT USE THIS SOFTWARE APPLICATION.
# Copyright 2014 Matthew Gross (Matt Gross) (mattgross.net)(http://github.com/MatthewGross)
# Github: http://github.com/MatthewGross/RemoteWAKE
# Website: http://mattgross.net/projects/remotewake
# Enjoy! Litle note: Only give this to people you trust. If you are stupid enough to throw the login on Facebook or Twitter
# I tend to believe you deserve to be woken up with a huge alarm every 5 seconds.
# !-- Massive Comments End!
# !-- Software.... COMMENCE...
# File Description:
# RemoteWake.py - Primary run script and listener for the RemoteWAKE Server.

import socket
import sys
from time import sleep # for setting alarms in advance
from config import *
from logic import run_alarm

if not HOST:
  HOST = "localhost" # default : localhost
if not PORT:
  PORT = 2040 # default port is 2040
if not LISTEN_FROM:
  print >>sys.stderr, 'Please set LISTEN_FROM within config.py'
  # exit
  sys.exit()

# variables already set from "from config import *: HOST, PORT, BUFFER_SIZE, LISTEN_FROM
def RemoteWakeListener(HOST, PORT, LISTEN_FROM):
  # configs set... let's go!
  sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
  server_addr = (host, port)
  print >>sys.stderr, 'Starting RemoteWAKE Server on %s port %s ... Listening...' % server_addr
  sock.bind(server_addr)
  # Listen for incoming
  sock.listen(1)
  
  while True:
    # waiting...
    # no message required
    connection, client_addr = sock.accept()
    try:
      print >>sys.stderr, 'Incoming Connection from ', client_addr
      # define default response message
      message = ''
      # check if client_addr matches LISTEN_FROM, otherwise it's another site...
      if client_addr == LISTEN_FROM:
        # some configs
        wake_up_message = 'Running Alarm! WAKE UP!'
        # allowed IP
        # listen for data
        while True:
          data = connection.recv(16)
          print >>sys.stderr, 'received "%s"' % data
          if data:
            print >>sys.stderr, 'Data Received... Parsing...'
            pairs = data.split('||') # split by '||'
            # setup dict
            result = {}
            # loop through splitted data
            for pair in pairs:
              (key, value) = pair.split('==')
              result[key] = value
              print >>sys.stderr, 'Config %s set to "%s"' %(key, value)
            print >>sys.stderr, "All Results Set... Completing Action..."
            # setup variables, we are going to need them either way...
            alarm_type = int(result.get("alarm_type")) # is an integer
            how_long = int(float(result.get("how_long")) # in seconds
            from_name = result.get("from_name") # string
            from_message = result.get("from_message") # string
            # check action
            if action == "startAlarm":
              # start alarm now
              print >>sys.stderr, wake_up_message
              run_alarm(alarm_type, how_long, from_name, from_message)
            if action == "setAlarm":
              # setting the alarm in advance... Start the countdown
              countdown = int(float(result.get("time")) # in seconds
              # post status to server
              print >>sys.stderr, 'Setting Countdown at %s Seconds' % countdown
              # countdown!
              while True:
                # counting down from countdown
                if countdown > 0:
                  countdown = (countdown-1)
                  print >>sys.stderr, 'Alarm in %s Seconds...' % countdown
                else:
                  # countdown is 0
                  print >>sys.stderr, wake_up_message
                  # run alarm
                  run_alarm(alarm_type, how_long, from_name, from_message)
                  # exit while loop:
                  break
              
          else:
            print >>sys.stderr, 'No More Data from', client_addr
            break

      else:
        # send connection refused message
        message = "Connection Refused... Your IP Address is Not Allowed to send to this server. Error: IP_CONNECTION_REFUSED"
      
      # send default message
      connection.send(message)

if __name__ == "__main__":
  RemoteWakeListener(HOST, PORT, LISTEN_FROM)
  
