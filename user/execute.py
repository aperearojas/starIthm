import subprocess
import sys
recipient_email = sys.argv[1]
user_name = sys.argv[2]
execution = 'python exec.py '+ recipient_email + ' ' + user_name
subprocess.Popen(execution, shell=True) ##asynchronous one
#subprocess.check_output('python exec.py', shell=True, stderr=subprocess.STDOUT) ##non-async
