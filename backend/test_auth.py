import requests

BASE_URL = "http://localhost:8085/api/auth/token"
PROTECTED_URL = "http://localhost:8085/api/v1/clientes/2" # Admin client id is 2 usually

def login(username, password):
    resp = requests.post(BASE_URL, data={"username": username, "password": password})
    if resp.status_code == 200:
        return resp.json()["access_token"]
    return None

token1 = login("admin", "admin1234")

# Make request with token1
headers1 = {"Authorization": f"Bearer {token1}"}
resp1 = requests.get(PROTECTED_URL, headers=headers1)
print(f"Request 1 with Token 1 status: {resp1.status_code}")

token2 = login("admin", "admin1234")

# Make request with token1 again
resp2 = requests.get(PROTECTED_URL, headers=headers1)
print(f"Request 2 with Token 1 status: {resp2.status_code}")
print(f"Request 2 response text: {resp2.text}")

# Make request with token2
headers2 = {"Authorization": f"Bearer {token2}"}
resp3 = requests.get(PROTECTED_URL, headers=headers2)
print(f"Request 3 with Token 2 status: {resp3.status_code}")

