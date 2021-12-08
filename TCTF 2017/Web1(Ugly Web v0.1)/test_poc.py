import requests

url = "http://x.x.x.x:x/test.php"
url2 = url + "?a=1"

s = requests.session()
r = s.get(url2)
print r.content
r2 = s.get(url)
print r2.content

