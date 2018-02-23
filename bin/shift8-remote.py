#!/usr/bin/python

import requests
import json
import sys
from datetime import datetime
import calendar

# Get unix timestamp to pass as variable
d = datetime.utcnow()
unixtime = calendar.timegm(d.utctimetuple())

###########################
# Check command arguments #
###########################
if len(sys.argv) < 3 :
        print "\nUsage Syntax :"
        print "\nshift8-remote.py <url> <key> <command> <argument> <argument value>"
        print "Argument is optional only if needed"
        print "\nExample : shift8-remote.py http://shift8.local abc1234 update_plugin plugin Hello_Dolly/hello.php"
        sys.exit(0)

try:
    sys.argv[4]
    sys.argv[5]
except:
    argument = None
    argument_value = None
else:
    argument = sys.argv[4]
    argument_value = sys.argv[5]

# Define arguments if applicable
shift8_url = sys.argv[1] 
payload = { 
        "shift8_remote_verify_key" : sys.argv[2],
        "timestamp" : unixtime,
        "actions[0]" : sys.argv[3],
        argument : argument_value,
        }

headers = {'content-type': 'application/x-www-form-urlencoded'}
response = requests.post(shift8_url, data=payload, headers=headers)
print response.text
