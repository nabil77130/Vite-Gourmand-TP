import urllib.request
import json
import os

token = "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"
url = "https://api.github.com/user/repos"
data = {
    "name": "julies-catering-tp",
    "private": True,
    "description": "Julie's Catering Food Delivery App"
}

headers = {
    "Authorization": f"token {token}",
    "Content-Type": "application/json",
    "Accept": "application/vnd.github.v3+json"
}

try:
    req = urllib.request.Request(url, data=json.dumps(data).encode('utf-8'), headers=headers)
    with urllib.request.urlopen(req) as response:
        print(f"Success: {response.status}")
        print(response.read().decode('utf-8'))
except urllib.error.HTTPError as e:
    print(f"Error: {e.code}")
    print(e.read().decode('utf-8'))
