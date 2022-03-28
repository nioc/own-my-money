# This dockerfile build VueJS GUI and serve both PHP API and static files with Apache.

# Build image: `docker build -f Dockerfile --build-arg VERSION=$VERSION --build-arg GIT_COMMIT=$(git log -1 --format=%h) --build-arg BUILD_DATE=$(date -u +'%Y-%m-%dT%H:%M:%SZ') -t nioc/own-my-money:latest ../`
# Start image (see the wiki for environment variables): `docker run -d -p 80:80 --rm --name own-my-money-1 nioc/own-my-money:latest`

# VueJS GUI build stage
FROM node:lts-alpine as build-stage

# Install app dependencies
RUN apk add git
WORKDIR /app
COPY money-front-vue/package*.json ./
RUN npm install

# Copy src files (exluding files defined in .dockerignore)
COPY money-front-vue/. .
COPY .git/. .

# Build VueJS app
RUN npm run build


# Production stage
FROM php:7.4-apache as production-stage

# Install PHP modules
RUN apt-get update && \
    apt-get install -y libicu-dev libcurl4-openssl-dev libxml2-dev zlib1g-dev libpng-dev libjpeg62-turbo-dev && \
    docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install gd intl pdo pdo_mysql curl xml exif && \
    docker-php-ext-configure intl && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

EXPOSE 80

# Copy Apache & PHP conf
WORKDIR /var/www/own-my-money
COPY docker/vhost-001-money.conf /etc/apache2/sites-available/001-money.conf
COPY docker/apache.conf /etc/apache2/conf-available/custom.conf
COPY docker/docker-entrypoint.sh /docker-entrypoint.sh
COPY docker/php.ini /usr/local/etc/php/php.ini

# Enable Apache modules & site
RUN a2enmod headers rewrite && \
    a2dissite 000-default && \
    a2ensite 001-money

# Copy PHP scripts
COPY server /var/www/own-my-money/server
RUN sed -i "s|containerized = 0|containerized = 1|g" /var/www/own-my-money/server/configuration/configuration.ini

# Copy VueJS GUI files
COPY --from=build-stage /app/dist /var/www/own-my-money/front

# Set ownership to Apache user
RUN chown www-data /var/www/own-my-money/ -RL

# Set default environment variables
ENV OMM_BASE_URI=http://localhost
ENV OMM_DB_HOST=
ENV OMM_DB_USER=
ENV OMM_DB_PWD=
ENV OMM_DB_NAME=
ENV OMM_MAILER=
ENV OMM_MAIL_SENDER=
ENV OMM_HASH_KEY=
ENV OMM_SETUP=

# Tag image
ARG GIT_COMMIT=unspecified
ARG BUILD_DATE
ARG VERSION=unspecified
LABEL org.label-schema.name="own-my-money"
LABEL org.label-schema.vendor="nioc"
LABEL org.label-schema.license="AGPL-3.0-or-later"
LABEL org.label-schema.vcs-url="https://github.com/nioc/own-my-money"
LABEL org.label-schema.vcs-ref=$GIT_COMMIT
LABEL org.label-schema.build-date=$BUILD_DATE
LABEL org.label-schema.version=$VERSION
LABEL maintainer="nioc <dev@nioc.eu>"

# Update config at runtime and start Apache
ENTRYPOINT ["bash", "/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
