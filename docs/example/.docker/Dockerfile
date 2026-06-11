FROM docker.io/bitnami/keycloak:21

ENV KEYCLOAK_HTTPS_USE_PEM=true
ENV KEYCLOAK_HTTPS_CERTIFICATE_FILE=/opt/bitnami/keycloak/certs/ssl-cert.pem
ENV KEYCLOAK_HTTPS_CERTIFICATE_KEY_FILE=/opt/bitnami/keycloak/certs/ssl-key.pem

RUN mkdir -p /opt/bitnami/keycloak/certs
RUN openssl req -x509 -newkey rsa:4096 -keyout /opt/bitnami/keycloak/certs/ssl-key.pem -out /opt/bitnami/keycloak/certs/ssl-cert.pem -sha256 -days 3650 -nodes -subj "/C=FR/ST=Île-de-France/L=Paris/O=Eyrolles/OU=Honyasan/CN=keycloak.eyrolles.com"

#COPY ./themes/my-theme /opt/bitnami/keycloak/themes/my-theme