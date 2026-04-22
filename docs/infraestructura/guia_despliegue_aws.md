# ☁️ Guía de Despliegue: Infraestructura SIRA en AWS (Amazon Web Services)

Esta guía detalla los pasos para desplegar el ecosistema SIRA en una instancia **AWS EC2**, garantizando alta disponibilidad y seguridad profesional.

---

## 1. Provisión de la Instancia (EC2)

### Opción Recomendada: t3.small 🚀
Para un rendimiento profesional y fluidez total en los efectos visuales (VFX) y la API, se recomienda:
*   **Instancia**: `t3.small` (2 vCPUs, 2 GB RAM).
*   **Ventaja**: Permite gestionar los 4 contenedores de SIRA (Nginx, API, DB, Frontend) sin cuellos de botella.
*   **AMI**: **Ubuntu Server 24.04 LTS**.
*   **Almacenamiento**: 20GB EBS (gp3).

### Opción Económica: t2.micro / t3.micro 📉
Si se utiliza la capa gratuita (Free Tier), es **obligatorio** configurar un archivo Swap para evitar caídas de la base de datos:
*   **Instancia**: `t3.micro` (1 vCPU, 1 GB RAM).
*   **Configuración requerida**: Swap file de mínimo 2GB (ver Sección 4).

---

## 2. Configuración de Red (Security Groups)
Configurar el Firewall de AWS con las siguientes reglas de entrada:

| Tipo | Puerto | Protocolo | Descripción |
| :--- | :--- | :--- | :--- |
| SSH | 22 | TCP | Acceso administrativo. |
| HTTP | 80 | TCP | Tráfico web estándar. |
| HTTPS | 443 | TCP | Tráfico web seguro. |
| Custom | 8085 | TCP | **Puerto SIRA (Vital para acceso Nginx)**. |

> [!TIP]
> **IP Elástica**: Solicita y asocia una "Elastic IP" en la consola de AWS para que la dirección de tu servidor sea permanente y no cambie al reiniciar.

---

## 3. Preparación del Sistema (Host AWS)
Una vez conectado vía SSH, ejecuta los comandos de preparación:

```bash
# Actualizar sistema e instalar Docker
sudo apt update && sudo apt upgrade -y
sudo apt install -y docker.io docker-compose
sudo usermod -aG docker $USER
```

---

## 4. Optimización de Memoria (Solo para Micro Instancias)
Si has elegido una instancia `micro`, ejecuta estos comandos para crear memoria virtual:

```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

---

## 5. Despliegue de SIRA
Clona el repositorio y lanza el entorno:

```bash
git clone https://github.com/TU_USUARIO/SIRA_Project.git
cd SIRA_Project

# Configurar variables de entorno
cp .env.example .env
nano .env # Ajustar JWT_SECRET_KEY y DB_PASSWORD

# Levantar infraestructura
docker-compose up -d --build
```

---

## 6. Verificación Final
Accede a través de la IP elástica:
`http://TU_IP_AWS:8085`

> [!NOTE]
> Recuerda que en producción, el contenedor de Nginx actúa como proxy inverso, centralizando todo el tráfico seguro hacia la API y el Frontend.

---
**Documentación de Infraestructura Cloud SIRA**  
*AWS Optimized V15.0*
