#!/bin/bash

# -----------------------------------------------------------------------------
# Script de Instalación de Herramientas para SIRA_Project
#
# Este script instala:
# 1. Git (Control de versiones)
# 2. Docker Engine (Plataforma de contenedores)
# 3. Docker Compose (Plugin de orquestación)
# 4. GitHub CLI (Herramienta para gestionar GitHub)
#
# Prerrequisito: Ejecutar 'sudo apt update && sudo apt upgrade -y' antes.
# -----------------------------------------------------------------------------

# Para el script si cualquier comando falla
set -e

echo "--- Instalando paquetes de requisitos previos ---"
sudo apt install ca-certificates curl gpg -y

# --- Instalación de Docker y Docker Compose (Método Oficial) ---

echo "--- Añadiendo la clave GPG oficial de Docker ---"
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

echo "--- Añadiendo el repositorio oficial de Docker ---"
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# --- Instalación de GitHub CLI (gh) (Método Oficial) ---

echo "--- Añadiendo la clave GPG oficial de GitHub CLI ---"
curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg
sudo chmod go+r /usr/share/keyrings/githubcli-archive-keyring.gpg

echo "--- Añadiendo el repositorio oficial de GitHub CLI ---"
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages \
  stable main" | sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null

# --- Instalación Final ---

echo "--- Actualizando la lista de paquetes (con los nuevos repos) ---"
sudo apt update

echo "--- Instalando Git, Docker, Docker Compose y GitHub CLI ---"
sudo apt install git gh docker-ce docker-ce-cli containerd.io docker-compose-plugin -y

# --- Configuración Post-Instalación (MUY IMPORTANTE) ---

echo "--- Añadiendo el usuario actual al grupo 'docker' ---"
# Esto permite ejecutar comandos de Docker sin necesitar 'sudo'
sudo usermod -aG docker $USER

echo "--- ¡Instalación completada! ---"
echo "IMPORTANTE: Para que los permisos de Docker surtan efecto, debes:"
echo "1. Salir de la sesión (log out) y volver a entrar."
echo "O"
echo "2. Reiniciar la máquina virtual ('sudo reboot')."
