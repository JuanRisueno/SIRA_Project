"""
=============================================================================
             Definición de Schemas (Pydantic) - Tarea 8
=============================================================================

Propósito:
schemas.py define la forma de los datos que tu API envía y recibe, actuando como un filtro de seguridad que valida la información que entra y limpia la información que sale, asegurando que el models.py nunca quede expuesto.

Este archivo define los "Schemas" (esquemas) de datos utilizando Pydantic.
https://www.tutorialesprogramacionya.com/fastapiya/
https://keepcoding.io/blog/como-utilizar-las-type-hints-en-python/
https://www.tutorialesprogramacionya.com/fastapiya/tema9.html

Estos schemas actúan como el "contrato" de la API. Definen la forma exacta
de los datos que:
1.  La API RECIBE (ej. en un POST o PUT).
2.  La API DEVUELVE (ej. en un GET).

FastAPI usa estos schemas para:
-   Validar automáticamente los datos de entrada (ej. asegurar que un 'int' es un 'int').
-   Filtrar los datos de salida (ej. NUNCA devolver el 'hash_contrasena').
-   Generar la documentación automática de la API (en /docs).
"""
# --- IMPORTACIONES ---
# BaseModel: La clase base de Pydantic. Todos los schemas heredan de ella para validar datos.
# Field: Permite añadir validaciones extra (longitud, regex) y metadatos a los campos.
# ConfigDict: Configuración interna del modelo (usado para activar el modo ORM).
from pydantic import BaseModel, Field, ConfigDict

# Tipos de datos de Python para "Type Hinting" (Pistas de tipo)
from typing import Optional, List
# Decimal: Usamos este tipo específico para coincidir con el Numeric de la BBDD y evitar errores de redondeo.
from decimal import Decimal
# date: Para manejar fechas sin hora (ej. fecha de plantación).
from datetime import date, datetime

# =============================================================================
# 1. SCHEMAS DE CLIENTE
# =============================================================================
# --- CLASE BASE ---
# Contiene los campos comunes que se usan tanto al LEER como al ESCRIBIR. Suelen ser los cambios que rellena el usuario.
class ClienteBase(BaseModel):
    nombre_empresa: str
    # Validamos longitud exacta de 9 caracteres para el CIF.
    cif: str = Field(..., min_length=9, max_length=9)
    email_admin: str
    telefono: str
    persona_contacto: str

# --- CLASE CREATE (Input) ---
# Datos necesarios para crear el cliente. Aquí incluimos la contraseña.
class ClienteCreate(ClienteBase):
    # Solo pedimos la contraseña al crear el cliente.
    # NO la incluimos en 'ClienteBase' ni en 'Cliente' (Response) por seguridad.
    hash_contrasena: str 

# --- CLASE RESPONSE (Output) ---
# Estructura de datos que devolvemos al cliente (Frontend/Usuario).
class Cliente(ClienteBase):
    # El ID es generado por la BBDD (Serial), así que solo existe en el schema de Respuesta.
    cliente_id: int
    model_config = ConfigDict(from_attributes=True)

# =============================================================================
# 2. SCHEMAS DE LOCALIDAD
# =============================================================================

class LocalidadBase(BaseModel):
    # Field(...) indica que el campo es obligatorio.
    # min/max_length=5 fuerza que el CP tenga exactamente 5 caracteres (ej: "23700").
    codigo_postal: str = Field(..., min_length=5, max_length=5, description="Código Postal formato español")
    municipio: str
    provincia: str

class LocalidadCreate(LocalidadBase):
    pass

class Localidad(LocalidadBase):
    # model_config sustituye a la antigua clase 'Config' en Pydantic v2.
    # from_attributes=True permite que Pydantic lea datos directamente de objetos SQLAlchemy (ORM),
    # no solo de diccionarios JSON.
    model_config = ConfigDict(from_attributes=True)


# =============================================================================
# 3. SCHEMAS DE CULTIVO
# =============================================================================
class CultivoBase(BaseModel):
    nombre_cultivo: str
    # Optional[str]: Permite que el campo sea NULL.
    # = None: Valor por defecto si no se envía nada.
    external_api_id: Optional[str] = None

class CultivoCreate(CultivoBase):
    pass

class Cultivo(CultivoBase):
    cultivo_id: int
    model_config = ConfigDict(from_attributes=True)

# =============================================================================
# 4. SCHEMAS DE PARCELA
# =============================================================================
class ParcelaBase(BaseModel):
    direccion: str
    ref_catastral: str = Field(..., min_length=14, max_length=14)

class ParcelaCreate(ParcelaBase):
    # Claves Foráneas (FK): Para crear una parcela, necesitamos saber
    # a qué cliente (ID) y a qué localidad (CP) pertenece.
    cliente_id: int
    codigo_postal: str

class Parcela(ParcelaBase):
    parcela_id: int
    
    # En la respuesta, confirmamos los IDs asociados (FK).
    cliente_id: int
    codigo_postal: str
    
    # Tablas relacionadas: Cliente y Localidad
    cliente: Cliente
    localidad: Localidad
    
    model_config = ConfigDict(from_attributes=True)

# =============================================================================
# 5. SCHEMAS DE INVERNADERO
# =============================================================================
class InvernaderoBase(BaseModel):
    # Usamos 'date' de Python. Pydantic convertirá automáticamente strings "YYYY-MM-DD" a objetos fecha.
    fecha_plantacion: Optional[date] = None 
    # Usamos Decimal para garantizar precisión en medidas físicas.
    largo_m: Decimal
    ancho_m: Decimal

class InvernaderoCreate(InvernaderoBase):
    # Obligatorio: No existe invernadero sin parcela física.
    parcela_id: int 
    # Opcional: Puede estar en "Barbecho" (sin cultivo asignado).
    cultivo_id: Optional[int] = None

class Invernadero(InvernaderoBase):
    invernadero_id: int
    
    # Claves Foráneas (FK)
    parcela_id: int
    cultivo_id: Optional[int] = None

    # --- ANIDACIÓN (Rich Response) ---
    # Esta es la potencia de los schemas:
    # Al pedir un Invernadero, Pydantic irá al objeto ORM, buscará la relación '.cultivo',
    # cogerá sus datos y los formateará usando el schema 'Cultivo' definido arriba.
    cultivo: Optional[Cultivo] = None
    
    #Tabla relacionada: Parcela
    parcela: Parcela

    model_config = ConfigDict(from_attributes=True)
    
# =============================================================================
# 6. SCHEMAS DE PARAMETROS_OPTIMOS
# =============================================================================
class ParametrosOptimosBase(BaseModel):
    temp_optima_min: Decimal
    temp_optima_max: Decimal
    humedad_optima_min: Decimal
    humedad_optima_max: Decimal
    necesidad_hidrica: Decimal
    fase_crecimiento: str
    

class ParametrosOptimosCreate(ParametrosOptimosBase):
    # Obligatorio: No existe ParametrosOptimos sin parcela Cultivo.
    cultivo_id: int 

class ParametrosOptimos(ParametrosOptimosBase):
    parametro_id: int
    
    # Clave Foránea (FK)
    cultivo_id: int

    # Tabla relacionada: Cultivo
    cultivo: Cultivo

    model_config = ConfigDict(from_attributes=True)
    
# =============================================================================
# 7. SCHEMAS DE RECOMENDACION_RIEGO
# =============================================================================
class RecomendacionRiegoBase(BaseModel):
    fecha_recomendacion: Optional[datetime] = None
    cantidad_ml: Decimal
    duracion_min: int
    razon_logica: str = Field(..., max_length=255)
    

class RecomendacionRiegoCreate(RecomendacionRiegoBase):
    # Obligatorio: No existe RecomendacionRiego sin parcela Invernadero.
    invernadero_id: int 

class RecomendacionRiego(RecomendacionRiegoBase):
    recomendacion_id: int
    
    # Clave Foránea (FK)
    invernadero_id: int

    # Tabla relacionada: Invernadero
    invernadero: Invernadero

    model_config = ConfigDict(from_attributes=True)
    
# =============================================================================
# 8. SCHEMAS DE TIPO_ACTUADOR
# =============================================================================
class TipoActuadorBase(BaseModel):
    nombre_tipo: str = Field(..., max_length=100)
    

class TipoActuadorCreate(TipoActuadorBase):
    pass    

class TipoActuador(TipoActuadorBase):
    tipo_actuador_id: int
    
    model_config = ConfigDict(from_attributes=True)
    
# =============================================================================
# 9. SCHEMAS DE TIPO_SENSOR
# =============================================================================
class TipoSensorBase(BaseModel):
    nombre_tipo: str = Field(..., max_length=100)
    unidad_medida: str = Field(..., max_length=20)
    
class TipoSensorCreate(TipoSensorBase):
    pass    

class TipoSensor(TipoSensorBase):
    tipo_sensor_id: int
    
    model_config = ConfigDict(from_attributes=True)

# =============================================================================
# 10. SCHEMAS DE SENSOR
# =============================================================================
class SensorBase(BaseModel):
    # Optional: Puede ser NULL si el sensor está en la caja (Inventario).
    ubicacion_sensor: Optional[str] = Field(None, max_length=100)
    estado_sensor: Optional[str] = Field(None, max_length=20)
    

class SensorCreate(SensorBase):
    # OBLIGATORIO: Siempre sabemos qué tipo de aparato es (ej. "Temp").
    tipo_sensor_id: int
    
    # OPCIONAL: Si no se envía, se asume que va al almacén (Inventario).
    invernadero_id: Optional[int] = None
    
class Sensor(SensorBase):
    sensor_id: int
    
    # Clave Foránea (FK)
    invernadero_id: Optional[int] = None
    tipo_sensor_id: int
    
    # Tablas relacionadas: Invernadero y TipoSensor
    tipo_sensor: TipoSensor
    invernadero: Optional[Invernadero] = None

    model_config = ConfigDict(from_attributes=True)
    
# =============================================================================
# 11. SCHEMAS DE ACTUADOR
# =============================================================================
class ActuadorBase(BaseModel):
    # Optional: Puede ser NULL si el actuador está en la caja (Inventario).
    ubicacion_actuador: Optional[str] = Field(None, max_length=100)
    estado_actuador: Optional[str] = Field(None, max_length=20)
    

class ActuadorCreate(ActuadorBase):
    # OBLIGATORIO: Siempre sabemos qué tipo de aparato es (ej. "Ventilador").
    tipo_actuador_id: int
    
    # OPCIONAL: Si no se envía, se asume que va al almacén (Inventario).
    invernadero_id: Optional[int] = None
    
class Actuador(ActuadorBase):
    actuador_id: int
    
    # Clave Foránea (FK)
    invernadero_id: Optional[int] = None
    tipo_actuador_id: int
    
    # Tablas relacionadas: Invernadero y TipoActuador
    tipo_actuador: TipoActuador
    invernadero: Optional[Invernadero] = None

    model_config = ConfigDict(from_attributes=True)
    
# =============================================================================
# 12. SCHEMAS DE MEDICION
# =============================================================================
class MedicionBase(BaseModel):
    # Optional: Si la Raspberry no envía hora, la BBDD pondrá la actual.
    fecha_hora: Optional[datetime] = None
    valor: Decimal    

class MedicionCreate(MedicionBase):
    # Obligatorio: No existe Medicion sin Sensor asociado.
    sensor_id: int

class Medicion(MedicionBase):
    medicion_id: int
    
    # Claves Foráneas (FK)
    sensor_id: int
    
    # Tabla relacionada: Sensor
    sensor: Sensor

    model_config = ConfigDict(from_attributes=True)
    
# =============================================================================
# 13. SCHEMAS DE ACCION_ACTUADOR
# =============================================================================
class AccionActuadorBase(BaseModel):
    fecha_hora: Optional[datetime] = None
    accion_detalle: str = Field(..., max_length=100)

class AccionActuadorCreate(AccionActuadorBase):
    # Obligatorio: No existe AccionActuador sin Actuador asociado.
    actuador_id: int

class AccionActuador(AccionActuadorBase):
    accion_id: int
    
    # Claves Foráneas (FK)
    actuador_id: int
    
    # Tabla relacionada: Actuador
    actuador: Actuador

    model_config = ConfigDict(from_attributes=True)