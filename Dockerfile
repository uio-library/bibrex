FROM php:7.3-fpm

# VOLUME /www

RUN apt-get update && apt-get install -y --no-install-recommends \
	# Postgres
	postgresql-9.6 \
	libpq-dev \
	# ImageMagick
	imagemagick \
	libmagickwand-dev \
	# Zip
	zlib1g-dev \
	libzip-dev \
	unzip

RUN docker-php-ext-install zip

RUN docker-php-ext-install pdo_pgsql

RUN pecl install imagick \
    && docker-php-ext-enable imagick

RUN php -i

COPY scripts/get_composer.sh /var/www/html
RUN ./get_composer.sh

COPY composer.json /var/www/html
COPY database /var/www/html
COPY app /var/www/html
COPY artisan /var/www/html
RUN php composer.phar install


# RUN php artisan install

#RUN php artisan migrate --env=testing --force

# RUN apt-get -y install locales
# RUN echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen
# RUN echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen
# RUN echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen
# RUN echo "fi_FI.UTF-8 UTF-8" >> /etc/locale.gen
# RUN echo "sv_SE.UTF-8 UTF-8" >> /etc/locale.gen
# RUN locale-gen

# RUN a2enmod rewrite
# RUN docker-php-ext-install gettext
