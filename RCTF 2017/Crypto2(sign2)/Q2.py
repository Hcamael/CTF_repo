HASH_ASN1 = {
'MD5': b('\x30\x20\x30\x0c\x06\x08\x2a\x86\x48\x86\xf7\x0d\x02\x05\x05\x00\x04\x10'),
'SHA-1': b('\x30\x21\x30\x09\x06\x05\x2b\x0e\x03\x02\x1a\x05\x00\x04\x14'),
'SHA-256': b('\x30\x31\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x01\x05\x00\x04\x20'),
'SHA-384': b('\x30\x41\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x02\x05\x00\x04\x30'),
'SHA-512': b('\x30\x51\x30\x0d\x06\x09\x60\x86\x48\x01\x65\x03\x04\x02\x03\x05\x00\x04\x40'),
}

def rVerify(message, signature, pub_key): 
    blocksize = rsa.common.byte_size(pub_key.n)
    encrypted = rsa.transform.bytes2int(signature)
    decrypted = rsa.core.decrypt_int(encrypted, pub_key.e, pub_key.n)
    clearsig = rsa.transform.int2bytes(decrypted, blocksize)
    
    if clearsig[0:2] != b('\x00\x01'):
        print ('How ugly your signature looks...More practice,OK?')
        return False
    
    try:
        sep_idx = clearsig.index(b('\x00'), 2)
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