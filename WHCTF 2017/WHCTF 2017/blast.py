#!/usr/bin/env python2
# -*- coding=utf-8 -*-

from libnum import s2n, n2s

n = 0xa56204500cf195ba39e1e18e7fbb16f1dee8441c16ca6b1462397b27bce445540fdba8faaa3066bee8f8e61e9b1058f8cb5090843523403ec055f5432a167b2f8d63067ddba9c846472c27ee0e6ca37cf23201b511897b98a102233a0c2f57aa0c656db5626c5e42dc8e14f6bbcf089699c95bf54b35652ba2421086a5d50d2da6e0971561580a5eed0c5523c7b1251d400864c0424fee0d69697d4ae245574e5798e14d767b3f1183d40dad160f53bce4fc29b00e0197b4ff06105352ea535a2c7f38832cef7a819c7eb258c5e5d2d162447693133d2cb05fdb1928d51d1ad6d111d21c4fe270cf711ce3c61f66345b410f85d0d9cf0a5ecad7482c640f715fL
e = 0x10001
u = 0x2c46082616b0027ee0d8764077b41ac865dc7c2d8eb531f7f5a3b5c91d47f7462fd2eb1b09f570bf7c04c3b01f8cd3e1411b6213eb1560242867abbf2989920c3cb139b3070692055506f1fa7248b1c8293de0231717aa030cb4eea425e929c460a269c386d6237b34ecdd8571a9863aa09164c0326b29c535911d198a0a19f9bb58dd12d2d962e695d1a1ed49eb358226c1a29e2cb995062e06f10cbf83a9148e392cdeaa8c6d49a3c1ee6ff95c2b73d83c26d26c22d2bff5f6489a8

charset = [' ', '!', '"', '#', '$', '%', '&', "'", '(', ')', '*', '+', ',', '-', '.', '/', '0', '1',
          '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?', '@', 'A', 'B', 'C', 'D', 'E',
          'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y',
          'Z', '[', '\\', ']', '^', '_', '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
          'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '{', '|', '}']
# charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"

for x1 in charset[33:]:
    for x2 in charset:
        for x3 in charset:
            for x4 in charset:
                tmp = x1+x2+x3+x4
                print tmp+"\r",
                if pow(s2n(tmp),e,n) == u:
                    print tmp
                    break
