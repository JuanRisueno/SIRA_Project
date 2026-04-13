"""
===============================================================================================
            Router: Datos Maestros (Clientes, Localidades, Parcelas e Invernaderos)
===============================================================================================

Propósito:
Este archivo define los "Endpoints" (URLs) de la API relacionados con la
estructura base del sistema.

Aquí es donde FastAPI recibe las peticiones HTTP (GET, POST, PUT, DELETE), valida los datos
usando 'schemas.py', llama a la lógica de base de datos en 'crud.py',
y devuelve la respuesta al usuario.
"""

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy import func, or_, String
from sqlalchemy.orm import Session
from typing import List, Optional
import requests

from .. import crud, models, schemas, auth
from ..database import SessionLocal

# --- Configuración del Router ---
router = APIRouter(
    prefix="/api/v1",
    tags=["Datos Maestros (Configuración)"]
)

# --- Dependencia para obtener la DB ---
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

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


# =============================================================================
# 1. GESTIÓN DE CLIENTES
# =============================================================================

# 1. POST (Crear) - PÚBLICO (Para registrarse) / PRIVADO (Para Admin añada usuarios)
@router.post("/clientes/", response_model=schemas.Cliente, status_code=status.HTTP_201_CREATED, summary="Crear Cliente")
def crear_cliente(
    cliente: schemas.ClienteCreate, 
    db: Session = Depends(get_db),
    current_user: Optional[models.Cliente] = Depends(auth.get_current_user_optional) # Usuario opcional
):
    """
    Registra una nueva empresa/agricultor en SIRA.
    - Si se especifica un rol distinto de 'cliente', requiere ser Admin/Root.
    """
    # 1. Seguridad: Si intentan poner un rol que no sea 'cliente', deben ser admins.
    if cliente.rol != "cliente":
        if not current_user or current_user.rol not in ["admin", "root"]:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN, 
                detail="No tienes permisos para asignar roles especiales. Contacta con un administrador."
            )
        
        # Restricción: El rol 'root' es único y solo se crea mediante semilla de base de datos.
        if cliente.rol == "root":
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN, 
                detail="No se pueden crear más usuarios de tipo 'root'. El sistema ya tiene uno."
            )

    # 2. Verificar si ya existe
    db_cliente = crud.get_cliente_by_cif(db, cif=cliente.cif)
    if db_cliente:
        raise HTTPException(status_code=400, detail="El cliente con este CIF ya está registrado.")
    
    # 3. Crear usando el rol especificado
    return crud.create_cliente(db=db, cliente=cliente, rol=cliente.rol)

# 2. GET (Listar Todos) - PROTEGIDO POR JWT (SOLO ADMIN)
@router.get("/clientes/", response_model=List[schemas.Cliente], summary="Listar Clientes")
def listar_clientes(
    skip: int = 0, 
    limit: int = 1000, 
    q: Optional[str] = None,
    db: Session = Depends(get_db),
    # current_user: schemas.Cliente = Depends(auth.require_admin) # PAUSADO PARA DESARROLLO
):
    """
    Devuelve un listado paginado de clientes. Soporta búsqueda por q (nombre, contacto, cif).
    - El ROOT ve a todos (Admins y Clientes).
    - El ADMIN solo ve a usuarios con rol 'cliente'.
    """
    query = db.query(models.Cliente)
    
    # Restricción: Si el que llama es ADMIN, solo le mostramos clientes normales.
    # [PAUSADO PARA DESARROLLO]
    # if current_user.rol == "admin":
    #     query = query.filter(models.Cliente.rol == "cliente")
        
    # Filtrado por búsqueda (Insensible a tildes y mayúsculas)
    # Requiere que la extensión 'unaccent' esté cargada en PostgreSQL.
    if q:
        search_filter = f"%{q}%"
        query = query.filter(
            or_(
                func.unaccent(models.Cliente.nombre_empresa.cast(String)).ilike(func.unaccent(search_filter)),
                func.unaccent(models.Cliente.persona_contacto.cast(String)).ilike(func.unaccent(search_filter)),
                models.Cliente.cif.ilike(search_filter)
            )
        )
        
    return query.order_by(models.Cliente.cliente_id).offset(skip).limit(limit).all()

# 3. GET (Buscar por CIF) - PROTEGIDO POR JWT
@router.get("/clientes/buscar/{cif}", response_model=schemas.Cliente, summary="Buscar Cliente por CIF")
def buscar_cliente_por_cif(
    cif: str, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # CANDADO
):
    db_cliente = crud.get_cliente_by_cif(db, cif=cif)
    if db_cliente is None:
        raise HTTPException(status_code=404, detail="No existe ningún cliente con este CIF")
    return db_cliente

# 4. GET (Leer por ID) - PROTEGIDO POR JWT
@router.get("/clientes/{cliente_id}", response_model=schemas.Cliente, summary="Leer Cliente")
def leer_cliente(cliente_id: int, db: Session = Depends(get_db), current_user: schemas.Cliente = Depends(auth.require_admin)):
    db_cliente = crud.get_cliente(db, cliente_id=cliente_id)
    if db_cliente is None:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    return db_cliente

# 5. PATCH (Activar/Desactivar) - PROTEGIDO (Sólo Admin/Root)
@router.patch("/clientes/{cliente_id}/status", response_model=schemas.Cliente, summary="Cambiar Estado del Cliente (Ocultar/Activar)")
def actualizar_estado_cliente(
    cliente_id: int, 
    activa: bool, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.require_admin)
):
    """
    Cambia la visibilidad de un cliente. 
    - Un ADMIN solo puede cambiar estados de CLIENTES.
    - El ROOT puede cambiar el estado de cualquier usuario excepto el suyo propio.
    """
    db_cliente = crud.get_cliente(db, cliente_id=cliente_id)
    if not db_cliente:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    
    # Restricción de Admin: Solo puede tocar clientes
    if current_user.rol == "admin" and db_cliente.rol != "cliente":
        raise HTTPException(status_code=403, detail="Un administrador no puede cambiar el estado de otros administradores o del sistema root.")
    
    return crud.set_cliente_status(db, cliente_id=cliente_id, activa=activa)

# 5. PUT (Actualizar) - PROTEGIDO POR JWT
@router.put("/clientes/{cliente_id}", response_model=schemas.Cliente, summary="Actualizar Cliente")
def actualizar_cliente(
    cliente_id: int, 
    cliente_update: schemas.ClienteUpdate, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # CANDADO
):
    try:
        db_cliente = crud.update_cliente(db=db, cliente_id=cliente_id, cliente_update=cliente_update)
        if db_cliente is None:
            raise HTTPException(status_code=404, detail="Cliente no encontrado")
        return db_cliente
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))

# 6. DELETE (Borrar) - PROTEGIDO POR JWT
@router.delete("/clientes/{cliente_id}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Cliente (Lógico)")
def borrar_cliente(
    cliente_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # CANDADO
):
    exito = crud.delete_cliente(db, cliente_id=cliente_id)
    if not exito:
        raise HTTPException(status_code=404, detail="Cliente no encontrado o ya borrado")
    return None


# =============================================================================
# 2. GESTIÓN DE LOCALIDADES (PÚBLICO)
# =============================================================================
# Las localidades se dejan públicas para facilitar desplegables en el Frontend

@router.post("/localidades/", response_model=schemas.Localidad, status_code=status.HTTP_201_CREATED, summary="Crear Localidad")
def crear_localidad(localidad: schemas.LocalidadCreate, db: Session = Depends(get_db)):
    db_localidad = crud.get_localidad(db, codigo_postal=localidad.codigo_postal)
    if db_localidad:
        raise HTTPException(status_code=400, detail="Este Código Postal ya existe en la base de datos.")
    return crud.create_localidad(db=db, localidad=localidad)

@router.get("/localidades/{codigo_postal}", response_model=schemas.Localidad, summary="Leer Localidad")
def leer_localidad(codigo_postal: str, db: Session = Depends(get_db)):
    db_localidad = crud.get_localidad(db, codigo_postal=codigo_postal)
    if db_localidad is None:
        raise HTTPException(status_code=404, detail="Localidad no encontrada")
    return db_localidad

@router.put("/localidades/{codigo_postal}", response_model=schemas.Localidad, summary="Actualizar Localidad")
def actualizar_localidad(codigo_postal: str, localidad_update: schemas.LocalidadUpdate, db: Session = Depends(get_db)):
    db_localidad = crud.update_localidad(db=db, codigo_postal=codigo_postal, localidad_update=localidad_update)
    if db_localidad is None:
        raise HTTPException(status_code=404, detail="Localidad no encontrada")
    return db_localidad

@router.get("/localidades/", response_model=List[schemas.Localidad], summary="Listar Localidades")
def listar_localidades(
    skip: int = 0, 
    limit: int = 1000, 
    q: Optional[str] = None,
    db: Session = Depends(get_db)
):
    return crud.get_localidades(db, skip=skip, limit=limit, q=q)

@router.delete("/localidades/{codigo_postal}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Localidad")
def borrar_localidad(
    codigo_postal: str, 
    db: Session = Depends(get_db),
    # current_user: schemas.Cliente = Depends(auth.get_current_user) # PAUSADO PARA DESARROLLO
):
    if not crud.get_localidad(db, codigo_postal):
        raise HTTPException(status_code=404, detail="Localidad no encontrada")
    try:
        crud.delete_localidad(db, codigo_postal=codigo_postal)
    except Exception as e:
        raise HTTPException(
            status_code=400, 
            detail="No se puede borrar esta localidad porque existen parcelas registradas en ella."
        )
    return None

# --- NUEVO: Validación Inteligente de CP ---
@router.get("/geo/check-cp/{cp}", summary="Validar CP (Local + Externo)")
def validar_cp_inteligente(cp: str, db: Session = Depends(get_db)):
    """
    Busca un CP en la base de datos. Si no existe, consulta Zippopotam.us.
    Incluye una capa de corrección ortográfica para nombres comunes.
    """
    if len(cp) != 5 or not cp.isdigit():
        raise HTTPException(status_code=400, detail="El código postal debe tener 5 dígitos.")

    def obtener_provincia_por_cp(cp: str, backup_state: str = None) -> str:
        # Los dos primeros dígitos del CP indican la provincia en España
        prefijo = cp[:2]
        return MAPA_PROVINCIAS.get(prefijo, backup_state if backup_state else "Desconocida")

    # 1. Buscar en BBDD Local
    db_loc = crud.get_localidad(db, codigo_postal=cp)
    if db_loc:
        return {
            "codigo_postal": db_loc.codigo_postal,
            "municipio": db_loc.municipio,
            "provincia": obtener_provincia_por_cp(cp, db_loc.provincia),
            "origen": "local"
        }

    # 2. Si no está, buscar en API Externa (Zippopotam.us)
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

    raise HTTPException(status_code=404, detail="No se han encontrado datos para este Código Postal.")

@router.get("/geo/search-municipio/{nombre}", summary="Buscar CPs por Municipio (Local)")
def buscar_municipio_inteligente(nombre: str, db: Session = Depends(get_db)):
    """
    Busca municipios que coincidan con 'nombre' y devuelve sus CPs y Provincias.
    Exclusivo para la base de datos SIRA para garantizar consistencia.
    """
    if len(nombre) < 3:
        raise HTTPException(status_code=400, detail="Escriba al menos 3 caracteres para buscar.")

    # Buscar coincidencias parciales (usando crud.get_localidades que ya tiene ilike y unaccent)
    db_locs = crud.get_localidades(db, q=nombre, limit=20)
    
    if not db_locs:
        raise HTTPException(status_code=404, detail=f"No se han encontrado CPs para '{nombre}'. Pruebe buscando por Código Postal.")

    def obtener_provincia_por_cp(cp: str, backup_state: str = None) -> str:
        prefijo = cp[:2]
        return MAPA_PROVINCIAS.get(prefijo, backup_state if backup_state else "Desconocida")

    resultados = []
    for loc in db_locs:
        resultados.append({
            "codigo_postal": loc.codigo_postal,
            "municipio": loc.municipio,
            "provincia": obtener_provincia_por_cp(loc.codigo_postal, loc.provincia)
        })

    return resultados

# =============================================================================
# 3. GESTIÓN DE PARCELAS
# =============================================================================

@router.post("/parcelas/", response_model=schemas.Parcela, status_code=status.HTTP_201_CREATED, summary="Crear Parcela")
def crear_parcela(
    parcela: schemas.ParcelaCreate, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    if not crud.get_cliente(db, cliente_id=parcela.cliente_id):
        raise HTTPException(status_code=404, detail=f"Cliente {parcela.cliente_id} no encontrado.")
    if not crud.get_localidad(db, codigo_postal=parcela.codigo_postal):
        raise HTTPException(status_code=404, detail=f"Código Postal {parcela.codigo_postal} no registrado.")
    try:
        return crud.create_parcela(db=db, parcela=parcela)
    except Exception as e:
        raise HTTPException(status_code=400, detail="Error al crear parcela. Verifique Ref. Catastral.")

@router.get("/parcelas/", response_model=List[schemas.Parcela], summary="Listar Parcelas")
def listar_parcelas(
    skip: int = 0, 
    limit: int = 100, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    return crud.get_parcelas(db, skip=skip, limit=limit)

@router.get("/parcelas/cliente/{cliente_id}", response_model=List[schemas.Parcela], summary="Listar Parcelas de un Cliente")
def listar_parcelas_cliente(
    cliente_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    if not crud.get_cliente(db, cliente_id):
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    return crud.get_parcelas_por_cliente(db, cliente_id=cliente_id)

@router.get("/parcelas/localidad/{codigo_postal}", response_model=List[schemas.Parcela], summary="Listar Parcelas de una Localidad")
def listar_parcelas_localidad(
    codigo_postal: str, 
    db: Session = Depends(get_db),
    # current_user: schemas.Cliente = Depends(auth.get_current_user) # PAUSADO PARA DESARROLLO
):
    if not crud.get_localidad(db, codigo_postal):
        raise HTTPException(status_code=404, detail="Localidad no encontrada")
    return crud.get_parcelas_por_localidad(db, codigo_postal=codigo_postal)

@router.get("/parcelas/{parcela_id}", response_model=schemas.Parcela, summary="Leer Parcela")
def read_parcela(
    parcela_id: int, 
    db: Session = Depends(get_db),
    # current_user: schemas.Cliente = Depends(auth.get_current_user) # PAUSADO PARA DESARROLLO
):
    db_parcela = crud.get_parcela(db, parcela_id=parcela_id)
    if db_parcela is None:
        raise HTTPException(status_code=404, detail="Parcela no encontrada")
    return db_parcela

@router.put("/parcelas/{parcela_id}", response_model=schemas.Parcela, summary="Actualizar Parcela")
def actualizar_parcela(
    parcela_id: int, 
    parcela_update: schemas.ParcelaUpdate, 
    db: Session = Depends(get_db),
    # current_user: schemas.Cliente = Depends(auth.get_current_user) # PAUSADO PARA DESARROLLO
):
    try:
        db_parcela = crud.update_parcela(db=db, parcela_id=parcela_id, parcela_update=parcela_update)
        if db_parcela is None:
            raise HTTPException(status_code=404, detail="Parcela no encontrada")
        return db_parcela
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))

@router.delete("/parcelas/{parcela_id}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Parcela (Lógico)")
def borrar_parcela(
    parcela_id: int, 
    db: Session = Depends(get_db),
    # current_user: schemas.Cliente = Depends(auth.get_current_user) # PAUSADO PARA DESARROLLO
):
    if not crud.delete_parcela(db, parcela_id):
        raise HTTPException(status_code=404, detail="Parcela no encontrada")
    return None


# =============================================================================
# 4. GESTIÓN DE INVERNADEROS
# =============================================================================

@router.post("/invernaderos/", response_model=schemas.Invernadero, status_code=status.HTTP_201_CREATED, summary="Crear Invernadero")
def crear_invernadero(
    invernadero: schemas.InvernaderoCreate, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    db_parcela = crud.get_parcela(db, parcela_id=invernadero.parcela_id)
    if not db_parcela:
        raise HTTPException(status_code=404, detail=f"La parcela {invernadero.parcela_id} no existe.")
    if invernadero.cultivo_id:
        db_cultivo = crud.get_cultivo(db, cultivo_id=invernadero.cultivo_id)
        if not db_cultivo:
            raise HTTPException(status_code=404, detail=f"El cultivo {invernadero.cultivo_id} no existe.")
    return crud.create_invernadero(db=db, invernadero=invernadero)

@router.get("/invernaderos/", response_model=List[schemas.Invernadero], summary="Listar Invernaderos")
def listar_invernaderos(
    skip: int = 0, 
    limit: int = 100, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    """Lista los invernaderos registrados. Requiere Login."""
    return crud.get_invernaderos(db, skip=skip, limit=limit)

@router.get("/invernaderos/cliente/{cliente_id}", response_model=List[schemas.Invernadero], summary="Listar Invernaderos de un Cliente")
def listar_invernaderos_cliente(
    cliente_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    if not crud.get_cliente(db, cliente_id):
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    return crud.get_invernaderos_por_cliente(db, cliente_id=cliente_id)

@router.get("/invernaderos/{invernadero_id}", response_model=schemas.Invernadero, summary="Leer Invernadero")
def leer_invernadero(
    invernadero_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    db_invernadero = crud.get_invernadero(db, invernadero_id=invernadero_id)
    if db_invernadero is None:
        raise HTTPException(status_code=404, detail="Invernadero no encontrado")
    return db_invernadero

@router.put("/invernaderos/{invernadero_id}", response_model=schemas.Invernadero, summary="Actualizar Invernadero")
def actualizar_invernadero(
    invernadero_id: int, 
    invernadero_update: schemas.InvernaderoUpdate, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    try:
        db_invernadero = crud.update_invernadero(db=db, invernadero_id=invernadero_id, invernadero_update=invernadero_update)
        if db_invernadero is None:
            raise HTTPException(status_code=404, detail="Invernadero no encontrado")
        return db_invernadero
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))

@router.delete("/invernaderos/{invernadero_id}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Invernadero (Lógico)")
def borrar_invernadero(
    invernadero_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    if not crud.delete_invernadero(db, invernadero_id):
        raise HTTPException(status_code=404, detail="Invernadero no encontrado")
    return None
    

# =============================================================================
# 5. GESTIÓN DE CULTIVOS (PÚBLICO)
# =============================================================================

@router.get("/cultivos/", response_model=List[schemas.Cultivo], summary="Listar Cultivos")
def listar_cultivos(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    """
    Devuelve el catálogo de cultivos registrados en el sistema. 
    Se utiliza para rellenar desplegables en el frontend.
    """
    return crud.get_cultivos(db, skip=skip, limit=limit)

@router.get("/cultivos/{cultivo_id}", response_model=schemas.Cultivo, summary="Leer Cultivo")
def leer_cultivo(cultivo_id: int, db: Session = Depends(get_db)):
    db_cultivo = crud.get_cultivo(db, cultivo_id=cultivo_id)
    if db_cultivo is None:
        raise HTTPException(status_code=404, detail="Cultivo no encontrado")
    return db_cultivo


# =============================================================================
# 6. JERARQUÍA DEL DASHBOARD
# =============================================================================

@router.get("/clientes/me/jerarquia", response_model=schemas.JerarquiaCliente, summary="Obtener Jerarquía del Dashboard")
def obtener_jerarquia(
    cliente_id: int = None,
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    """
    Construye y devuelve un árbol estructurado de TODA la infraestructura.
    Si eres admin, puedes pasar un 'cliente_id' para ver el de otro.
    Si eres cliente, solo verás el tuyo.
    """
    target_cliente_id = current_user.cliente_id
    
    # Si piden un cliente específico y son admin/root, lo permitimos
    if cliente_id is not None and current_user.rol in ["admin", "root"]:
        # Verificamos que el cliente solicitado exista
        target_cliente = crud.get_cliente(db, cliente_id)
        if not target_cliente:
            raise HTTPException(status_code=404, detail="Cliente no encontrado")
        
        # RESTRICCIÓN: Un ADMIN solo puede impersonar a un CLIENTE.
        if current_user.rol == "admin" and target_cliente.rol != "cliente":
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN, 
                detail="Acceso denegado: Un administrador no puede inspeccionar perfiles de otros administradores o del sistema root."
            )

        target_cliente_id = cliente_id
        nombre_empresa_mostrar = target_cliente.nombre_empresa
    else:
        nombre_empresa_mostrar = current_user.nombre_empresa

    # 1. Obtenemos todas las parcelas del cliente objetivo
    parcelas_db = crud.get_parcelas_por_cliente(db, cliente_id=target_cliente_id)
    
    # 2. Diccionario para agrupar las parcelas bajo el código postal de su localidad
    estruct_localidades = {}
    
    for parcela in parcelas_db:
        cp = parcela.localidad.codigo_postal
        
        # Si la localidad no existe en nuestro diccionario temporal, la creamos
        if cp not in estruct_localidades:
            estruct_localidades[cp] = {
                "codigo_postal": str(cp),
                "municipio": parcela.localidad.municipio,
                "provincia": parcela.localidad.provincia,
                "num_parcelas": 0,
                "num_invernaderos_total": 0,
                "parcelas": []
            }
            
        # Transformamos los invernaderos a la estructura de la API
        invernaderos_lista = []
        for inv in parcela.invernaderos:
            invernaderos_lista.append(schemas.InvernaderoJerarquia(
                invernadero_id=inv.invernadero_id,
                nombre=inv.nombre,
                largo_m=inv.largo_m,
                ancho_m=inv.ancho_m,
                cultivo=inv.cultivo.nombre_cultivo if inv.cultivo else None
            ))
            
        # Agregamos la parcela
        parcela_schema = schemas.ParcelaJerarquia(
            parcela_id=parcela.parcela_id,
            nombre=parcela.nombre,
            direccion=parcela.direccion,
            ref_catastral=parcela.ref_catastral,
            num_invernaderos=len(invernaderos_lista),
            invernaderos=invernaderos_lista
        )
        
        estruct_localidades[cp]["parcelas"].append(parcela_schema)
        estruct_localidades[cp]["num_parcelas"] += 1
        estruct_localidades[cp]["num_invernaderos_total"] += len(invernaderos_lista)
        
    # Convertimos el diccionario a la lista de esquemas
    localidades_jerarquia = []
    for loc_data in estruct_localidades.values():
        localidades_jerarquia.append(schemas.LocalidadJerarquia(**loc_data))
        
    # 3. Construimos el objeto raíz (El Cliente)
    return schemas.JerarquiaCliente(
        cliente_id=target_cliente_id,
        nombre_empresa=nombre_empresa_mostrar,
        num_localidades=len(localidades_jerarquia),
        localidades=localidades_jerarquia
    )