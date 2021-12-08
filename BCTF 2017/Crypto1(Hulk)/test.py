#!/usr/bin/env python
# -*- coding: utf-8 -*-

class Unbuffered(object):
   def __init__(self, stream):
       self.stream = stream
   def write(self, data):
       self.stream.write(data)
       self.stream.flush()
   def __getattr__(self, attr):
       return getattr(self.stream, attr)

import sys
sys.stdout = Unbuffered(sys.stdout)
from Crypto.Cipher import AES
import os
import binascii
import re

key = os.urandom(16)
flag = "bctf{qwedsazxcvfrtgbnhyujmkaiolpjhgfd}"

def encrypt(plain, iv_p=None, refresh_key=True):
	global key
	if refresh_key:
		key = os.urandom(16)
	if iv_p == None:
		iv_p = os.urandom(16)
	pad = 16 - (len(plain) % 16)
	if pad == 0: pad = 16
	plain += chr(pad)*pad
	c = AES.new(key=key, mode=AES.MODE_CBC, IV=iv_p)
	return c.encrypt(plain)



def hex2charlist(hexstr):
    charlist = []
    length = len(hexstr)
    if length % 2 != 0:
        hexstr = '0' + hexstr
        length += 1
    for i in range(0, length, 2):
        charlist.append(chr(int(hexstr[i]+hexstr[i+1], 16)))
    return charlist

if __name__ == '__main__':
    pattern = '\A[0-9a-fA-F]+\Z'
    request1 = raw_input('Give me the first hex vaule to encrypt: 0x').strip()
    if len(request1) > 96 or not re.match(pattern, request1):
        print 'invalid input, bye!'
        exit(0)
    plaintext1 = "".join(item for item in hex2charlist(request1)) + flag
    print len(plaintext1)
    ciphertext1 = encrypt(plaintext1, refresh_key = True)
    plaintext1_str = request1+'|flag'
    ciphertext1_str = ciphertext1.encode('hex')
    print 'plaintext: 0x%s\nciphertext: 0x%s' % (plaintext1_str, ciphertext1_str)

    request2 = raw_input('Give me the second hex vaule to encrypt: 0x').strip()
    if len(request2) > 96 or not re.match(pattern, request2):
        print 'invalid input, bye!'
        exit(0)
    plaintext2 = "".join(item for item in hex2charlist(request2))
    ciphertext2 = encrypt(plaintext2, iv_p = str(ciphertext1[-16:]), refresh_key = False)
    plaintext2_str = request2
    ciphertext2_str = ciphertext2.encode('hex')
    print 'plaintext: 0x%s\nciphertext: 0x%s' % (plaintext2_str, ciphertext2_str)

