"""
=============================================================================
            Definición de Schemas (Pydantic) - Tarea 8
=============================================================================

Propósito Didáctico (Enfoque ASIR):
Este archivo actúa como el "Traductor" entre el mundo JSON (API) y el mundo SQL (BBDD).

Estructura de cada entidad (Mapeo SQL -> Python):

1.  Clase Base: Campos normales (Columnas VARCHAR, DATE, DECIMAL).
    - Son los datos que viajan en ambas direcciones (Input y Output).

2.  Clase Create (Equivale al INSERT SQL):
    - Aquí definimos las CLAVES FORÁNEAS (FK) como enteros (int).
    - ¿Por qué? Porque al hacer un 'INSERT INTO parcela (cliente_id)...',
    la base de datos espera un número (el ID), no un objeto complejo.

3.  Clase Response (Equivale al SELECT + JOIN):
    - Aquí definimos la CLAVE PRIMARIA (PK) porque la BBDD ya la ha generado (Serial).
    - Aquí podemos definir RELACIONES (Objetos completos).
    - Pydantic actúa como un JOIN automático: recupera los datos vinculados para mostrarlos.
"""

# --- Importaciones ---
from pydantic import BaseModel, Field, ConfigDict
from typing import Optional, List
from decimal import Decimal
from datetime import date, datetime

# =============================================================================
# 1. CLIENTE
# =============================================================================
class ClienteBase(BaseModel):
    """Columnas estándar de la tabla (VARCHAR)."""
    nombre_empresa: str
    # Validación: CHAR(9) estricto.
    cif: str = Field(..., min_length=9, max_length=9, description="DNI/CIF único")
    email_admin: str
    telefono: str
    persona_contacto: str

class ClienteCreate(ClienteBase):
    """Datos para el INSERT."""
    # La contraseña se pasa aquí para ser hasheada antes de guardarse.
    hash_contrasena: str 

class Cliente(ClienteBase):
    """Datos del SELECT."""
    # PK (Primary Key): Solo existe después de insertar, por eso va aquí.
    cliente_id: int 
    
    # Configuración ORM: Permite traducir la fila SQL a este objeto JSON.
    model_config = ConfigDict(from_attributes=True)

class ClienteUpdate(BaseModel):
    """
    Schema para actualizar datos. 
    Todos los campos son opcionales. Solo se actualiza lo que envíes.
    """
    nombre_empresa: Optional[str] = None
    email_admin: Optional[str] = None
    telefono: Optional[str] = None
    persona_contacto: Optional[str] = None
    
    # Campo sensible: CIF
    # Permitimos enviarlo, pero con validación de formato (9 caracteres)
    cif: Optional[str] = Field(None, min_length=9, max_length=9, description="Nuevo CIF (si se requiere corrección)")
    
    # --- TU CONFIRMACIÓN DE SEGURIDAD ---
    # El Frontend deberá enviar esto en 'True' si el usuario rellenó el campo 'cif'
    confirmar_cambio_cif: bool = False

# =============================================================================
# 2. LOCALIDAD
# =============================================================================
class LocalidadBase(BaseModel):
    # PK Manual: En este caso excepcional, la PK la pone el usuario (CP),
    # por eso está en la Base (se usa para crear y para leer).
    codigo_postal: str = Field(..., min_length=5, max_length=5)
    municipio: str
    provincia: str

class LocalidadCreate(LocalidadBase):
    pass

class Localidad(LocalidadBase):
    model_config = ConfigDict(from_attributes=True)

class LocalidadUpdate(BaseModel):
    """
    Schema para corregir datos de una localidad.
    NOTA: No permitimos cambiar el 'codigo_postal' aquí porque es la Clave Primaria (PK).
    Si el CP está mal, se deberá crear una localidad nueva.
    """
    municipio: Optional[str] = None
    provincia: Optional[str] = None

# =============================================================================
# 3. CULTIVO
# =============================================================================
class CultivoBase(BaseModel):
    nombre_cultivo: str
    external_api_id: Optional[str] = None

class CultivoCreate(CultivoBase):
    pass

class Cultivo(CultivoBase):
    # PK (Serial) generada por la BBDD.
    cultivo_id: int
    model_config = ConfigDict(from_attributes=True)


# =============================================================================
# 4. PARCELA
# =============================================================================
class ParcelaBase(BaseModel):
    """Columnas normales."""
    direccion: str
    ref_catastral: str = Field(..., min_length=14, max_length=14)

class ParcelaCreate(ParcelaBase):
    """Datos para el INSERT (Necesitamos las FKs)."""
    # FK (Foreign Keys): Para insertar en SQL, necesitamos los IDs numéricos (o el CP).
    # SQL: INSERT INTO parcela (..., cliente_id, codigo_postal) VALUES ...
    cliente_id: int
    codigo_postal: str

class Parcela(ParcelaBase):
    """Datos del SELECT (Enriquecidos)."""
    # 1. La PK generada.
    parcela_id: int
    # 2. Las FKs (IDs) para referencia.
    cliente_id: int
    codigo_postal: str
    
    # 3. "JOINS Virtuales" (Rich Response):
    # En lugar de ver solo "cliente_id: 5", el usuario verá todo el objeto Cliente.
    # Pydantic busca la relación definida en models.py y rellena esto automáticamente.
    cliente: Cliente
    localidad: Localidad
    
    model_config = ConfigDict(from_attributes=True)

# --- EN schemas.py (Sección Parcela) ---

class ParcelaUpdate(BaseModel):
    """
    Schema para actualización estricta de parcela.
    
    REGLAS DE NEGOCIO:
    1. La Ubicación (Dirección + CP) ES INMUTABLE. No se puede cambiar.
    2. Se permite el traspaso de titularidad (cliente_id).
    3. Se permite corregir la Ref. Catastral (solo errores tipográficos) con confirmación.
    """
    # FK: El único cambio "natural" es la compra-venta (cambio de dueño)
    cliente_id: Optional[int] = None
    
    # Campo Sensible: Referencia Catastral (Solo para correcciones de errores)
    ref_catastral: Optional[str] = Field(None, min_length=14, max_length=14, description="Nueva Ref. Catastral si hay error")
    
    # Interruptor de Seguridad
    confirmar_cambio_ref: bool = False

# =============================================================================
# 5. INVERNADERO
# =============================================================================
class InvernaderoBase(BaseModel):
    nombre: str = Field(..., max_length=50, description="Ej: Nave 1, Invernadero Norte")
    fecha_plantacion: Optional[date] = None 
    largo_m: Decimal
    ancho_m: Decimal

class InvernaderoCreate(InvernaderoBase):
    """INSERT: Pasamos IDs."""
    # FK Obligatoria: No existe invernadero sin parcela.
    parcela_id: int 
    # FK Opcional (Nullable en SQL): Puede ser NULL (Barbecho).
    cultivo_id: Optional[int] = None 

class Invernadero(InvernaderoBase):
    """SELECT: Devolvemos datos y relaciones."""
    invernadero_id: int # PK
    parcela_id: int     # FK
    cultivo_id: Optional[int] = None # FK

    # JOINs: Objetos completos para el Frontend.
    cultivo: Optional[Cultivo] = None
    parcela: Parcela

    model_config = ConfigDict(from_attributes=True)

class InvernaderoUpdate(BaseModel):
    """
    Schema para actualizar un invernadero.
    Permite renombrarlo (nuevo dueño), rotar cultivos o corregir medidas.
    """
    # AÑADIDO: Permitimos cambiar el nombre
    nombre: Optional[str] = Field(None, max_length=50)
    
    fecha_plantacion: Optional[date] = None
    largo_m: Optional[Decimal] = None
    ancho_m: Optional[Decimal] = None
    
    cultivo_id: Optional[int] = None

# =============================================================================
# 6. PARÁMETROS ÓPTIMOS
# =============================================================================
class ParametrosOptimosBase(BaseModel):
    fase_crecimiento: str
    temp_optima_min: Decimal
    temp_optima_max: Decimal
    humedad_optima_min: Decimal
    humedad_optima_max: Decimal
    necesidad_hidrica: Decimal

class ParametrosOptimosCreate(ParametrosOptimosBase):
    # FK: Vincula estos parámetros a un cultivo (ID).
    cultivo_id: int 

class ParametrosOptimos(ParametrosOptimosBase):
    parametro_id: int # PK
    cultivo_id: int   # FK
    
    # JOIN: Ver el cultivo asociado.
    cultivo: Cultivo
    model_config = ConfigDict(from_attributes=True)


# =============================================================================
# 7. RECOMENDACIÓN DE RIEGO
# =============================================================================
class RecomendacionRiegoBase(BaseModel):
    fecha_recomendacion: Optional[datetime] = None
    cantidad_ml: Decimal
    duracion_min: int
    razon_logica: str = Field(..., max_length=255)

class RecomendacionRiegoCreate(RecomendacionRiegoBase):
    # FK: ¿A qué invernadero afecta?
    invernadero_id: int 

class RecomendacionRiego(RecomendacionRiegoBase):
    recomendacion_id: int # PK
    invernadero_id: int   # FK
    
    invernadero: Invernadero
    model_config = ConfigDict(from_attributes=True)


# =============================================================================
# 8. CATÁLOGOS (TIPOS)
# =============================================================================
class TipoActuadorBase(BaseModel):
    nombre_tipo: str = Field(..., max_length=100)

class TipoActuadorCreate(TipoActuadorBase):
    pass    

class TipoActuador(TipoActuadorBase):
    tipo_actuador_id: int # PK
    model_config = ConfigDict(from_attributes=True)

class TipoSensorBase(BaseModel):
    nombre_tipo: str = Field(..., max_length=100)
    unidad_medida: str = Field(..., max_length=20)
    
class TipoSensorCreate(TipoSensorBase):
    pass    

class TipoSensor(TipoSensorBase):
    tipo_sensor_id: int # PK
    model_config = ConfigDict(from_attributes=True)


# =============================================================================
# 9. DISPOSITIVOS (SENSOR / ACTUADOR)
# =============================================================================
# SENSOR
class SensorBase(BaseModel):
    ubicacion_sensor: Optional[str] = Field(None, max_length=100)
    estado_sensor: Optional[str] = Field(None, max_length=20)

class SensorCreate(SensorBase):
    """INSERT"""
    # FKs necesarias para crear el registro.
    tipo_sensor_id: int
    invernadero_id: Optional[int] = None # Nullable (Inventario)

class Sensor(SensorBase):
    """SELECT"""
    sensor_id: int # PK
    # FKs (IDs)
    tipo_sensor_id: int
    invernadero_id: Optional[int] = None
    
    # JOINs (Objetos)
    tipo_sensor: TipoSensor
    invernadero: Optional[Invernadero] = None # Puede no tener invernadero
    model_config = ConfigDict(from_attributes=True)

# ACTUADOR
class ActuadorBase(BaseModel):
    ubicacion_actuador: Optional[str] = Field(None, max_length=100)
    estado_actuador: Optional[str] = Field(None, max_length=20)

class ActuadorCreate(ActuadorBase):
    """INSERT"""
    tipo_actuador_id: int # FK
    invernadero_id: Optional[int] = None # FK Nullable

class Actuador(ActuadorBase):
    """SELECT"""
    actuador_id: int # PK
    tipo_actuador_id: int # FK
    invernadero_id: Optional[int] = None # FK
    
    # JOINs
    tipo_actuador: TipoActuador
    invernadero: Optional[Invernadero] = None
    model_config = ConfigDict(from_attributes=True)


# =============================================================================
# 10. HISTORIAL (MEDICIONES / ACCIONES)
# =============================================================================
class MedicionBase(BaseModel):
    fecha_hora: Optional[datetime] = None
    valor: Decimal    

class MedicionCreate(MedicionBase):
    # FK: ¿De qué sensor es este dato?
    sensor_id: int

class Medicion(MedicionBase):
    medicion_id: int # PK
    sensor_id: int   # FK
    
    # NOTA DE ARQUITECTURA: 
    # Aquí NO hacemos JOIN con 'Sensor' por rendimiento.
    # Las mediciones se piden por miles, traer el objeto sensor en cada una sería muy lento.
    model_config = ConfigDict(from_attributes=True)

class AccionActuadorBase(BaseModel):
    fecha_hora: Optional[datetime] = None
    accion_detalle: str = Field(..., max_length=100)

class AccionActuadorCreate(AccionActuadorBase):
    # FK: ¿Qué actuador se movió?
    actuador_id: int

class AccionActuador(AccionActuadorBase):
    accion_id: int   # PK
    actuador_id: int # FK
    model_config = ConfigDict(from_attributes=True)
