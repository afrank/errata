from pysphere import *
from pysphere.resources import VimService_services as VI
from pysphere.vi_task import VITask

def delete_from_disk(s,vm_path):
	vm = s.get_vm_by_path(vm_path)

	#Invoke Destroy_Task
	request = VI.Destroy_TaskRequestMsg()
	_this = request.new__this(vm._mor)
	_this.set_attribute_type(vm._mor.get_attribute_type())
	request.set_element__this(_this)
	ret = s._proxy.Destroy_Task(request)._returnval

	#Wait for the task to finish
	task = VITask(ret, s)

	status = task.wait_for_state([task.STATE_SUCCESS, task.STATE_ERROR])
	if status == task.STATE_SUCCESS:
		print "VM successfully deleted from disk"
		return True
	elif status == task.STATE_ERROR:
		print "Error removing vm:", task.get_error_message()
		return False

def unregister_vm(s,vm):
	mor = vm._mor
	request = VI.UnregisterVMRequestMsg()
	_this = request.new__this(mor)
	_this.set_attribute_type(mor.get_attribute_type())
	request.set_element__this(_this)
	s._proxy.UnregisterVM(request)
	return True
