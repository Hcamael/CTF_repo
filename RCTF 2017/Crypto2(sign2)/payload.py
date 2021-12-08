#!/usr/bin/env python
# -*- coding=utf-8 -*-

from libnum import nroot
from libnum import n2s
from libnum import s2n
import hashlib, os, rsa, binascii

def get_bit(n, b):
    return ((1 << b) & n) >> b

def set_bit(n, b, x):
    if x == 0: return ~(1 << b) & n
    if x == 1: return (1 << b) | n

def verify2(clearsig):
    if clearsig[0:2] != '\x00\x01':
        return False
    try:
        sep_idx = clearsig.index('\x00', 2)
    except ValueError:
        return False
    method_hash = clearsig[sep_idx+1:]
    for (x, y) in HASH_ASN1.items():
        if method_hash.startswith(y):
            if len(method_hash[len(y):]) == x:
                return True
    return False

def verify1(clearsig, message_hash):
    if clearsig[0:2] != '\x00\x01':
        return False
    try:
        sep_idx = clearsig.index('\x00', 2)
    except ValueError:
        return False
    method_hash = clearsig[sep_idx+1:]
    asn1code = "\x30\x51\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x03\x05\x00\x04\x40"
    if not method_hash.startswith(asn1code):
        return False
    if method_hash[len(asn1code):] == message_hash:
        return True
    else:
        return False

def to_bytes(n, endianess='big'):
    h = '%x' % n
    s = ('0'*(len(h) % 2) + h).decode('hex')
    return s

def from_bytes(b):
    return int(b.encode('hex'), 16)

def is_root(x):
    if pow(nroot(x,3),3) == x:
        return True
    return False

HASH_ASN1 = {
'MD5': '\x30\x20\x30\x0c\x06\x08\x2a\x86\x48\x86\xf7\x0d\x02\x05\x05\x00\x04\x10',
'SHA-1': '\x30\x21\x30\x09\x06\x05\x2b\x0e\x03\x02\x1a\x05\x00\x04\x14',
'SHA-256': '\x30\x31\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x01\x05\x00\x04\x20',
'SHA-384': '\x30\x41\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x02\x05\x00\x04\x30',
'SHA-512': '\x30\x51\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x03\x05\x00\x04\x40',
}

def rVerify(message, signature):
    n = 99103278939331174405096046174826505890630650433457474512679503637107184969587849584143967014347754889469667043136895601008192434248630928076345525071962146097925698057299368797800220354529704116063015906135093873544219941584758892847007593809714204471472620455658479996846811490190888414921319427626842981521L
    blocksize = rsa.common.byte_size(n)
    encrypted = rsa.transform.bytes2int(signature)
    decrypted = pow(encrypted, 3, n)
    clearsig = rsa.transform.int2bytes(decrypted, blocksize)
    print clearsig.encode('hex')
    if clearsig[0:2] != '\x00\x01':
        print ('How ugly your signature looks...More practice,OK?')
        return False
    
    try:
        sep_idx = clearsig.index('\x00', 2)
    except ValueError:
        print ('RU Kidding me?')
        return False           
    
    (method_name, signature_hash) = rfind_method_hash(clearsig[sep_idx+1:])
    message_hash = rsa.pkcs1._hash(message, method_name)
    
      
    if message_hash != signature_hash:
          print ('wanna cheat me,ah?')
          return False
    return True

def rfind_method_hash(method_hash): 
    for (hashname, asn1code) in HASH_ASN1.items():
        if not method_hash.startswith(asn1code):
            continue
        return (hashname, method_hash[len(asn1code):])
    print ('How ugly your signature looks...More practice,OK?')
    exit(0)

# if __name__ == '__main__':
#     HASH_ASN1 = {
# 16: '\x30\x20\x30\x0c\x06\x08\x2a\x86\x48\x86\xf7\x0d\x02\x05\x05\x00\x04\x10',
# 20: '\x30\x21\x30\x09\x06\x05\x2b\x0e\x03\x02\x1a\x05\x00\x04\x14',
# 32: '\x30\x31\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x01\x05\x00\x04\x20',
# 48: '\x30\x41\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x02\x05\x00\x04\x30',
# 64: '\x30\x51\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x03\x05\x00\x04\x40',
# }
#     prefix = "\x00\x01" + 42 * '\x01' + "\x00" + HASH_ASN1[64]
#     m = "sdlkfjskdf&110066&221p1pps1"
    # for x in xrange(1, 1000000):
    #     tmp_m = m + str(x)
    #     tmp_sha = hashlib.sha512(tmp_m).digest()
    #     tmp = s2n(prefix + tmp_sha)
    #     assert verify1(prefix+tmp_sha, tmp_sha)
    #     r = nroot(tmp, 3)
    #     r = r**3
    #     if r == tmp:
    #         print r
    #         break
    #     if tmp_sha in n2s(r):
    #         print n2s(r).encode('hex')
    # cz = 19595533242629369747791401605606558418088927130487463844933662202465281465266200982457647235235528838735010358900495684567911298014908298340170885513171109743249504533143507682501017145381579984990109696L
    # sha = hashlib.sha512(m).digest()
    # i = s2n(prefix + sha)
    # while True:
    #     if is_root(i):
    #         print i
    #         break
    #     i += cz
    # message = "0YMrY4ZuMYU2YhoTZTSZROgC0HTQNI6M"
    # message_hash = hashlib.md5(message).digest()
    # ASN1_blob = rsa.pkcs1.HASH_ASN1['MD5']
    # suffix = b'\x00' + ASN1_blob + message_hash

    # sig_suffix = 1
    # for b in range(len(suffix)*8):
    #     if get_bit(sig_suffix ** 3, b) != get_bit(s2n(suffix), b):
    #         sig_suffix = set_bit(sig_suffix, b, 1)
    
    # while True:
    #     prefix = b'\x00\x01' + os.urandom(1024/8 - 2)
    #     sig_prefix = n2s(nroot(s2n(prefix),3))[:-len(suffix)] + b'\x00' * len(suffix)
    #     sig = sig_prefix[:-len(suffix)] + n2s(sig_suffix)
    #     if b'\x00' not in n2s(s2n(sig) ** 3)[:-len(suffix)]: break

    # exploit = binascii.b2a_hex(sig)
    # print exploit
    # mmmm =  "0001367927199750dbc1feaea40f044d426322390e3a8ae88957ceb94bdd8602fcfec8a3d0a7c248e1ea6e9f".decode('hex')
    # print len(mmmm)
    # print verify1(mmmm, message)
    # print rVerify(sig, message)
message = "0YMrY4ZuMYU2YhoTZTSZROgC0HTQNI6M".encode("ASCII")
message_hash = hashlib.sha512(message).digest()

ASN1_blob = rsa.pkcs1.HASH_ASN1['SHA-512']
suffix = b'\x00' + ASN1_blob + message_hash

sig_suffix = 1
for b in range(len(suffix)*8):
    if get_bit(sig_suffix ** 3, b) != get_bit(from_bytes(suffix), b):
        sig_suffix = set_bit(sig_suffix, b, 1)
        

while True:
    prefix = b'\x00\x01' + os.urandom(1024/8 - 2)
    sig_prefix = to_bytes(nroot(from_bytes(prefix),3))[:-len(suffix)] + b'\x00' * len(suffix)
    sig = sig_prefix[:-len(suffix)] + to_bytes(sig_suffix)
    if b'\x00' not in to_bytes(from_bytes(sig) ** 3)[:-len(suffix)]: break

exploit = binascii.b2a_hex(sig)
print "message : 0YMrY4ZuMYU2YhoTZTSZROgC0HTQNI6M"
print "hash: %s" %message_hash.encode('hex')
print "exploit : " ,exploit
print rVerify(message, sig)
