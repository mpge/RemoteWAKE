#!/usr/bin/env python 
import os.path
# General Config File for RemoteWAKE

HOST = "localhost" # change this to the ip you wish to listen on... Should be a public IP (port forwarded)
PORT = 2040 # 2040 by default
LISTEN_FROM = "0.0.0.0" # change this to your website server ip (not your website or host)
SystemPath = None # only change this manually to the path before this config file if an error is returned

# DO NOT EDIT!
if SystemPath == None:
  SystemPath = os.path.abspath(os.path.join(yourpath, os.pardir))
