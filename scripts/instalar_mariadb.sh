#!/bin/bash

# Actualizar repositorios
sudo apt update

# Instalar MariaDB
sudo apt install mariadb-server -y

# Iniciar y habilitar el servicio
sudo systemctl start mariadb
sudo systemctl enable mariadb
