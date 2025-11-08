# Importamos SQLAlchemy
from sqlalchemy import (Column, Integer, String, Decimal, Date, 
                        ForeignKey, DateTime)
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func # Para usar TIMESTAMPTZ

# --- Definición de la Base (Temporal) ---
# (Como dijimos, esto se moverá a 'backend/app/database.py' más adelante)
from sqlalchemy.ext.declarative import declarative_base
Base = declarative_base()

# 1. CLIENTE
class Cliente(Base):
    __tablename__ = 'cliente' #Conecta la clase con la tabla 'cliente' en la BD

    cliente_id = Column(Integer, primary_key=True)
    nombre_empresa = Column(String, nullable=False,) #nullable en False porque es obligatorio
    cif = Column(String, unique=True, nullable=False)
    email_admin = Column(String, nullable=False)
    telefono = Column(String, nullable=False) 
    persona_contacto = Column(String, nullable=False) 
    hash_contrasena = Column(String, nullable=False)

    # --- Relaciones---
    parcelas = relationship("Parcela", back_populates="cliente") #back_populates es el argumento que conecta dos relaciones en clases separadas, permitiendo que se sincronicen automáticamente cuando uno de los dos cambia.

# 2. LOCALIDAD
class Localidad(Base):
    __tablename__ = 'localidad'
    
    # El CP es la PK. Es un String porque puede empezar por '0' (Ej: 08001)
    codigo_postal = Column(String, primary_key=True)
    municipio = Column(String, nullable=False)
    provincia = Column(String, nullable=False)

    # --- Relaciones ---
    parcelas = relationship("Parcela", back_populates="localidad")


# 3. PARCELA
class Parcela(Base):
    __tablename__ = 'parcela'
    
    parcela_id = Column(Integer, primary_key=True)
    direccion = Column(String, nullable=False)
    ref_catastral = Column(String, unique=True, nullable=False)
    
    # --- Claves Foráneas---
    cliente_id = Column(Integer, ForeignKey('cliente.cliente_id'), nullable=False, index=True) # index=True en las FK sirve para crear índices y acelerar al máximo las uniones de tablas (JOIN).
    codigo_postal = Column(String, ForeignKey('localidad.codigo_postal'), nullable=False, index=True)

    # --- Relaciones---
    cliente = relationship("Cliente", back_populates="parcelas")
    localidad = relationship("Localidad", back_populates="parcelas")
    invernaderos = relationship("Invernadero", back_populates="parcela")
    
# 4. INVERNADERO
class Invernadero(Base):
    __tablename__ = 'invernadero'
    
    invernadero_id = Column(Integer, primary_key=True)
    fecha_plantacion = Column(Date, nullable=True)
    largo_m = Column(Decimal, nullable=False)
    ancho_m = Column(Decimal, nullable=False)
        
    # --- Claves Foráneas ---
    parcela_id = Column(Integer, ForeignKey('parcela.parcela_id'), nullable=False, index=True)
    cultivo_id = Column(Integer, ForeignKey('cultivo.cultivo_id'), nullable=True, index=True)
    

    # --- Relaciones ---
    parcela = relationship("Parcela", back_populates="invernaderos")
    sensores = relationship("Sensor", back_populates="invernadero")
    actuadores = relationship("Actuador", back_populates="invernadero")
    cultivo = relationship("Cultivo", back_populates="invernaderos")
    recomendaciones_riego = relationship("RecomendacionRiego", back_populates="invernadero")
    
# 5. CULTIVO
class Cultivo(Base):
    __tablename__ = 'cultivo'
    
    cultivo_id = Column(Integer, primary_key=True)
    nombre_cultivo = Column(String, unique=True, nullable=False)
    
    # --- Relaciones ---
    invernaderos = relationship("Invernadero", back_populates="cultivo")
    parametros_optimos = relationship("ParametrosOptimos", back_populates="cultivo")

# 6. PARÁMETROS ÓPTIMOS
class ParametrosOptimos(Base):
    __tablename__ = 'parametros_optimos'
    
    parametro_id = Column(Integer, primary_key=True)
    fase_crecimiento = Column(String, nullable=False)
    temp_optima_min = Column(Decimal, nullable=False)
    temp_optima_max = Column(Decimal, nullable=False)
    humedad_optima_min = Column(Decimal, nullable=False)
    humedad_optima_max = Column(Decimal, nullable=False)
    necesidad_hidrica = Column(Decimal, nullable=False)
    
    # --- Clave Foránea ---
    cultivo_id = Column(Integer, ForeignKey('cultivo.cultivo_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    cultivo = relationship("Cultivo", back_populates="parametros_optimos")
    
# 7. RECOMENDACION_RIEGO
class RecomendacionRiego(Base):
    __tablename__ = 'recomendacion_riego'
    
    recomendacion_id = Column(Integer, primary_key=True)
    fecha_recomendacion = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    cantidad_ml = Column(Decimal, nullable=False)
    duracion_min = Column(Integer, nullable=False)
    razon_logica = Column(String, nullable=False)
    
    # --- Clave Foránea ---
    invernadero_id = Column(Integer, ForeignKey('invernadero.invernadero_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    invernadero = relationship("Invernadero", back_populates="recomendaciones_riego")

# 8. TIPO_SENSOR
class TipoSensor(Base):
    __tablename__ = 'tipo_sensor'
    
    tipo_sensor_id = Column(Integer, primary_key=True)
    nombre_tipo = Column(String, unique=True, nullable=False)
    unidad_medida = Column(String, nullable=False)
    
    # --- Relaciones ---
    sensores = relationship("Sensor", back_populates="tipo_sensor")

# 9. SENSOR
class Sensor(Base):
    __tablename__ = 'sensor'
    
    sensor_id = Column(Integer, primary_key=True)
    
    # --- Claves Foráneas ---
    invernadero_id = Column(Integer, ForeignKey('invernadero.invernadero_id'), nullable=False, index=True)
    tipo_sensor_id = Column(Integer, ForeignKey('tipo_sensor.tipo_sensor_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    invernadero = relationship("Invernadero", back_populates="sensores")
    tipo_sensor = relationship("TipoSensor", back_populates="sensores")
    mediciones = relationship("Medicion", back_populates="sensor")

# 10. MEDICION
class Medicion(Base):
    __tablename__ = 'medicion'
    
    medicion_id = Column(Integer, primary_key=True)
    fecha_hora = Column(DateTime(timezone=True), server_default=func.now(), nullable=False, index=True) #Excepción del index=True ya que se necesitará crear un índice en la fecha y hora de la tabla medición para agilizar las futuras consultas
    valor = Column(Decimal, nullable=False)
    
    # --- Clave Foránea ---
    sensor_id = Column(Integer, ForeignKey('sensor.sensor_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    sensor = relationship("Sensor", back_populates="mediciones")

# 11. TIPO_ACTUADOR
class TipoActuador(Base):
    __tablename__ = 'tipo_actuador'
    
    tipo_actuador_id = Column(Integer, primary_key=True)
    nombre_tipo = Column(String, unique=True, nullable=False)
    
    # --- Relaciones ---
    actuadores = relationship("Actuador", back_populates="tipo_actuador")

# 12. ACTUADOR
class Actuador(Base):
    __tablename__ = 'actuador'
    
    actuador_id = Column(Integer, primary_key=True)
    
    # --- Claves Foráneas ---
    invernadero_id = Column(Integer, ForeignKey('invernadero.invernadero_id'), nullable=False, index=True)
    tipo_actuador_id = Column(Integer, ForeignKey('tipo_actuador.tipo_actuador_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    invernadero = relationship("Invernadero", back_populates="actuadores")
    tipo_actuador = relationship("TipoActuador", back_populates="actuadores")
    actuadores = relationship("AccionActuador", back_populates="actuador")
    
# 13. ACCION_ACTUADOR
class AccionActuador(Base):
    __tablename__ = 'accion_actuador'
    
    accion_id = Column(Integer, primary_key=True)
    fecha_hora = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    accion_detalle = Column(String, nullable=False)
    
    # --- Clave Foránea ---
    actuador_id = Column(Integer, ForeignKey('actuador.actuador_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    actuador = relationship("Actuador", back_populates="actuadores")