"""
=============================================================================
               Archivo Principal de la API (main.py)
=============================================================================

Propósito:
Este archivo es el punto de entrada principal (entrypoint) para la API REST de SIRA,
construida con el framework FastAPI. Es el archivo que 'uvicorn' ejecuta (según
el Dockerfile) para iniciar el servidor.

¿QUÉ ES ESTA API Y POR QUÉ LA NECESITAMOS?
Una API (Interfaz de Programación de Aplicaciones) actúa como un
intermediario seguro entre nuestros datos (PostgreSQL) y el mundo exterior.
Es fundamental para el proyecto SIRA por tres razones:
1.  Seguridad: Impide el acceso directo a la Base de Datos. La API actúa como
    un "filtro" que valida quién entra y qué datos envía.
2.  Interoperabilidad: Permite que diferentes sistemas (la Web de Gestión,
    la Raspberry Pi con los sensores, o una futura App móvil) se comuniquen
    con la misma base de datos usando un lenguaje común (JSON).
3.  Lógica de Negocio: Centraliza las decisiones inteligentes (ej. cuándo regar)
    en un solo lugar, independientemente de desde dónde se consulte.

Responsabilidades Técnicas de este archivo:
1.  Crear la instancia principal de la aplicación FastAPI (`app = FastAPI()`).
2.  Gestionar el ciclo de vida de la conexión a la BBDD (crear tablas si no existen).
3.  Importar y "conectar" los diferentes módulos de 'routers' (ej. los
    endpoints para Clientes, Parcelas, Sensores) a la aplicación principal.
4.  Definir y configurar 'middleware' de la aplicación, como CORS (para
    permitir peticiones desde el frontend) y la seguridad JWT.
"""

from fastapi import FastAPI
from . import models
from .database import engine, SessionLocal

# Crea la instancia principal de la aplicación FastAPI
app = FastAPI()

@app.get("/")
def read_root():
    return {"mensaje": "SIRA API está funcionando correctamente!"}

@app.get("/test-clientes/")
def test_leer_clientes():
    db = SessionLocal() # Pide una sesión a la fábrica
    try:
        # --- ESTA ES LA PRUEBA ---
        # SQLAlchemy intentará mapear models.Cliente al 10-schema.sql
        clientes = db.query(models.Cliente).all()

        # Si tiene éxito, devolverá una lista vacía
        return {"status": "OK", "clientes": clientes}

    except Exception as e:
        # Si falla (ej. los nombres no coinciden), dará un error
        return {"status": "ERROR", "detalle": str(e)}
    finally:
        db.close()