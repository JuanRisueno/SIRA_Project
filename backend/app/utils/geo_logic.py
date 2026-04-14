import requests
from typing import Optional, Dict

# --- Mapeo de Provincias de España (Integridad Gating SIRA) ---
MAPA_PROVINCIAS = {
    "01": "Álava", "02": "Albacete", "03": "Alicante", "04": "Almería", "05": "Ávila",
    "06": "Badajoz", "07": "Islas Baleares", "08": "Barcelona", "09": "Burgos", "10": "Cáceres",
    "11": "Cádiz", "12": "Castellón", "13": "Ciudad Real", "14": "Córdoba", "15": "A Coruña",
    "16": "Cuenca", "17": "Gerona", "18": "Granada", "19": "Guadalajara", "20": "Guipúzcoa",
    "21": "Huelva", "22": "Huesca", "23": "Jaén", "24": "León", "25": "Lérida",
    "26": "La Rioja", "27": "Lugo", "28": "Madrid", "29": "Málaga", "30": "Murcia",
    "31": "Navarra", "32": "Orense", "33": "Asturias", "34": "Palencia", "35": "Las Palmas",
    "36": "Pontevedra", "37": "Salamanca", "38": "Santa Cruz de Tenerife", "39": "Cantabria", "40": "Segovia",
    "41": "Sevilla", "42": "Soria", "43": "Tarragona", "44": "Teruel", "45": "Toledo",
    "46": "Valencia", "47": "Valladolid", "48": "Vizcaya", "49": "Zamora", "50": "Zaragoza",
    "51": "Ceuta", "52": "Melilla"
}

def obtener_provincia_por_cp(cp: str, backup_state: Optional[str] = None) -> str:
    """
    Determina la provincia basándose en los dos primeros dígitos del CP.
    Si no se encuentra en el mapa local, devuelve el backup o 'Desconocida'.
    """
    prefijo = cp[:2]
    return MAPA_PROVINCIAS.get(prefijo, backup_state if backup_state else "Desconocida")

def consultar_zippopotam(cp: str) -> Optional[Dict]:
    """
    Consulta la API externa de Zippopotam para obtener datos geográficos de un CP.
    """
    try:
        url = f"http://api.zippopotam.us/es/{cp}"
        response = requests.get(url, timeout=5)
        if response.status_code == 200:
            data = response.json()
            if "places" in data and len(data["places"]) > 0:
                place = data["places"][0]
                return {
                    "codigo_postal": cp,
                    "municipio": place["place name"],
                    "provincia": obtener_provincia_por_cp(cp, place["state"]),
                    "origen": "externo"
                }
    except Exception:
        pass
    return None
