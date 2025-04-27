FROM wordpress:latest

# Instalar las dependencias necesarias y la extensión pgsql
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql

# Limpiar caché de apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/* 