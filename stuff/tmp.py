#!/usr/bin/python
# This file is part of tcollector.
# Copyright (C) 2010  The tcollector Authors.
#
# This program is free software: you can redistribute it and/or modify it
# under the terms of the GNU Lesser General Public License as published by
# the Free Software Foundation, either version 3 of the License, or (at your
# option) any later version.  This program is distributed in the hope that it
# will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
# of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser
# General Public License for more details.  You should have received a copy
# of the GNU Lesser General Public License along with this program.  If not,
# see <http://www.gnu.org/licenses/>.

import os
import re
import signal
import subprocess
import sys
import time
import threading
### import pygeoip
from collectors.lib import utils
from collections import defaultdict

SUMMARY_INTERVAL = 60

### gi = pygeoip.GeoIP('/usr/local/blacksmith/resources/GeoIPCity.dat', pygeoip.MEMORY_CACHE)

def kill(proc):
    """Kills the subprocess given in argument."""
    # Clean up after ourselves.
    proc.stdout.close()
    rv = proc.poll()
    if rv is None:
        os.kill(proc.pid, 15)
        rv = proc.poll()
        if rv is None:
            os.kill(proc.pid, 9)  # Bang bang!
            rv = proc.wait()  # This shouldn't block too long.
    print >> sys.stderr, "warning: proc exited %d" % rv
    return rv

def do_on_signal(signum, func, *args, **kwargs):
    """Calls func(*args, **kwargs) before exiting when receiving signum."""

    def signal_shutdown(signum, frame):
        print >> sys.stderr, "got signal %d, exiting" % signum
        func(*args, **kwargs)
        sys.exit(128 + signum)
                                                                                                                                                                                          50,9          Top
        func(*args, **kwargs)
        sys.exit(128 + signum)

    signal.signal(signum, signal_shutdown)

pl = {}

def main(argv):
        # p = subprocess.Popen(["/bin/cat","/var/run/adbidder.pipe"], stdout=subprocess.PIPE, bufsize=1)
        p = subprocess.Popen(["/usr/bin/tail","-qF","/var/log/nginx/access.log"], stdout=subprocess.PIPE, bufsize=1)
        do_on_signal(signal.SIGINT, kill, p)
        do_on_signal(signal.SIGPIPE, kill, p)
        do_on_signal(signal.SIGTERM, kill, p)
        reporter()
        global pl
        while True:
            #sys.stdout.write('.')
            line = p.stdout.readline()

            if not line and p.poll() is not None:
                break  # Nothing more to read and process exited.

            # 74.201.193.107 - - [13/Jan/2014:16:37:26 -0800] bidder.flurry.com POST /mopub HTTP/1.1 "204" 0 "-" "-" "-" 0.006
            m = re.match(r'(?P<address>[0-9.]*) - (?P<user>[^ ]*) \[(?P<timestamp>[^\]]*)\] (?P<vip>[^ ]*) (?P<method>[^ ]*) (?P<uri>[^ ]*) (?P<proto>[^ ]*) "(?P<status>[0-9]*)" (?P<bytes_sent>[0-9]*) "(?P<referer>[^"]*)" "(?P<user_agent>[^"]*)" "(?P<x_forward>[^"]*)" (?P<request_time>[0-9.]*)', line)
            if m is not None:
                d = m.groupdict()
                d['timestamp'] = time.strptime(d['timestamp'], "%d/%b/%Y:%H:%M:%S -0800")
                ## rec = gi.record_by_addr(d['address'])
                rec = None
                if d['vip'] is None:
                        d['vip'] = "Unspecified"
                if not d['vip'] in pl:
                        pl[d['vip']] = {}
                m1 = re.match(r'(?P<first_element>/[^/]*).*',d['uri'])
                uri = m1.groupdict()
                if uri is not None:
                        d['uri_first'] = uri['first_element']
                else:
                        d['uri_first'] = '/'
                if not d['uri_first'] in pl[d['vip']]:
                        pl[d['vip']][d['uri_first']] = {}
                if not "code" in pl[d['vip']][d['uri_first']]:
                        pl[d['vip']][d['uri_first']]['code'] = {}
                if not d['status'] in pl[d['vip']][d['uri_first']]['code']:
                        pl[d['vip']][d['uri_first']]['code'][d['status']] = 0
                pl[d['vip']][d['uri_first']]['code'][d['status']] += 1
                #if rec is not None:
                #       d['region'] = rec['region_code']
                #       if not "region" in pl:
                                                                                                                                                                                          49,9          67%
                #       d['region'] = rec['region_code']
                #       if not "region" in pl:
                #               pl['region'] = {}
                #       if not d['region'] in pl['region']:
                #               pl['region'][d['region']] = 0
                #       pl['region'][d['region']] += 1
            else:
                d = {}

def reporter():
        global pl
        # stamp = time.time()*1000
        stamp = time.time()
        interval = 10
        if pl is not None:
                for d in pl:
                        for p in pl[d]:
                                for c in pl[d][p]['code']:
                                        print "nginx.status_codes %i %s domain=%s code=%s path=%s" % (stamp,pl[d][p]['code'][c]/interval,d,c,p)
                pl = {}
        threading.Timer(interval, reporter).start()

if __name__ == "__main__":
    sys.exit(main(sys.argv))

