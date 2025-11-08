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
    __tablename__ = 'cliente' 

    cliente_id = Column(Integer, primary_key=True, index=True)
    nombre_empresa = Column(String, nullable=False, index=True) 
    cif = Column(String, unique=True, nullable=False, index=True)
    email_admin = Column(String, unique=True, nullable=False, index=True)
    telefono = Column(String, nullable=False, index=True) 
    persona_contacto = Column(String, nullable=False, index=True) 
    hash_contrasena = Column(String, nullable=False, index=True)

    # --- Relaciones---
    parcelas = relationship("Parcela", back_populates="cliente")


# 2. LOCALIDAD
class Localidad(Base):
    __tablename__ = 'localidad'
    
    # El CP es la PK. Es un String porque puede empezar por '0' (Ej: 08001)
    codigo_postal = Column(String, primary_key=True, index=True)
    municipio = Column(String, nullable=False, index=True)
    provincia = Column(String, nullable=False, index=True)

    # --- Relaciones ---
    parcelas = relationship("Parcela", back_populates="localidad")


# 3. PARCELA
class Parcela(Base):
    __tablename__ = 'parcela'
    
    parcela_id = Column(Integer, primary_key=True, index=True)
    direccion = Column(String, nullable=False, index=True)
    ref_catastral = Column(String, unique=True, nullable=False, index=True)
    
    # --- Claves Foráneas---
    cliente_id = Column(Integer, ForeignKey('cliente.cliente_id'), nullable=False, index=True)
    codigo_postal = Column(String, ForeignKey('localidad.codigo_postal'), nullable=False, index=True)

    # --- Relaciones---
    cliente = relationship("Cliente", back_populates="parcelas")
    localidad = relationship("Localidad", back_populates="parcelas")
    invernaderos = relationship("Invernadero", back_populates="parcela")
    
# 4. INVERNADERO
class Invernadero(Base):
    __tablename__ = 'invernadero'
    
    invernadero_id = Column(Integer, primary_key=True, index=True)
    fecha_plantacion = Column(Date, nullable=True, index=True)
    largo_m = Column(Decimal, nullable=False, index=True)
    ancho_m = Column(Decimal, nullable=False, index=True)
        
    # --- Clave Foránea ---
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
    
    cultivo_id = Column(Integer, primary_key=True, index=True)
    nombre_cultivo = Column(String, unique=True, nullable=False, index=True)
    
    # --- Relaciones ---
    invernaderos = relationship("Invernadero", back_populates="cultivo")
    parametros_optimos = relationship("ParametrosOptimos", back_populates="cultivo")

# 6. PARÁMETROS ÓPTIMOS
class ParametrosOptimos(Base):
    __tablename__ = 'parametros_optimos'
    
    parametro_id = Column(Integer, primary_key=True, index=True)
    fase_crecimiento = Column(String, nullable=False, index=True)
    temp_optima_min = Column(Decimal, nullable=False, index=True)
    temp_optima_max = Column(Decimal, nullable=False, index=True)
    humedad_optima_min = Column(Decimal, nullable=False, index=True)
    humedad_optima_max = Column(Decimal, nullable=False, index=True)
    necesidad_hidrica = Column(Decimal, nullable=False, index=True)
    
    # --- Clave Foránea ---
    cultivo_id = Column(Integer, ForeignKey('cultivo.cultivo_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    cultivo = relationship("Cultivo", back_populates="parametros_optimos")
    
# 7. RECOMENDACION_RIEGO
class RecomendacionRiego(Base):
    __tablename__ = 'recomendacion_riego'
    
    recomendacion_id = Column(Integer, primary_key=True, index=True)
    fecha_recomendacion = Column(DateTime(timezone=True), server_default=func.now(), nullable=False, index=True)
    cantidad_ml = Column(Decimal, nullable=False, index=True)
    duracion_min = Column(Integer, nullable=False, index=True)
    razon_logica = Column(String, nullable=False, index=True)
    
    # --- Clave Foránea ---
    invernadero_id = Column(Integer, ForeignKey('invernadero.invernadero_id'), nullable=False, index=True)
    
    # --- Relaciones ---
    invernadero = relationship("Invernadero", back_populates="recomendaciones_riego")