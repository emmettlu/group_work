FROM php:7.4-apache

# Note:
# - This Dockerfile runs *inside the container build*, not on your CentOS host.
# - It uses the official Debian-based php:7.4-apache image and installs mysqli via docker-php-ext-install.
# - Your host using dnf is irrelevant as long as Docker can pull/build images.

# Install PHP mysqli extension (required by pages/files.php)
RUN set -eux; \
    docker-php-ext-install -j"$(nproc)" mysqli

# (Optional) enable common Apache modules
RUN set -eux; \
    a2enmod rewrite headers; \
    true

WORKDIR /var/www/html
