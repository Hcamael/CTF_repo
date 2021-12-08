from Crypto.Util.number import getPrime,long_to_bytes,bytes_to_long
from libnum import invmod
import time
from os import urandom
import hashlib
import sys

def gen_args():
    p=getPrime(1024)
    q=getPrime(1024)
    n=p*q
    e=0x10001
    d=invmod(e,(p-1)*(q-1))%((p-1)*(q-1))
    return (p,q,e,n,d)

def run():
    m=int(open("./flag","r").read().encode("hex"),16)#flag{*}
    (p,q,e,n,d)=gen_args()
    c=pow(m,e,n)
    print "n:",hex(n)
    print "e:",hex(e)
    print "c:",hex(c)
    print "m:",hex(m)
    print "d:",hex(d)
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