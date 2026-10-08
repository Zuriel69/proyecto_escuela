#!/bin/bash

# Actualizar repositorios
sudo apt update

# Instalar PHP y los módulos necesarios para que funcione con Apache y MariaDB
sudo apt install php libapache2-mod-php php-mysql -y
