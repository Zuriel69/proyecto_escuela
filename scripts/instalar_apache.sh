#!/bin/bash

# Actualizar repositorios
sudo apt update

# Instalar Apache
sudo apt install apache2 -y

# Iniciar y habilitar el servicio
sudo systemctl start apache2
sudo systemctl enable apache2
