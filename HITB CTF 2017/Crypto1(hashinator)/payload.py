#! /usr/bin/env python3
# -*- coding: utf-8 -*-

import re
import os
import hashlib
import struct
import time
from base64 import b64encode
from pwn import *

context.log_level = "debug"

def md5(bytestring):
    return hashlib.md5(bytestring).digest()

def sha(bytestring):
    return hashlib.sha1(bytestring).digest()

def blake(bytestring):
    return hashlib.blake2b(bytestring).digest()

def scrypt(bytestring):
    l = int(len(bytestring) / 2)
    salt = bytestring[:l]
    p = bytestring[l:]
    return hashlib.scrypt(p, salt=salt, n=2**16, r=8, p=1, maxmem=67111936)

def xor(s1, s2):
    return b''.join([bytes([s1[i] ^ s2[i % len(s2)]]) for i in range(len(s1))])

def generate_password():
	return os.urandom(64)

def generate_salt(p):
    msize = 128 # f-you hashcat :D
    salt_size = msize - len(p)
    return os.urandom(salt_size)

def generate_rounds():
    hashes = [md5, sha, blake, scrypt]
    rand = struct.unpack("Q", os.urandom(8))[0]
    rounds = []
    for i in range(32):
        rounds.append(hashes[rand % len(hashes)])
        rand = rand >> 2
    return rounds

def calculate_hash(payload, hash_rounds):
    interim_salt = payload[:64]
    interim_hash = payload[64:]
    for i in range(len(hash_rounds)):
        interim_salt = xor(interim_salt, hash_rounds[-1-i](interim_hash))
        interim_hash = xor(interim_hash, hash_rounds[i](interim_salt))
    final_hash = interim_salt + interim_hash
    return final_hash

def generate_delay(self):
    rand = struct.unpack("I", os.urandom(4))[0]
    time.sleep(rand / 1000000000.0)

def test():
	password = generate_password()
	print("password: %s"%password)
	salt = generate_salt(password)
	print("salt: %s"%salt)
	hash_rounds = generate_rounds()
	password_hash = calculate_hash(salt + password, hash_rounds)
	r_pass = restore(password_hash, hash_rounds)
	print(r_pass == password)	

def restore(password_hash, hash_rounds):
	salt = password_hash[:64]
	ihash = password_hash[64:]
	for i in range(32):
		ihash = xor(ihash, hash_rounds[-1-i](salt))
		salt = xor(salt, hash_rounds[i](ihash))
	print("restore data: %s"%(salt+ihash))
	return ihash

def attack():
	p = remote("47.88.216.38", 20013)
	p.readuntil(" b'")
	base = p.readuntil("'")[:-1]
	base = base64.b64decode(base)
	assert len(base) == 128
	salt = base[:64]
	ihash = base[64:]
	p.readuntil("used:\n")
	hash_round = []
	for x in range(32):
		tmp = p.readline()
		if b"scrypt" in tmp:
			hash_round.append(scrypt)
		elif b"sha" in tmp:
			hash_round.append(sha)
		elif b"md5" in tmp:
			hash_round.append(md5)
		elif b"blake" in tmp:
			hash_round.append(blake)

	assert len(hash_round) == 32

	ihash = restore(base, hash_round)
	ihash = re.findall(b"([a-zA-Z0-9]+)", ihash)[-1]	

	p.sendline(ihash)
	p.interactive()

if __name__ == '__main__':
	# test()
	attack()