#!/usr/bin/python

import string
import sys
# import random
import getopt
import MySQLdb
from pysphere import *

def usage():
	print "%15s     %-30s" % ("--type|-t","Host Type (web, hbase)")
	print "%15s     %-30s" % ("--hostname|-h","Hostname for new host")
	exit(0)

type = None
hostname = None
opts, args = getopt.getopt(sys.argv[1:], "t:h:", ["type=", "hostname="])
for o,a in opts:
	if o in ("-t","--type"):
		type = a
	elif o in ("-h","--hostname"):
		hostname = a

if type is None or hostname is None:
	usage()

if type == 'web':
	vm_to_use = '[esx-vol3] web-template-20140415.1/web-template-20140415.1.vmtx'
	# vm_to_use = '[esx-vol3] web-dev-template-1/web-dev-template-1.vmx'
elif type == 'hbase':
	vm_to_use = '[esx-vol3] hbase-template-20140415.2/hbase-template-20140415.2.vmtx'
	# vm_to_use = '[esx-vol3] hbase-dev-template-1/hbase-dev-template-1.vmx'
else:
	usage()

db = MySQLdb.connect(host="crunchberry",user="pe",passwd="adamRULES",db="pe_systems")
cur = db.cursor()

cur.execute("SELECT * FROM virtual_instances WHERE name = '%s'" % hostname)
if cur.rowcount > 0:
	print "Must select unique hostname"
	usage()

cur.execute("INSERT INTO virtual_instances (type,name,status) VALUES ('%s','%s',1)" % (type,hostname))
db.commit()

s = VIServer()

s.connect("vcenter02-sf","mark","Flurry@123!")

# pick least-populated resource pool
print "Selecting Pool to use..."
pools = {}
resource_pools = s.get_resource_pools()
for r in resource_pools.keys():
	if not string.replace(resource_pools[r],'/Resources','') == '':
		key = string.replace(resource_pools[r],'/Resources/','')
		pools[key] = r

pool_count = {}
for vmpath in s.get_registered_vms(status='poweredOn'):
	vm = s.get_vm_by_path(vmpath)
	pool = vm.get_resource_pool_name()
	if pool in pool_count:
		pool_count[pool] += 1
	else:
		pool_count[pool] = 1

use_pool = { 'pool' : 'nopool', 'running' : 999 }
for p in pools.keys():
	if not p in pool_count:
		pool_count[p] = 0

	if p is not None and pool_count[p] < use_pool['running']:
		use_pool['pool'] = p
		use_pool['running'] = pool_count[p]

print "Selected %s" % use_pool['pool']
a_rp = pools[use_pool['pool']]

template = s.get_vm_by_path(vm_to_use)
print "Starting up new VM. This will take some time..."
new_vm = template.clone(hostname,resourcepool=a_rp)
print new_vm.get_status()

cur.execute("UPDATE virtual_instances SET status = 2 WHERE name = '%s'" % hostname)
db.commit()

s.disconnect()
