import collections
import hashlib
import json
import os
import stat
import subprocess
import sys
import time
import ctypes

AUTH_LIB=None

def load_nasauth_lib():
    global AUTH_LIB
    if not AUTH_LIB:
        AUTH_LIB = ctypes.cdll.LoadLibrary('/usr/lib/libuLinux_nasauth.so')
    return AUTH_LIB
    
def get_local_sid():
    # --> The SID may have expiration issue, and need to considier working under
    #     multi-threads environment. So to get new SID all the time.
    naslib = load_nasauth_lib()
    c_sid = ctypes.c_char_p(b'1234567890')
    naslib.auth_add_session(c_sid, b'admin', 1, b'')
    if c_sid.value:
        return c_sid.value.decode()
    return None

print(get_local_sid())