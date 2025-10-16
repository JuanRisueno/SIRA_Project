# SIRA Project 🌱💧

> **Sistema Integral de Riego Automático (SIRA)** - Proyecto Fin de Grado para el ciclo de Administración de Sistemas Informáticos en Red (ASIR).

Implementación completa de la infraestructura backend para un sistema de gestión de riego automatizado para invernaderos.

## 🛠️ Stack Tecnológico

El proyecto utiliza un stack tecnológico moderno y estándar en la industria, desplegado íntegramente sobre contenedores Docker.

### Infraestructura y DevOps

| Tecnología | Propósito |
|------------|-----------|
| Git / GitHub | Control de versiones y colaboración mediante Pull Requests |
| Docker / Docker Compose | Entorno de desarrollo aislado y reproducible |
| Ubuntu Server 24.04 LTS | SO base para contenedores |

### Backend y Base de Datos

| Tecnología | Propósito |
|------------|-----------|
| Python / FastAPI | API de alto rendimiento |
| PostgreSQL | Base de datos relacional |
├── api/                # Código de FastAPI
├── docker-compose.yml
Una vez ejecutando el proyecto, visita:

```env
1. Fork del proyecto
4. Push a la rama (`git push origin feature/nueva-caracteristica`)
## 📄 Licencia
# SIRA — Sistema Integral de Riego Automático

[![status: draft](https://img.shields.io/badge/status-draft-orange)](#) [![docker](https://img.shields.io/badge/docker-enabled-blue)](#) [![license](https://img.shields.io/badge/license-CC--BY--NC--SA-lightgrey)](#)

Descripción
-----------
SIRA (Sistema Integral de Riego Automático) es un proyecto para la gestión y automatización de riego en invernaderos. Backend en Python (FastAPI), desplegado con Docker Compose y proxy mediante Nginx.

Características principales
- API REST para gestionar sensores y actuadores.
- Documentación automática de la API (Swagger / ReDoc).
- Despliegue por contenedores (Docker Compose).
- Pensado como proyecto final para ASIR.

Estado
------
Proyecto en desarrollo. Ajusta la configuración y credenciales antes de producción.

Quickstart — inicio rápido
--------------------------
Requisitos
- Git
- Docker (>=20.x) y Docker Compose (v2 recomendado)

Clonar y levantar con Docker Compose
```bash
git clone https://github.com/<tu-usuario>/SIRA_Project.git
cd SIRA_Project
docker compose up --build -d
```

Configuración de entorno
- Copia el ejemplo de variables (si existe):
```bash
cp .env.example .env
# Edita .env con tus credenciales/ajustes
```
- Variables típicas:
```
POSTGRES_USER=tu_usuario
POSTGRES_PASSWORD=tu_password
POSTGRES_DB=sira_db
DATABASE_URL=postgresql://tu_usuario:tu_password@sira_db:5432/sira_db
```

Verificar que todo funciona
```bash
# Revisar contenedores
```bash

# Ver logs del backend (ajusta el nombre del servicio si difiere)
# Revisar contenedores

# Comprobar endpoint raíz o docs
curl http://localhost/        # o
curl http://localhost/docs    # swagger UI (si está expuesto en la raíz)
```

Arquitectura y stack
--------------------
- Backend: Python + FastAPI (archivo principal en `backend/app/main.py`)
- Servidor web / reverse-proxy: Nginx (`nginx/nginx.conf`)
- Orquestación local: Docker Compose (`docker-compose.yml`)
- Dependencias backend: `backend/requirements.txt`

Estructura del proyecto
```
SIRA_Project/
├─ backend/
│  ├─ Dockerfile
│  ├─ requirements.txt
│  └─ app/
│     └─ main.py
├─ nginx/
│  └─ nginx.conf
├─ docker-compose.yml
└─ README.md
```

API docs
--------
- Swagger UI: http://localhost/docs
- ReDoc: http://localhost/redoc

Comandos útiles
---------------
```bash
# Levantar en foreground (útil en dev)
docker compose ps

# Levantar en background


# Parar y borrar contenedores/recursos
# Ver logs del backend (ajusta el nombre del servicio si difiere)

# Reiniciar un servicio específico
docker logs -f backend_api

# Ver logs

```

Desarrollo local (sin Docker)
-----------------------------
Si quieres ejecutar el backend localmente (útil para debugging):
1. Entrar en `backend/`
2. Crear y activar virtualenv, instalar dependencias:
```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

Buenas prácticas
----------------
- Mantener las variables sensibles fuera del repositorio (usar `.env` y/o secretos de CI).
- Usar ramas feature y PRs para cambios colaborativos.
- Añadir tests unitarios y CI (GitHub Actions) en futuros pasos.

Contribuir
----------
1. Haz fork del repositorio.
2. Crea una rama: `git checkout -b feature/nombre`.
3. Haz commits claros y descriptivos.
4. Envía un Pull Request.

Licencia
--------
Indica aquí la licencia del proyecto (por ejemplo MIT, o una nota que es Proyecto Fin de Grado si procede). Ejemplo:
```
Copyright (c) 2025 Juan Risueno.
Licencia: MIT (o la que decidas)
```

Contacto / Autor
----------------
- Autor: Juan Risueno (ajusta si quieres otro contacto)
- Email: tu.email@ejemplo.com (opcional)

Notas y próximos pasos sugeridos
-------------------------------
- Añadir un badge de CI si añades GitHub Actions.
- Si el servicio real usa PostgreSQL, incluir instrucciones para backups y restauración.
- Añadir sección de endpoints clave (ej.: ejemplo de POST para crear una zona de riego).
- Si quieres, preparo el PR con este README y un pequeño archivo `.github/ISSUE_TEMPLATE` o `workflows` básico.

---

Este README fue actualizado automáticamente por la solicitud del mantenedor.
# Comprobar endpoint raíz o docs
curl http://localhost/        # o
curl http://localhost/docs    # swagger UI (si está expuesto en la raíz)
```

Arquitectura y stack
--------------------
- Backend: Python + FastAPI (archivo principal en `backend/app/main.py`)
- Servidor web / reverse-proxy: Nginx (`nginx/nginx.conf`)
- Orquestación local: Docker Compose (`docker-compose.yml`)
- Dependencias backend: `backend/requirements.txt`

Estructura del proyecto
```
SIRA_Project/
├─ backend/
│  ├─ Dockerfile
│  ├─ requirements.txt
│  └─ app/
│     └─ main.py
├─ nginx/
│  └─ nginx.conf
├─ docker-compose.yml
└─ README.md
```

API docs
--------
- Swagger UI: http://localhost/docs
- ReDoc: http://localhost/redoc

Comandos útiles
---------------
```bash
# Levantar en foreground (útil en dev)
docker compose up

# Levantar en background
docker compose up -d

# Parar y borrar contenedores/recursos
docker compose down

# Reiniciar un servicio específico
docker compose restart backend_api

<<<<<<< HEAD
# El usuario es el que tengas configurado en el archivo .env
=======
# Ver logs
docker compose logs -f backend_api
```

Desarrollo local (sin Docker)
-----------------------------
Si quieres ejecutar el backend localmente (útil para debugging):
1. Entrar en `backend/`
2. Crear y activar virtualenv, instalar dependencias:
```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

Buenas prácticas
----------------
- Mantener las variables sensibles fuera del repositorio (usar `.env` y/o secretos de CI).
- Usar ramas feature y PRs para cambios colaborativos.
- Añadir tests unitarios y CI (GitHub Actions) en futuros pasos.

Contribuir
----------
1. Haz fork del repositorio.
2. Crea una rama: `git checkout -b feature/nombre`.
3. Haz commits claros y descriptivos.
4. Envía un Pull Request.

Licencia
--------
Indica aquí la licencia del proyecto (por ejemplo MIT, o una nota que es Proyecto Fin de Grado si procede). Ejemplo:
```
Copyright (c) 2025 Juan Risueno.
Licencia: MIT (o la que decidas)
```

Contacto / Autor
----------------
- Autor: Juan Risueno (ajusta si quieres otro contacto)
- Email: tu.email@ejemplo.com (opcional)

Notas y próximos pasos sugeridos
-------------------------------
- Añadir un badge de CI si añades GitHub Actions.
- Si el servicio real usa PostgreSQL, incluir instrucciones para backups y restauración.
- Añadir sección de endpoints clave (ej.: ejemplo de POST para crear una zona de riego).
- Si quieres, preparo el PR con este README y un pequeño archivo `.github/ISSUE_TEMPLATE` o `workflows` básico.

---

Este README fue actualizado automáticamente por la solicitud del mantenedor.
>>>>>>> f8145c4 (docs: mejorar README para GitHub (estructura y quickstart))
