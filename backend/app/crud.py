"""
=============================================================================
            Lógica CRUD (Create, Read, Update, Delete)
=============================================================================

Propósito:
Este archivo contiene funciones reutilizables para interactuar con la base de datos.
Actúa como puente entre la API (Routers) y los datos (Models).

Separar esta lógica permite:
1.  Reutilizar código: La función 'get_cliente' sirve para el login y para el perfil.
2.  Testear fácil: Podemos probar estas funciones sin levantar el servidor web.
3.  Limpieza: Los endpoints en 'main.py' o 'routers/' quedan limpios y legibles.

Convención de Nombres:
- get_item(db, id): Obtener uno por ID.
- get_items(db, skip, limit): Obtener lista paginada.
- create_item(db, schema): Crear nuevo.
"""
# --- 1. IMPORTACIONES DE TERCEROS (Librerías externas) ---

# Session: No es para ejecutar código, es para "Type Hinting" (Pistas de Tipo).
# Sirve para que VS Code sepa que la variable 'db' es una conexión a BBDD
# y te autocomplete métodos como .add(), .commit() o .query().
from sqlalchemy.orm import Session

# bcrypt: Librería oficial para hashear contraseñas de forma segura.
import bcrypt


# --- 2. IMPORTACIONES LOCALES (Tu propio código) ---

# Importamos los 'models' (Tablas SQL) para saber DÓNDE guardar los datos.
# Importamos los 'schemas' (Validación Pydantic) para saber QUÉ datos recibimos.
# El punto (.) significa "busca en esta misma carpeta".
from . import models, schemas

# =============================================================================
# 1. LÓGICA PARA CLIENTE
# =============================================================================

"""Busca un cliente por su ID (PK)."""
"""db: Session --> es la conexión técnica temporal a PostgreSQL que FastAPI le entrega automáticamente al usuario para que puedas crear o leer datos"""
"""cliente_id:int --> es el dato que le pasamos para conseguir la información con el get"""
def get_cliente(db: Session, cliente_id: int):
    return db.query(models.Cliente).filter(models.Cliente.cliente_id == cliente_id).first()

"""
Busca un cliente por su CIF.
IMPORTANTE: Esta función se usará para el LOGIN (Regla de Negocio).
"""
def get_cliente_by_cif(db: Session, cif: str):
    return db.query(models.Cliente).filter(models.Cliente.cif == cif).first()

"""Lista paginada de clientes."""
def get_clientes(db: Session, skip: int = 0, limit: int = 100):
    return db.query(models.Cliente).offset(skip).limit(limit).all()

"""
Crea un nuevo cliente en la base de datos.
PASO CLAVE: Hashear la contraseña usando bcrypt puro.
"""
def create_cliente(db: Session, cliente: schemas.ClienteCreate):
    # 1. Convertir la contraseña a bytes (utf-8)
    password_bytes = cliente.hash_contrasena.encode('utf-8')
    
    # 2. Generar un "Salt" (ruido aleatorio) y hashear la contraseña.
    # bcrypt requiere bytes, por eso hacemos .encode('utf-8')
    salt = bcrypt.gensalt()
    hashed_bytes = bcrypt.hashpw(cliente.hash_contrasena.encode('utf-8'), salt)
    
    # 3. Convertir el hash (que es bytes) a string para guardarlo en PostgreSQL (VARCHAR).
    hashed_password_str = hashed_bytes.decode('utf-8')
    
    # 4. Crear objeto ORM
    db_cliente = models.Cliente(
        nombre_empresa=cliente.nombre_empresa,
        cif=cliente.cif,
        email_admin=cliente.email_admin,
        telefono=cliente.telefono,
        persona_contacto=cliente.persona_contacto,
        hash_contrasena=hashed_password_str # Guardamos el string seguro
    )
    
    # 5. Transacción SQL
    db.add(db_cliente)
    db.commit()
    db.refresh(db_cliente)
    
    return db_cliente

# =============================================================================
# 2. LÓGICA PARA LOCALIDAD
# =============================================================================

"""
Busca una localidad por su CP.
Nota: Aquí la PK no es un 'int', es un 'str' (ej: "23700").
"""
def get_localidad(db: Session, codigo_postal: str):
    return db.query(models.Localidad).filter(models.Localidad.codigo_postal == codigo_postal).first()

"""
Registra una nueva localidad.
Al no ser autoincremental, nosotros le pasamos el CP explícitamente.
"""
def create_localidad(db: Session, localidad: schemas.LocalidadCreate):
    db_localidad = models.Localidad(
        codigo_postal=localidad.codigo_postal,
        municipio=localidad.municipio,
        provincia=localidad.provincia
    )
    db.add(db_localidad)
    db.commit()
    db.refresh(db_localidad)
    return db_localidad

# =============================================================================
# 3. LÓGICA PARA PARCELA
# =============================================================================

"""
Devuelve el listado de todos los terrenos registrados (paginado).
"""
def get_parcelas(db: Session, skip: int = 0, limit: int = 100):
    return db.query(models.Parcela).offset(skip).limit(limit).all()

"""
Busca UNA parcela por su ID.
"""
def get_parcela(db: Session, parcela_id: int):
    return db.query(models.Parcela).filter(models.Parcela.parcela_id == parcela_id).first()

"""
Crea una parcela vinculándola a un Cliente y una Localidad existentes.
Recibe los IDs (FK) en el schema y los pasa al modelo ORM para el INSERT.
"""
def create_parcela(db: Session, parcela: schemas.ParcelaCreate):
    db_parcela = models.Parcela(
        direccion=parcela.direccion,
        ref_catastral=parcela.ref_catastral,
        cliente_id=parcela.cliente_id,      # Enlace al Cliente
        codigo_postal=parcela.codigo_postal # Enlace a la Localidad
    )
    db.add(db_parcela)
    db.commit()
    db.refresh(db_parcela)
    return db_parcela

# =============================================================================
# 4. LÓGICA PARA INVERNADERO
# =============================================================================
"""
Devuelve el listado de todos los invernaderos registrados (paginado).
"""
def get_invernaderos(db: Session, skip: int = 0, limit: int = 100):
    return db.query(models.Invernadero).offset(skip).limit(limit).all()

"""
Crea un invernadero vinculándolo a un Cliente y una Parcela existentes.
Recibe los IDs (FK) en el schema y los pasa al modelo ORM para el INSERT.
"""
def create_invernadero(db: Session, invernadero: schemas.InvernaderoCreate):
    db_invernadero = models.Invernadero(
        fecha_plantacion=invernadero.fecha_plantacion,
        largo_m=invernadero.largo_m,
        ancho_m=invernadero.ancho_m,
        parcela_id=invernadero.parcela_id,  # Enlace al Cliente
        cultivo_id=invernadero.cultivo_id   # Enlace al Cultivo
    )
    db.add(db_invernadero)
    db.commit()
    db.refresh(db_invernadero)
    return db_invernadero

"""
Actualiza el cultivo de un invernadero
"""
def update_invernadero_cultivo(db: Session, invernadero_id: int, nuevo_cultivo_id: int):
    # 1. Buscamos el invernadero
    db_invernadero = db.query(models.Invernadero).filter(models.Invernadero.invernadero_id == invernadero_id).first()
    
    if db_invernadero:
        # 2. Actualizamos el campo FK
        db_invernadero.cultivo_id = nuevo_cultivo_id
        # 3. Guardamos cambios
        db.commit()
        db.refresh(db_invernadero)
        
    return db_invernadero

# =============================================================================
# 5. LÓGICA PARA CULTIVO
# =============================================================================
"""
Devuelve un único cultivo buscando por su ID.
"""
def get_cultivo(db: Session, cultivo_id: int):
    return db.query(models.Cultivo).filter(models.Cultivo.cultivo_id == cultivo_id).first()

"""
Busca un cultivo por nombre (ej: "Tomate Pera") para evitar duplicados.
"""
def get_cultivo_by_nombre(db: Session, nombre_cultivo: str):
    return db.query(models.Cultivo).filter(models.Cultivo.nombre_cultivo == nombre_cultivo).first()

"""
Crea un Cultivo en el catálogo.
"""
def create_cultivo(db: Session, cultivo: schemas.CultivoCreate):
    db_cultivo = models.Cultivo(
        nombre_cultivo=cultivo.nombre_cultivo,
        external_api_id=cultivo.external_api_id
    )
    db.add(db_cultivo)
    db.commit()
    db.refresh(db_cultivo)
    return db_cultivo

# =============================================================================
# 6. LÓGICA PARA PARÁMETROS ÓPTIMOS
# =============================================================================
# =============================================================================
# 6. LÓGICA PARA PARÁMETROS ÓPTIMOS
# =============================================================================

"""
Obtiene TODOS los rangos de parámetros (fases) asociados a un CULTIVO.
Ej: Recibe el ID del Tomate y devuelve: [Fase Germinación, Fase Floración...]
"""
def get_parametros_por_cultivo(db: Session, cultivo_id: int):
    # OJO: Filtramos por 'cultivo_id', no por 'parametro_id'.
    # Usamos .all() porque un cultivo tiene varias fases.
    return db.query(models.ParametrosOptimos).filter(models.ParametrosOptimos.cultivo_id == cultivo_id).all()

"""
Crea un nuevo set de parámetros (una fase nueva para un cultivo).
"""
def create_parametros_optimos(db: Session, parametros: schemas.ParametrosOptimosCreate):
    db_parametros = models.ParametrosOptimos(
        fase_crecimiento=parametros.fase_crecimiento,
        temp_optima_min=parametros.temp_optima_min,
        temp_optima_max=parametros.temp_optima_max,
        humedad_optima_min=parametros.humedad_optima_min,
        humedad_optima_max=parametros.humedad_optima_max,
        necesidad_hidrica=parametros.necesidad_hidrica,
        # FK Importante: Vinculamos estos números a un cultivo padre (ID)
        cultivo_id=parametros.cultivo_id 
    )
    db.add(db_parametros)
    db.commit()
    db.refresh(db_parametros)
    return db_parametros

# =============================================================================
# 7. LÓGICA PARA CATÁLOGOS (TIPOS DE SENSOR/ACTUADOR)
# =============================================================================

"""
Busca un tipo de sensor por nombre (ej: "Temperatura Aire").
Útil para evitar duplicados al inicializar la base de datos.
"""
def get_tipo_sensor_by_nombre(db: Session, nombre_tipo: str):
    return db.query(models.TipoSensor).filter(models.TipoSensor.nombre_tipo == nombre_tipo).first()

"""
Crea un nuevo Tipo de Sensor en el catálogo.
"""
def create_tipo_sensor(db: Session, tipo_sensor: schemas.TipoSensorCreate):
    db_tipo = models.TipoSensor(
        nombre_tipo=tipo_sensor.nombre_tipo,
        unidad_medida=tipo_sensor.unidad_medida
    )
    db.add(db_tipo)
    db.commit()
    db.refresh(db_tipo)
    return db_tipo

"""
Busca un tipo de actuador por nombre (ej: "Electroválvula").
"""
def get_tipo_actuador_by_nombre(db: Session, nombre_tipo: str):
    return db.query(models.TipoActuador).filter(models.TipoActuador.nombre_tipo == nombre_tipo).first()

"""
Crea un nuevo Tipo de Actuador en el catálogo.
"""
def create_tipo_actuador(db: Session, tipo_actuador: schemas.TipoActuadorCreate):
    db_tipo = models.TipoActuador(
        nombre_tipo=tipo_actuador.nombre_tipo
    )
    db.add(db_tipo)
    db.commit()
    db.refresh(db_tipo)
    return db_tipo


# =============================================================================
# 8. LÓGICA PARA SENSORES Y MEDICIONES (IOT)
# =============================================================================

"""
Listado de todos los sensores (instalados o en inventario).
"""
def get_sensores(db: Session, skip: int = 0, limit: int = 100):
    return db.query(models.Sensor).offset(skip).limit(limit).all()

"""
Registra un nuevo sensor físico (Hardware).
Puede asignarse a un invernadero (campo opcional) o quedarse en stock (None).
"""
def create_sensor(db: Session, sensor: schemas.SensorCreate):
    db_sensor = models.Sensor(
        ubicacion_sensor=sensor.ubicacion_sensor,
        estado_sensor=sensor.estado_sensor,
        tipo_sensor_id=sensor.tipo_sensor_id,   # FK: Qué es
        invernadero_id=sensor.invernadero_id     # FK: Dónde está (puede ser None)
    )
    db.add(db_sensor)
    db.commit()
    db.refresh(db_sensor)
    return db_sensor

"""
Guarda una medición (Dato puro).
IMPORTANTE: No pasamos la fecha, la pone la BBDD automáticamente (server_default).
"""
def create_medicion(db: Session, medicion: schemas.MedicionCreate):
    db_medicion = models.Medicion(
        valor=medicion.valor,
        sensor_id=medicion.sensor_id # FK: Quién midió esto
    )
    db.add(db_medicion)
    db.commit()
    db.refresh(db_medicion)
    return db_medicion

"""
Obtiene el historial de mediciones de un sensor.
Ordenado por fecha DESCENDENTE (lo más nuevo primero) para ver el estado actual rápido.
"""
def get_mediciones_por_sensor(db: Session, sensor_id: int, limit: int = 100):
    return db.query(models.Medicion)\
            .filter(models.Medicion.sensor_id == sensor_id)\
            .order_by(models.Medicion.fecha_hora.desc())\
            .limit(limit).all()


# =============================================================================
# 9. LÓGICA PARA ACTUADORES Y ACCIONES
# =============================================================================

"""
Listado de actuadores.
"""
def get_actuadores(db: Session, skip: int = 0, limit: int = 100):
    return db.query(models.Actuador).offset(skip).limit(limit).all()

"""
Registra un actuador físico.
Igual que el sensor, puede estar asignado a un invernadero o no.
"""
def create_actuador(db: Session, actuador: schemas.ActuadorCreate):
    db_actuador = models.Actuador(
        ubicacion_actuador=actuador.ubicacion_actuador,
        estado_actuador=actuador.estado_actuador,
        tipo_actuador_id=actuador.tipo_actuador_id,
        invernadero_id=actuador.invernadero_id
    )
    db.add(db_actuador)
    db.commit()
    db.refresh(db_actuador)
    return db_actuador

"""
Guarda en el log qué hizo un actuador.
Ej: "Apertura ventana 50%". Sirve para auditoría.
"""
def create_accion_actuador(db: Session, accion: schemas.AccionActuadorCreate):
    db_accion = models.AccionActuador(
        accion_detalle=accion.accion_detalle,
        actuador_id=accion.actuador_id # FK: Quién se movió
    )
    db.add(db_accion)
    db.commit()
    db.refresh(db_accion)
    return db_accion


# =============================================================================
# 10. LÓGICA PARA RECOMENDACIONES DE RIEGO (IA)
# =============================================================================

"""
Guarda una decisión tomada por el algoritmo de riego inteligente.
"""
def create_recomendacion(db: Session, recomendacion: schemas.RecomendacionRiegoCreate):
    db_recomendacion = models.RecomendacionRiego(
        cantidad_ml=recomendacion.cantidad_ml,
        duracion_min=recomendacion.duracion_min,
        razon_logica=recomendacion.razon_logica,
        invernadero_id=recomendacion.invernadero_id # FK: Dónde regar
    )
    db.add(db_recomendacion)
    db.commit()
    db.refresh(db_recomendacion)
    return db_recomendacion

"""
Historial de riegos recomendados para un invernadero.
Útil para que el agricultor vea por qué se regó ayer.
"""
def get_recomendaciones_por_invernadero(db: Session, invernadero_id: int, limit: int = 50):
    return db.query(models.RecomendacionRiego)\
            .filter(models.RecomendacionRiego.invernadero_id == invernadero_id)\
            .order_by(models.RecomendacionRiego.fecha_recomendacion.desc())\
            .limit(limit).all()
