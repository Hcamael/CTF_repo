#!/usr/bin/env python2
# -*- coding=utf-8 -*-

import base64
from pwn import *
from libnum import s2n

# context.log_level = "debug"
p = remote("118.31.18.75", 20013)
iv = p.readline()
p.recvuntil("work:")
iv = base64.b64decode(iv)
for x in xrange(100000000000):
    if hashlib.md5(iv+str(x)).hexdigest().startswith("0000"):
        p.sendline(base64.b64encode(str(x)))
        break

t = s2n("flag")
e = 0x10001
p.recvuntil("n: ")
n = p.readline().strip()
n = int(n[2:-1], 16)
print "n: "+ hex(n)

print p.recvuntil("x: ")

# x = n
# while True:
# 	if hex(x)[2:10] == '666c6167':
# 		print x
# 		break
# 	x += n
y = pow(t, e, n)
p.sendline("")
p.sendline(hex(y)[2:])

p.interactive()
