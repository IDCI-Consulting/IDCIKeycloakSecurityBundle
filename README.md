IDCI Keycloak Security Bundle
=============================

This Symfony bundle is an alternative solution to FOSUserBundle, working with keycloak.

## Installation

With composer:

```
$ composer require idci/keycloak-security-bundle
```

## Configuration

If you want to set up keycloak locally you can download it [here](https://www.keycloak.org/downloads.html) and follow instructions from [the official documentation](https://www.keycloak.org/docs/3.2/server_installation/topics/installation.html). In case that you want to use keycloak in docker go directly to [configuration for Docker](#docker).

### Bundle configuration

#### Basic

In case of you already have keycloak running locally on your machine or is running remotely but without proxy, here is the default configuration you should use:

```yaml
# config/packages/idci_keycloak_security.yaml
idci_keycloak_security:
    server_url: 'http://localhost:8080/auth' # your accessible keycloak url
    # server_url: 'http://keycloak.example.com/auth' # example with public url
    realm: 'MyRealm'
    client_id: 'my-client'
    client_secret: '21d4cc5c-9ed6-4bf8-8528-6d659b66f216'
    default_target_path: 'home' # The route name you will be redirected to after sign in
```

#### Docker

If you want to use keycloak in docker you can base your stack on this [sample](./Resources/docs/example).

Here is a stack example configuration for docker swarm:

```yaml
# config/packages/idci_keycloak_security.yaml
idci_keycloak_security:
    server_public_url: 'http://keycloak.docker/auth' # your keycloak url accessible via your navigator
    server_private_url: 'http://keycloak:8080/auth' # your keycloak container reference in the network
    realm: 'MyRealm'
    client_id: 'my-client'
    client_secret: '21d4cc5c-9ed6-4bf8-8528-6d659b66f216'
    default_target_path: 'home' # The route you will be redirected to after sign in
```

Make sure that your php container in the container is attached to a network with keycloak, otherwise it will not be able to resolve "http://keycloak:8080/auth" and the public_server_url must be accessible through the port 80 because keycloak verify the issuer.

### Route configuration

Create a new file in ```config/routes/``` to load pre configured bundle routes.

```yaml
# config/routes/idci_keycloak_security.yaml
IDCIKeycloakSecurityBundle:
    resource: "@IDCIKeycloakSecurityBundle/Resources/config/routing.yaml"
    prefix: /
```

### Symfony security configuration

To link keycloak with symfony you must change the default security configuration in symfony.

Here is a simple configuration that restrict access to ```/admin/*``` routes only to user with role "ROLE_ADMIN" :

```yaml
# config/packages/security.yaml
imports:
    # import Keycloak security provider
    - { resource: '@IDCIKeycloakSecurityBundle/Resources/config/security.yaml' }

security:
    enable_authenticator_manager: true
    firewalls:

        # Authorize everyone to try connecting (this route is imported from our bundle routing configuration)
        auth_connect:
            pattern: ^/auth/connect/.*
            security: false

        # This bundle is using security guard provided by symfony
        # Login form authentication
        secured_area:
            pattern: ^/admin
            provider: idci_keycloak_security_provider
            custom_authenticators:
                - IDCI\Bundle\KeycloakSecurityBundle\Security\Authenticator\KeycloakAuthenticator
            entry_point: IDCI\Bundle\KeycloakSecurityBundle\Security\AuthenticationEntryPoint

        # Bearer token authentication
        api:
            pattern: ^/api
            provider: idci_keycloak_bearer_security_provider
            custom_authenticators:
                - IDCI\Bundle\KeycloakSecurityBundle\Security\Authenticator\KeycloakBearerAuthenticator

    role_hierarchy:
        ROLE_ADMIN: ROLE_USER

    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/api, roles: ROLE_API }
```

## Keycloak configuration

If you need help to use keycloak because it is the first time you work on it, we've made a little tutorial step by step describing a basic configuration of a keycloak realm that you can found [here](./Resources/docs/keycloak-help-guide.md)

## TODO

- Install bundle configuration with flex recipe.
