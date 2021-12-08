from ubuntu:20.04

run apt-get -qq update && apt-get install -qq --no-install-recommends xinetd socat python3 qemu-user-static

copy service.py /service.py
copy shuffl /
copy runner-x86_64 /
copy runner-aarch64 /
copy runner-riscv64 /

copy service.conf /
copy banner_fail /
copy wrapper /
run chmod +x /wrapper

expose 9090


cmd chmod go-rwx /proc && xinetd -filelog /dev/stderr -dontfork -f /service.conf
# ^^ Assuming that ps would be too revealing.
#    AFAIK, this also disables the built-in printf(%n) protection. Alternatively:
#cmd ["/usr/sbin/xinetd", "-filelog", "/dev/stderr", "-dontfork", "-f", "/service.conf"]
