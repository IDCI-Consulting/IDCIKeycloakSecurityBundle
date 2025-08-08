# Traefik

Traefik Docker stack

## Requirements

This docker stack require docker running in swarm mode.
You have to create an overlay network before running the stack
```sh
$ docker network create --scope swarm --driver overlay traefik
```

## Start the Docker stack
```sh
$ docker stack deploy -c docker-compose.yml traefik
```

## Stop the Docker stack
```sh
$ docker stack rm traefik
```

## Web GUI
A web GUI is available with `http://[@IP]:8080`