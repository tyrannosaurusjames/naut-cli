FROM php:7.3-cli-stretch as naut-cli-phar-builder
RUN apt-get update && apt-get install -y unzip
RUN echo 'phar.readonly = 0' > ${PHP_INI_DIR}/conf.d/phar-create.ini
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY . /app
WORKDIR /app
RUN bash bin/build-phar.sh

FROM php:7.3-cli-alpine
COPY --from=naut-cli-phar-builder /app/dist/naut-cli.phar /app/naut-cli
WORKDIR /app
RUN chmod +x /app/naut-cli
ENTRYPOINT ["/app/naut-cli"]
