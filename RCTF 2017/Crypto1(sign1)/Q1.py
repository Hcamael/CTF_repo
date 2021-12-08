def rVerify(message, signature, pub_key): 
    blocksize = rsa.common.byte_size(pub_key.n)
    encrypted = rsa.transform.bytes2int(signature)
    decrypted = rsa.core.decrypt_int(encrypted, pub_key.e, pub_key.n)
    clearsig = rsa.transform.int2bytes(decrypted, blocksize)
    try:
        sep_idx = clearsig.index(b('\x00'), 2)
    except ValueError:
        print ('How ugly your signature looks...More practice,OK?')
        return False 
        
    signature = clearsig[sep_idx+1:]
    
    # Compare the real hash to the hash in the signature
    if message != signature:
        print ('wanna cheat me,ah?')
        return False
    return True