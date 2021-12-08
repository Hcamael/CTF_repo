binary_list1 = [
0x602018,
0x602020,
0x602028,
0x602030,
0x602038,
0x602038,
0x602040,
0x602048,
0x602050,
0x602058,
0x602060,
0x602068,
0x602068,
0x602070,
0x602078,
0x602080,
0x602088,
0x602090,
0x602098,
0x6020A0,
0x6020A8,
0x6020B0,
0x23330200,
0x400000,
0x6020fc

]
binary_list = [
0x602018,
0x602028,
0x602030,
0x602050,
0x602070,
0x6020B0,
0x602098,
0x602078,
0x602088,
0x602090
]

libc_list = [
0x3C67A8,
0x3C4B10,
0x3C4B08,
0x3C67B0,
0x3C67A0,
0x3C5520,
0x3C5540,
0x3C5620,
0x3C5C40,
0x3C5708,
]


stack_list = [
0xd4,
20
]


b_list=[]

l_list=[]

s_list=[]

for t in binary_list1:
	b_list.append(hex(t-0x400000)[2:])

for t in binary_list:
	for i in range(1,8):
		b_list.append(hex(t-i-0x400000)[2:])


while 1:
	if len(b_list)<100:
		b_list.append('0')
	else:
		break

for t in libc_list:
	for i in range(8):
		l_list.append(hex(t-i)[2:])

while 1:
	if len(l_list)<100:
		l_list.append('0')
	else:
		break

for t in stack_list:
	for i in range(8):
		s_list.append(hex(t-i)[2:])

while 1:
	if len(s_list)<100:
		s_list.append('0')
	else:
		break

assert len(b_list)<=100,"binary_list too loooong"
assert len(l_list)<=100,"libc_list too loooong"
assert len(s_list)<=100,"stack_list too loooong"

data=''

for k in b_list:
	data+=k+'\n'
for k in l_list:
	data+=k+'\n'
for k in s_list:
	data+=k+'\n'

open('blacklist','w').write(data)