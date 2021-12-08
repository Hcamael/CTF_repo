from pwn import *
from hashlib import sha256
import itertools
import string
import base64
import re

def SHA256(s):
	return sha256(s).hexdigest()

def valid_str():
	strings = string.ascii_letters+string.digits
	for a1 in strings:
		for a2 in strings:
			for a3 in strings:
				for a4 in strings:
					yield a1+a2+a3+a4

def run_xxxx(pfs, hashv):
	s = valid_str()
	for x in s:
		tmph = SHA256(x+pfs)
		# print tmph
		if tmph == hashv:
			return x
	return False

def cal_send(fake_msg):
	iv = "2jpmLoSsOlQrqyqE"
	msg = "Welcome!!"
	fake_iv = ""
	for x in xrange(len(fake_msg)):
		tmp = chr(ord(iv[x])^ord(msg[x])^ord(fake_msg[x]))
		fake_iv += tmp
	fake_iv += iv[len(fake_iv):]
	assert len(fake_iv) == len(iv)
	return fake_iv

def main():
	p = remote("52.193.157.19", 9999)
	proof = p.readline()
	pfs, hashv = re.findall("XXXX\+([a-zA-Z0-9]+)\) == ([0-9a-z]+)",proof)[0]
	xxxx = run_xxxx(pfs, hashv)
	if not xxxx:
		print "False"
		return
	p.sendline(xxxx)
	p.readuntil("Done!\n")
	welcom = p.readline().strip().decode("base64")
	f_iv = cal_send("get-flag")
	p.sendline(base64.b64encode(f_iv + welcom[16:]))
	p.interactive()


if __name__ == '__main__':
	main()