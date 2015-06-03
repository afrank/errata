#!/usr/bin/python

import sys
from pysphere import *

if len(sys.argv) < 2:
	print "This script takes one argument: the name of the vm (eg. hbase-1)"
	exit(0)

host = sys.argv[1]

s = VIServer()
s.connect("vcenter02-sf","mark","Flurry@123!")
vm = s.get_vm_by_name(host)
print vm.get_status()
s.disconnect()
