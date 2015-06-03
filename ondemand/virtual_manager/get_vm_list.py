#!/usr/bin/python

from pysphere import *
from pprint import pprint

s = VIServer()
s.connect("vcenter02-sf","mark","Flurry@123!")

pprint(s.get_registered_vms())
