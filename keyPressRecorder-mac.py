from AppKit import NSApplication, NSApp
from Foundation import NSObject, NSLog
from Cocoa import NSEvent, NSKeyDownMask
from PyObjCTools import AppHelper

import requests
import time
import json

records = {}
begin   = int(time.time())

jsonData = open('./config.json').read()
config   = json.loads(jsonData)
USERNAME = config['username']
URL      = config['url']
GAP      = config['gap']

class AppDelegate(NSObject):
    def applicationDidFinishLaunching_(self, notification):
        mask = NSKeyDownMask
        NSEvent.addGlobalMonitorForEventsMatchingMask_handler_(mask, handler)


def handler(event):
    
    global records
    global begin
    global GAP
    global username
    global URL
    
    try:
        
        if event.keyCode():
            records[event.keyCode()] = int(time.time())
        
        now = int(time.time())
        
        if now - begin > GAP and records:
            print now
            print begin
            print records
            r = requests.post(URL, data={'user': USERNAME, 'records': json.dumps(records)})
            NSLog(r.status_code)
            records = {}
            begin   = int(time.time())
    
    except KeyboardInterrupt:
        AppHelper.stopEventLoop()


def main():
    app = NSApplication.sharedApplication()
    delegate = AppDelegate.alloc().init()
    NSApp().setDelegate_(delegate)
    AppHelper.runEventLoop()
    

if __name__ == '__main__':
    main()
