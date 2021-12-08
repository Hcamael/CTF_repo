#!/bin/bash
cd `dirname $0`
exec ./qemu-x86_64 -B $((RANDOM * 4096)) ./app
