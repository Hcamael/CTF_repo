import os
import hashlib
import socket
import threading
import socketserver
import struct
import time
from base64 import b64encode

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

class HashHandler(socketserver.BaseRequestHandler):

    welcome_message = """
Welcome, young wanna-be Cracker, to the Hashinator.

To prove your worthiness, you must display the power of your cracking skills.

The test is easy:
1. We send you a password from the rockyou list, hashed using multiple randomly chosen algorithms.
2. You crack the hash and send back the original password.

As you already know the dictionary and won't need any fancy password rules, {} seconds should be plenty, right?

Please wait while we generate your hash...
    """

    hashes = [md5, sha, blake, scrypt]
    timeout = 10
    total_rounds = 32

    def handle(self):
        self.request.sendall(self.welcome_message.format(self.timeout).encode())

        password = self.generate_password()
        salt = self.generate_salt(password)
        hash_rounds = self.generate_rounds()
        password_hash = self.calculate_hash(salt + password, hash_rounds)
        self.generate_delay()

        self.request.sendall("Challenge password hash: {}\n".format(b64encode(password_hash)).encode())
        self.request.sendall("Rounds used:\n".encode())
        test_rounds = []
        for r in hash_rounds:
            test_rounds.append(r)

        for r in hash_rounds:
            self.request.sendall("- {}\n".format(r.__name__).encode())
        self.request.sendall("Your time starts now!\n".encode())
        self.request.settimeout(self.timeout)
        try:
            response = self.request.recv(1024)
            if response.strip() == password:
                self.request.sendall("Congratulations! You are a true cracking master!\n".encode())
                self.request.sendall("Welcome to the club: {}\n".format(flag).encode())
                return
        except socket.timeout:
            pass
        self.request.sendall("Your cracking skills are bad, and you should feel bad!".encode())


    def generate_password(self):
        rand = struct.unpack("I", os.urandom(4))[0]
        lines = 14344391 # size of rockyou
        line = rand % lines
        password = ""
        f = open('rockyou.txt', 'rb')
        for i in range(line):
            password = f.readline()
        return password.strip()

    def generate_salt(self, p):
        msize = 128 # f-you hashcat :D
        salt_size = msize - len(p)
        return os.urandom(salt_size)

    def generate_rounds(self):
        rand = struct.unpack("Q", os.urandom(8))[0]
        rounds = []
        for i in range(self.total_rounds):
            rounds.append(self.hashes[rand % len(self.hashes)])
            rand = rand >> 2
        return rounds

    def calculate_hash(self, payload, hash_rounds):
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



class ThreadedTCPServer(socketserver.ThreadingMixIn, socketserver.TCPServer):
    allow_reuse_address = True

PORT = 1337
HOST = '0.0.0.0'
flag = ""

with open("flag.txt") as f:
    flag = f.read()

def main():
    server = ThreadedTCPServer((HOST, PORT), HashHandler)
    server_thread = threading.Thread(target=server.serve_forever)
    server_thread.start()
    server_thread.join()

if __name__ == "__main__":
    main()
