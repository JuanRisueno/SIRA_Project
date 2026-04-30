import requests

BASE_URL = "http://localhost:8085/api/auth/token"
PROTECTED_URL = "http://localhost:8085/api/v1/clientes/2"

# 1. Login
resp = requests.post(BASE_URL, data={"username": "admin", "password": "admin1234"})
token = resp.json().get("access_token")
print(f"Token acquired. Status: {resp.status_code}")

# 2. Force ultima_actividad to be 40 minutes ago
import os
os.system('docker exec sira_db psql -U juanrisueno -d sira_db -c "UPDATE cliente SET ultima_actividad = NOW() - INTERVAL \'40 minutes\' WHERE cif = \'admin\';"')

# 3. Access endpoint
headers = {"Authorization": f"Bearer {token}"}
resp2 = requests.get(PROTECTED_URL, headers=headers)
print(f"Request status: {resp2.status_code}")
print(f"Request response: {resp2.text}")
