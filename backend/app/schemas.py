# backend/app/schemas.py

"""
=============================================================================
            Definición de Schemas (Pydantic) - Tarea 8 (ACTUALIZADO AUTH)
=============================================================================
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
    cif: str = Field(..., min_length=9, max_length=9, description="DNI/CIF único")
    email_admin: str
    telefono: str
    persona_contacto: str

class ClienteCreate(ClienteBase):
    """Datos para el REGISTRO (Input)."""
    # [CAMBIO] Recibimos la contraseña plana desde el Frontend.
    # El Backend se encarga de hashearla antes de guardarla en BBDD.
    password: str 

class ClienteRead(ClienteBase):
    """
    [NUEVO] Schema específico para LEER clientes.
    Este es el que busca el sistema de Login.
    """
    cliente_id: int 
    model_config = ConfigDict(from_attributes=True)

# Mantenemos la clase 'Cliente' por compatibilidad con tu código antiguo
# Ahora 'Cliente' es simplemente un alias de 'ClienteRead'
class Cliente(ClienteRead):
    pass

class ClienteUpdate(BaseModel):
    """Schema para actualizar datos."""
    username: Optional[str] = None
    nombre_empresa: Optional[str] = None
    email_admin: Optional[str] = None
    telefono: Optional[str] = None
    persona_contacto: Optional[str] = None
    cif: Optional[str] = Field(None, min_length=9, max_length=9)
    confirmar_cambio_cif: bool = False

# =============================================================================
# 2. LOCALIDAD
# =============================================================================
class LocalidadBase(BaseModel):
    codigo_postal: str = Field(..., min_length=5, max_length=5)
    municipio: str
    provincia: str

class LocalidadCreate(LocalidadBase):
    pass

class Localidad(LocalidadBase):
    model_config = ConfigDict(from_attributes=True)

class LocalidadUpdate(BaseModel):
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
    cultivo_id: int
    model_config = ConfigDict(from_attributes=True)

# =============================================================================
# 4. PARCELA
# =============================================================================
class ParcelaBase(BaseModel):
    direccion: str
    ref_catastral: str = Field(..., min_length=14, max_length=14)

class ParcelaCreate(ParcelaBase):
    cliente_id: int
    codigo_postal: str

class Parcela(ParcelaBase):
    parcela_id: int
    cliente_id: int
    codigo_postal: str
    
    # Aquí usamos 'Cliente' (que ahora es compatible con ClienteRead)
    cliente: Cliente 
    localidad: Localidad
    
    model_config = ConfigDict(from_attributes=True)

class ParcelaUpdate(BaseModel):
    cliente_id: Optional[int] = None
    ref_catastral: Optional[str] = Field(None, min_length=14, max_length=14)
    confirmar_cambio_ref: bool = False

# =============================================================================
# 5. INVERNADERO
# =============================================================================
class InvernaderoBase(BaseModel):
    nombre: str = Field(..., max_length=50)
    fecha_plantacion: Optional[date] = None 
    largo_m: Decimal = Field(..., json_schema_extra={'example': 0.00})
    ancho_m: Decimal = Field(..., json_schema_extra={'example': 0.00})

class InvernaderoCreate(InvernaderoBase):
    parcela_id: int 
    cultivo_id: Optional[int] = None 

class Invernadero(InvernaderoBase):
    invernadero_id: int 
    parcela_id: int     
    cultivo_id: Optional[int] = None 

    cultivo: Optional[Cultivo] = None
    parcela: Parcela

    model_config = ConfigDict(from_attributes=True)

class InvernaderoUpdate(BaseModel):
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
    cultivo_id: int 

class ParametrosOptimos(ParametrosOptimosBase):
    parametro_id: int 
    cultivo_id: int   
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
    invernadero_id: int 

class RecomendacionRiego(RecomendacionRiegoBase):
    recomendacion_id: int 
    invernadero_id: int   
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
    tipo_actuador_id: int 
    model_config = ConfigDict(from_attributes=True)

class TipoSensorBase(BaseModel):
    nombre_tipo: str = Field(..., max_length=100)
    unidad_medida: str = Field(..., max_length=20)
    
class TipoSensorCreate(TipoSensorBase):
    pass    

class TipoSensor(TipoSensorBase):
    tipo_sensor_id: int 
    model_config = ConfigDict(from_attributes=True)

# =============================================================================
# 9. DISPOSITIVOS (SENSOR / ACTUADOR)
# =============================================================================
class SensorBase(BaseModel):
    ubicacion_sensor: Optional[str] = Field(None, max_length=100)
    estado_sensor: Optional[str] = Field(None, max_length=20)

class SensorCreate(SensorBase):
    tipo_sensor_id: int
    invernadero_id: Optional[int] = None 

class Sensor(SensorBase):
    sensor_id: int 
    tipo_sensor_id: int
    invernadero_id: Optional[int] = None
    tipo_sensor: TipoSensor
    invernadero: Optional[Invernadero] = None 
    model_config = ConfigDict(from_attributes=True)

class ActuadorBase(BaseModel):
    ubicacion_actuador: Optional[str] = Field(None, max_length=100)
    estado_actuador: Optional[str] = Field(None, max_length=20)

class ActuadorCreate(ActuadorBase):
    tipo_actuador_id: int 
    invernadero_id: Optional[int] = None 

class Actuador(ActuadorBase):
    actuador_id: int 
    tipo_actuador_id: int 
    invernadero_id: Optional[int] = None 
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
    sensor_id: int

class Medicion(MedicionBase):
    medicion_id: int 
    sensor_id: int   
    model_config = ConfigDict(from_attributes=True)

class AccionActuadorBase(BaseModel):
    fecha_hora: Optional[datetime] = None
    accion_detalle: str = Field(..., max_length=100)

class AccionActuadorCreate(AccionActuadorBase):
    actuador_id: int

class AccionActuador(AccionActuadorBase):
    accion_id: int   
    actuador_id: int 
    model_config = ConfigDict(from_attributes=True)

# =============================================================================
# 11. SEGURIDAD (JWT) - ¡ACTUALIZADO!
# =============================================================================

class Token(BaseModel):
    """Lo que devolvemos al usuario cuando se loguea correctamente"""
    access_token: str
    token_type: str

class TokenData(BaseModel):
    """
    Los datos que extraemos DECODIFICANDO el token.
    [IMPORTANTE]: Usamos 'username' porque es el estándar de OAuth2 que implementamos.
    """
    username: Optional[str] = None

# =============================================================================
# 12. JERARQUÍA DEL DASHBOARD (Localidad -> Parcela -> Invernadero)
# =============================================================================

class InvernaderoJerarquia(BaseModel):
    invernadero_id: int
    nombre: str
    largo_m: Decimal
    ancho_m: Decimal
    cultivo: Optional[str] = None

class ParcelaJerarquia(BaseModel):
    parcela_id: int
    direccion: str
    ref_catastral: str
    num_invernaderos: int = 0
    invernaderos: List[InvernaderoJerarquia] = []

class LocalidadJerarquia(BaseModel):
    codigo_postal: str
    municipio: str
    provincia: str
    num_parcelas: int = 0
    num_invernaderos_total: int = 0
    parcelas: List[ParcelaJerarquia] = []

class JerarquiaCliente(BaseModel):
    cliente_id: int
    nombre_empresa: str
    num_localidades: int = 0
    localidades: List[LocalidadJerarquia] = []