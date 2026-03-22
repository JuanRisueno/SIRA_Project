# Guía de Despliegue en Servidor de Producción (VPS)

Este documento describe cómo instalar todo el ecosistema y la infraestructura de **SIRA** en un servidor remoto real (VPS - Virtual Private Server, ej. DigitalOcean, AWS EC2, Linode, o un Servidor / Raspberry de la granja) partiendo de cero.

Se asume la conexión a un sistema Linux limpio (deb o rpm preferibles, aquí se usa un Ubuntu/Debian Standard).

## 1. Requisitos y Conexión Inicial
Conéctate al servidor vía SSH proporcionado por el proveedor:
```bash
ssh root@IP_DEL_SERVIDOR
```

Actualiza el índice local de repositorios e instala utilidades vitales de código:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install git -y
```

## 2. Instalación de Docker y Composer en el Host Físico
Es necesario el motor oficial de Docker. Descarga e importa las claves PGP correctas del repositorio oficial:
```bash
sudo apt install -y ca-certificates curl gnupg
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

# Añadir el tag oficial a Apt sources
echo \
  "deb [arch="$(dpkg --print-architecture)" signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  "$(. /etc/os-release && echo "$VERSION_CODENAME")" stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Instalar infraestructura completa
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

## 3. Clonación Segura del Proyecto Core
Bájate la última *Release* o la rama *main* del repositorio a una ruta predefinida y organizada del host (por ejemplo `/opt/sira`):
```bash
cd /opt
sudo git clone https://github.com/TUREPOSITORIO/SIRA_Project.git
sudo chown -R $USER:$USER SIRA_Project/
cd SIRA_Project
```

## 4. Configuración del Entorno Protegido
En producción es estricto tener nuevas contraseñas con mucha entropía. Jamás usar `usuario/contraseña`.

```bash
cp .env.example .env
nano .env
```
*(Se configuran todas las variables, tales como `DB_PASSWORD`, `SECRET_KEY` del JWT, y `TREFLE_API_KEY` requeridas por la red).*

## 5. Arranque en Frío Permanente
Hacemos la construcción oficial de los contenedores usando el comando desacoplado:
```bash
docker compose up --build -d
```

Validamos visualmente que la orquestación ha sucedido existosamente:
```bash
docker compose ps
docker compose logs -f api
```

✅ **Punto Limpio Establecido**: En este instante, la infraestructura es accesible desde cualquier navegador escribiendo en él la IP de la máquina o apuntando un dominio en los DNS. Dado que la orquestación de compose (`docker-compose.yml`) tiene las flags protectoras `restart: unless-stopped` configuradas, en caso de cuelgue, corte de luz general o reinicio drástico del VPS físico, el demonio Docker despertará a SIRA instantáneamente en el siguiente arranque sin interacción humana del Sysadmin.
