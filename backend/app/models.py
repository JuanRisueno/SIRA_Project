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
    nombre_empresa = Column(String) 
    cif = Column(String, unique=True, index=True)
    email_admin = Column(String, unique=True, index=True)
    telefono = Column(String, nullable=True) 
    persona_contacto = Column(String, nullable=True) 
    hash_contrasena = Column(String)

    # --- Relaciones (ACTUALIZADO) ---
    # (1:N) Un Cliente tiene muchas Parcelas
    parcelas = relationship("Parcela", back_populates="cliente")


# 2. LOCALIDAD
class Localidad(Base):
    __tablename__ = 'localidad'
    
    # El CP es la PK. Es un String porque puede empezar por '0' (Ej: 08001)
    codigo_postal = Column(String, primary_key=True, index=True)
    municipio = Column(String, unique=True, index=True)
    provincia = Column(String, unique=True, index=True)

    # --- Relaciones ---
    # (1:N) Una Localidad (CP) puede tener muchas Parcelas
    parcelas = relationship("Parcela", back_populates="localidad")


# 3. PARCELA (ANTES 'FINCA')
# E-R: PARCELA
class Parcela(Base):
    __tablename__ = 'parcela'
    
    parcela_id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String)
    direccion = Column(String)
    
    # --- Claves Foráneas (ACTUALIZADO) ---
    cliente_id = Column(Integer, ForeignKey('cliente.cliente_id')) [cite: 19]
    # Apuntamos a la PK de la tabla Localidad
    codigo_postal = Column(String, ForeignKey('localidad.codigo_postal')) [cite: 19]

    # --- Relaciones (ACTUALIZADO) ---
    # (N:1) Muchas Parcelas pertenecen a un Cliente
    cliente = relationship("Cliente", back_populates="parcelas")
    # (N:1) Muchas Parcelas se ubican en una Localidad
    localidad = relationship("Localidad", back_populates="parcelas")
    
    # (1:N) Una Parcela tiene muchos Invernaderos
    invernaderos = relationship("Invernadero", back_populates="parcela")