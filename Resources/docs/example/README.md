# Keycloak

Keycloak Docker stack


## Requirement

You must have docker in swarm mode to run this docker stack.

This demo stack require "Traefik" to be running on your host.
Follow the documentation [here](./traefik/README.md)

## Build local image

```sh
$ docker build -t local/keycloak:latest -f .docker/Dockerfile .
```
or you can use the Makefile:
```sh
$ make build-image
```

## Start

To start the keycloak docker stack:
```sh
$ docker stack deploy -c docker-compose.yml keycloak
```
or you can use the Makefile:
```sh
$ make deploy
```

## Stop

To stop the keycloak docker stack:
```sh
$ docker stack rm keycloak
```
or you can use the Makefile:
```sh
$ make undeploy
```