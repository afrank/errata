#!/usr/bin/python

import sys
from pysphere import *

if len(sys.argv) < 2:
	print "this script takes one argument: a vm name"
	exit(2)

name = sys.argv[1]

s = VIServer()
s.connect("vcenter02-sf","mark","Flurry@123!")

vm = s.get_vm_by_name(name)

ip = vm.get_property('ip_address')
if ip is not None:
	print ip
