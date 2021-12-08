from Crypto.Util.number import getPrime,long_to_bytes,bytes_to_long
import primefac
import time
from os import urandom
import hashlib
import sys
class Unbuffered(object):
   def __init__(self, stream):
       self.stream = stream
   def write(self, data):
       self.stream.write(data)
       self.stream.flush()
   def __getattr__(self, attr):
       return getattr(self.stream, attr)
import sys
sys.stdout = Unbuffered(sys.stdout)
def gen_args():
    p=getPrime(1024)
    q=getPrime(1024)
    n=p*q
    e=0x10001
    d=primefac.modinv(e,(p-1)*(q-1))%((p-1)*(q-1))
    return (p,q,e,n,d)
def proof():
    salt=urandom(4)
    print salt.encode("base64"),
    proof=raw_input("show me your work: ")
    if hashlib.md5(salt+proof.decode("base64")).hexdigest().startswith("0000"):
        print "checked success"
        return 1
    return 0

def run():
    if not proof():
        return
    m=int(open("/home/bibi/PycharmProjects/work/whctf/flag","r").read().encode("hex"),16)#flag{*}
    (p,q,e,n,d)=gen_args()
    c=pow(m,e,n)
    print "n:",hex(n)
    print "e:",hex(e)
    print "c:",hex(c)
    t=int(hex(m)[2:][0:8],16)
    u=pow(t,e,n)
    print "u:",hex(u)
    print "===="
    x=int(hex(m)[2:][0:8]+raw_input("x: "),16)
    print "===="
    y=int(raw_input("y: "),16)
    if (pow(x,e,n)==y and pow(y,d,n)==t):
        print "s:",hex(int(bin(p)[2:][0:568],2))
run()