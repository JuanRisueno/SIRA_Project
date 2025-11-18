"""
=============================================================================
             Definición de Modelos ORM (SQLAlchemy)
=============================================================================

Propósito:
Este archivo define la capa de abstracción de la base de datos (ORM) para
el proyecto SIRA.

Cada clase definida aquí (ej. 'Cliente', 'Parcela', 'Sensor') representa
una tabla en la base de datos PostgreSQL.

Este archivo actúa como la "fuente única de la verdad" (Single Source of Truth)
para la lógica de datos de Python:
1.  Traduce las clases de Python a tablas SQL.
2.  Define las columnas, tipos de datos y restricciones (PK, FK, UK, NOT NULL).
3.  Establece las relaciones lógicas (ej. 'parcelas', 'invernaderos') que
    permiten a la API navegar por los datos usando objetos, en lugar de
    escribir consultas JOIN complejas.

Este archivo depende de 'database.py' (que gestiona la conexión) y es
utilizado por 'schemas.py' (Pydantic) y toda la lógica de la API (CRUD).
"""
# Importamos los tipos de datos y funciones necesarios de SQLAlchemy.
from sqlalchemy import (Column, Integer, String, Date, ForeignKey, DateTime, CHAR, Numeric)
from decimal import Decimal

from sqlalchemy.orm import relationship
from sqlalchemy.sql import func

# --- Definición de la Base Declarativa ---
# Importamos la 'Base' centralizada desde nuestro nuevo archivo database.py
# (El .database significa "importa desde el archivo database.py que está en la misma carpeta")
from .database import Base

# =============================================================================
# --- Definición de Modelos de Base de Datos (ORM) ---
# =============================================================================

# 1. CLIENTE
class Cliente(Base):
    """
    Representa la tabla 'cliente' en la base de datos, que almacena información 
    sobre los clientes (empresas) de la plataforma.
    """
    __tablename__ = 'cliente' # Conecta la clase Python con la tabla SQL 'cliente'.

    cliente_id: int = Column(Integer, primary_key=True)
    nombre_empresa: str = Column(String(150), nullable=False) # Nombre de la empresa (obligatorio).
    cif: str = Column(CHAR(9), unique=True, nullable=False) # Identificador fiscal único.
    email_admin: str = Column(String(150), nullable=False)
    telefono: str = Column(String(13), nullable=False) 
    persona_contacto: str = Column(String(100), nullable=False) 
    hash_contrasena: str = Column(String(255), nullable=False) # Contraseña hasheada por seguridad.

    # --- Relaciones ---
    # Define la relación uno a muchos con la tabla Parcela.
    # 'back_populates' sincroniza automáticamente ambas relaciones.
    parcelas = relationship("Parcela", back_populates="cliente")

# 2. LOCALIDAD
class Localidad(Base):
    """
    Representa la tabla 'localidad', que almacena códigos postales, municipios y provincias.
    """
    __tablename__ = 'localidad'
    
    # El CP es la PK. Se usa String porque puede empezar por '0' (Ej: 08001).
    codigo_postal: str = Column(CHAR(5), primary_key=True)
    municipio: str = Column(String(100), nullable=False)
    provincia: str = Column(String(100), nullable=False)

    # --- Relaciones ---
    # Relación uno a muchos con Parcela.
    parcelas = relationship("Parcela", back_populates="localidad")


# 3. PARCELA
class Parcela(Base):
    """
    Representa una parcela de terreno asociada a un cliente y una localidad específica.
    """
    __tablename__ = 'parcela'
    
    parcela_id: int = Column(Integer, primary_key=True)
    direccion: str = Column(String(150), nullable=False)
    ref_catastral: str = Column(CHAR(14), unique=True, nullable=False)
    
    # --- Claves Foráneas (FK) ---
    cliente_id: int = Column(Integer, ForeignKey('cliente.cliente_id'), nullable=False, index=True) 
    codigo_postal: str = Column(CHAR(5), ForeignKey('localidad.codigo_postal'), nullable=False, index=True)

    # Nota: 'index=True' en las FK crea índices en la BD para acelerar las uniones de tablas (JOINs).
    
    # --- Relaciones ---
    # Relación muchos a uno con Cliente y Localidad.
    cliente = relationship("Cliente", back_populates="parcelas")
    localidad = relationship("Localidad", back_populates="parcelas")
    # Relación uno a muchos con Invernadero.
    invernaderos = relationship("Invernadero", back_populates="parcela")
    
# 4. INVERNADERO
class Invernadero(Base):
    """
    Representa un invernadero individual dentro de una parcela, con dimensiones y cultivo asociados.
    """
    __tablename__ = 'invernadero'
    
    invernadero_id: int = Column(Integer, primary_key=True)
    fecha_plantacion: Date = Column(Date, nullable=True) # Puede ser nulo si aún no se ha plantado.
    largo_m: Decimal = Column(Numeric(8,2), nullable=False)
    ancho_m: Decimal = Column(Numeric(8,2), nullable=False)
        
    # --- Claves Foráneas ---
    parcela_id: int = Column(Integer, ForeignKey('parcela.parcela_id'), nullable=False, index=True)
    cultivo_id: int = Column(Integer, ForeignKey('cultivo.cultivo_id'), nullable=True, index=True)
    

    # --- Relaciones ---
    parcela = relationship("Parcela", back_populates="invernaderos")
    sensores = relationship("Sensor", back_populates="invernadero")
    actuadores = relationship("Actuador", back_populates="invernadero")
    cultivo = relationship("Cultivo", back_populates="invernaderos")
    recomendaciones_riego = relationship("RecomendacionRiego", back_populates="invernadero")
    
# 5. CULTIVO
class Cultivo(Base):
    """
    Define los tipos de cultivos disponibles (ej. Tomate, Lechuga) y sus nombres únicos.
    """
    __tablename__ = 'cultivo'
    
    cultivo_id: int = Column(Integer, primary_key=True)
    nombre_cultivo: str = Column(String(100), unique=True, nullable=False)
    external_api_id: str = Column(String(100), unique=True, nullable=True)
    
    # --- Relaciones ---
    invernaderos = relationship("Invernadero", back_populates="cultivo")
    parametros_optimos = relationship("ParametrosOptimos", back_populates="cultivo")

# 6. PARÁMETROS ÓPTIMOS
class ParametrosOptimos(Base):
    """
    Almacena los rangos óptimos de temperatura, humedad y necesidad hídrica para cada cultivo/fase.
    """
    __tablename__ = 'parametros_optimos'
    
    parametro_id: int = Column(Integer, primary_key=True)
    fase_crecimiento: str = Column(String(50), nullable=False)
    temp_optima_min: Decimal = Column(Numeric(5,2), nullable=False)
    temp_optima_max: Decimal = Column(Numeric(5,2), nullable=False)
    humedad_optima_min: Decimal = Column(Numeric(5,2), nullable=False)
    humedad_optima_max: Decimal = Column(Numeric(5,2), nullable=False)
    necesidad_hidrica: Decimal = Column(Numeric(8,2), nullable=False)
    
    # --- Clave Foránea ---
    cultivo_id: int = Column(Integer, ForeignKey('cultivo.cultivo_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    cultivo = relationship("Cultivo", back_populates="parametros_optimos")
    
# 7. RECOMENDACION_RIEGO
class RecomendacionRiego(Base):
    """
    Registra las recomendaciones de riego generadas por el sistema para un invernadero específico.
    """
    __tablename__ = 'recomendacion_riego'
    
    recomendacion_id: int = Column(Integer, primary_key=True)
    # Fecha y hora de la recomendación, por defecto es la hora actual del servidor.
    fecha_recomendacion: DateTime = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    cantidad_ml: Decimal = Column(Numeric(8,2), nullable=False)
    duracion_min: int = Column(Integer, nullable=False)
    razon_logica: str = Column(String(255), nullable=False) # Explicación del motivo de la recomendación.
    
    # --- Clave Foránea ---
    invernadero_id: int = Column(Integer, ForeignKey('invernadero.invernadero_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    invernadero = relationship("Invernadero", back_populates="recomendaciones_riego")

# 8. TIPO_SENSOR
class TipoSensor(Base):
    """
    Catálogo de tipos de sensores disponibles (ej. Temperatura, Humedad del suelo), incluyendo unidades.
    """
    __tablename__ = 'tipo_sensor'
    
    tipo_sensor_id: int = Column(Integer, primary_key=True)
    nombre_tipo: str = Column(String(100), unique=True, nullable=False)
    unidad_medida: str = Column(String(20), nullable=False) # Ej: 'ºC', '%Humedad', 'ppm'.
    
    # --- Relaciones ---
    sensores = relationship("Sensor", back_populates="tipo_sensor")

# 9. SENSOR
class Sensor(Base):
    """
    Representa un dispositivo sensor instalado físicamente en un invernadero.
    """
    __tablename__ = 'sensor'
    
    sensor_id: int = Column(Integer, primary_key=True)
    ubicacion_sensor: str = Column(String(100), nullable=True)
    estado_sensor: str = Column(String(20), nullable=True)
    
    # --- Claves Foráneas ---
    invernadero_id: int = Column(Integer, ForeignKey('invernadero.invernadero_id'), nullable=True, index=True)
    tipo_sensor_id: int = Column(Integer, ForeignKey('tipo_sensor.tipo_sensor_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    invernadero = relationship("Invernadero", back_populates="sensores")
    tipo_sensor = relationship("TipoSensor", back_populates="sensores")
    mediciones = relationship("Medicion", back_populates="sensor")

# 10. MEDICION
class Medicion(Base):
    """
    Almacena los datos capturados por los sensores en un momento y valor específicos.
    """
    __tablename__ = 'medicion'
    
    medicion_id: int = Column(Integer, primary_key=True)
    # Índice en fecha_hora para agilizar consultas temporales.
    fecha_hora: DateTime = Column(DateTime(timezone=True), server_default=func.now(), nullable=False, index=True)
    valor: Decimal = Column(Numeric(10,2), nullable=False)
    
    # --- Clave Foránea ---
    sensor_id: int = Column(Integer, ForeignKey('sensor.sensor_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    sensor = relationship("Sensor", back_populates="mediciones")

# 11. TIPO_ACTUADOR
class TipoActuador(Base):
    """
    Catálogo de tipos de actuadores (ej. Válvula de riego, Ventilación, Calefacción).
    """
    __tablename__ = 'tipo_actuador'
    
    tipo_actuador_id: int = Column(Integer, primary_key=True)
    nombre_tipo: str = Column(String(100), unique=True, nullable=False)
    
    # --- Relaciones ---
    actuadores = relationship("Actuador", back_populates="tipo_actuador")

# 12. ACTUADOR
class Actuador(Base):
    """
    Representa un dispositivo actuador instalado físicamente en un invernadero.
    """
    __tablename__ = 'actuador'
    
    actuador_id: int = Column(Integer, primary_key=True)
    ubicacion_actuador: str = Column(String(100), nullable=True)
    estado_actuador: str = Column(String(20), nullable=True)
    
    # --- Claves Foráneas ---
    invernadero_id: int = Column(Integer, ForeignKey('invernadero.invernadero_id'), nullable=True, index=True)
    tipo_actuador_id: int = Column(Integer, ForeignKey('tipo_actuador.tipo_actuador_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    invernadero = relationship("Invernadero", back_populates="actuadores")
    tipo_actuador = relationship("TipoActuador", back_populates="actuadores")
    acciones_actuador = relationship("AccionActuador", back_populates="actuador") 
    
# 13. ACCION_ACTUADOR
class AccionActuador(Base):
    """
    Registro histórico de cada acción realizada por un actuador (ej. "Válvula abierta 5 min").
    """
    __tablename__ = 'accion_actuador'
    
    accion_id: int = Column(Integer, primary_key=True)
    fecha_hora: DateTime = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    accion_detalle: str = Column(String(100), nullable=False)
    
    # --- Clave Foránea ---
    actuador_id: int = Column(Integer, ForeignKey('actuador.actuador_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    actuador = relationship("Actuador", back_populates="acciones_actuador") 
