# SIRA Project 🌱💧

> **Sistema Integral de Riego Automático (SIRA)**  
> Proyecto Fin de Grado — ASIR  
> Plataforma backend para la gestión inteligente de sensores, zonas y actuadores en invernaderos.  
> Backend en Python (FastAPI) desplegado con Docker Compose (PostgreSQL) y servido mediante Nginx.

[![Status: Draft](https://img.shields.io/badge/status-draft-orange)](#)
[![Docker](https://img.shields.io/badge/docker-enabled-blue)](#)
[![license](https://img.shields.io/badge/license-MIT-lightgrey)](#)

---

## 🧑‍💻 Stack Tecnológico

| Tecnología      | Propósito                                                  |
|-----------------|------------------------------------------------------------|
| Python / FastAPI| API REST backend de alto rendimiento                       |
| PostgreSQL      | Base de datos relacional                                   |
| Nginx           | Proxy inverso y servidor web                               |
| Docker / Compose| Despliegue y orquestación de servicios                     |
| Git / GitHub    | Control de versiones y colaboración por PR                 |
| Shell Script    | Automatización de instalaciones y servicios                |

---

## 📦 Estructura del Proyecto

```
SIRA_Project/
├── backend/            # Backend API (FastAPI)
│   ├── Dockerfile
│   ├── requirements.txt
│   └── app/
│       ├── main.py     # Entrypoint de la API
│       ├── models.py   # Modelos de datos (SQLAlchemy)
│       └── database.py # Configuración de conexión a PostgreSQL
├── nginx/
│   └── nginx.conf      # Proxy inverso
├── docker-compose.yml  # Orquestación de servicios
├── .env.example        # Ejemplo de entorno
├── install-sira-tools.sh # Script de instalación
├── docs/               # Documentación y diagramas
└── README.md
```

---

## 🚀 Puesta en marcha

### Requisitos

- Git
- Docker y Docker Compose (`docker compose` >= v2.x)
- Python 3.10+ (solo para ejecución local sin Docker)

### Instalación rápida (recomendada: Docker Compose)

1. **Clonar el repositorio**

```bash
git clone https://github.com/JuanRisueno/SIRA_Project.git
cd SIRA_Project
```

2. **Configurar variables de entorno**

```bash
cp .env.example .env
# Edita .env con tus credenciales y ajustes básicos
```

3. **Desplegar servicios Docker**

```bash
docker compose up --build -d
```

4. **Verificar servicios**

```bash
docker compose ps
docker compose logs -f
```

5. **Acceder a la API**

- Nginx: [http://localhost/](http://localhost) (según nginx.conf)
- Documentación interactiva: [Swagger](http://localhost/docs) | [ReDoc](http://localhost/redoc)

6. **Parar todo**

```bash
docker compose down
```

---

## 🧪 Desarrollo local (sin Docker)

```bash
cd backend
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

- Docs locales: [http://localhost:8000/docs](http://localhost:8000/docs) (Swagger)
- [http://localhost:8000/redoc](http://localhost:8000/redoc) (ReDoc)

---

## 🔑 Variables de entorno importantes

```env
DB_USER=usuario
DB_PASSWORD=contraseña
DB_NAME=sira_db
DATABASE_URL=postgresql://${DB_USER}:${DB_PASSWORD}@db:5432/${DB_NAME}
```
- El servicio de base de datos en `docker-compose.yml` se llama `db`.
- Estas variables deben configurarse en `.env`, usando como base `.env.example`.

---

## 🛠️ Comandos útiles

- Levantar servicios en primer plano:  `docker compose up`
- Levantar en modo detached:           `docker compose up -d`
- Ver estado:                          `docker compose ps`
- Logs del API:                        `docker compose logs -f api`
- Acceso a PostgreSQL:                 `docker exec -it sira_db psql -U ${DB_USER} -d ${DB_NAME}`

---

## 🩺 Pruebas rápidas

- **Endpoint health** (si existe):
  ```bash
  curl -sS http://localhost:8000/health || echo "API no responde"
  ```
- **Probar documentación:**
  ```bash
  curl -s http://localhost:8000/docs | head -n 20
  ```

---

## 📚 Documentación de arquitectura

- Diagrama entidad-relación: ver [`docs/Base de Datos/Modelo-Relacional_SIRA(Mermaid).txt`](docs/Base%20de%20Datos/Modelo-Relacional_SIRA(Mermaid).txt)
- Entrevista representativa y requisitos de cliente: [`docs/Base de Datos/ENTREVISTA_CLIENTE_DEV.md`](docs/Base%20de%20Datos/ENTREVISTA_CLIENTE_DEV.md)
- Checklist y guías para desarrolladores: [`docs/Flujo de Trabajo/CHECKLIST PARA INICIAR.txt`](docs/Base%20de%20Trabajo/CHECKLIST%20PARA%20INICIAR.txt)

---

## 🤝 Contribuir

1. Haz fork del proyecto
2. Crea una rama feature (`git checkout -b feature/nueva-funcionalidad`)
3. Haz commits pequeños y descriptivos
4. Realiza Push y abre un Pull Request
5. Añade tests para cambios importantes

---

## 📄 Licencia

Proyecto desarrollado como Proyecto Fin de Grado para ASIR.  
Licencia MIT — ajusta si procede.

Copyright (c) 2025 Juan Risueno

---

## 📬 Autor/es y colaboradores

- Juan Risueno (autor principal)
- Jorge Pedro López (colaborador)
- Alfonso Navarro (colaborador)
- Email de contacto: risu.profesional@gmail.com

---

## 🔜 Próximos pasos sugeridos

- Añadir badges de CI / coverage
- Crear plantillas de Issue y PR en `.github/`
- Añadir workflows (GitHub Actions: lint + tests)
- Incluir ejemplos de peticiones a la API en `examples/`
- Mejorar seguridad con JWT y CORS en FastAPI
