IDCI Keycloak Security Bundle
=============================

This Symfony bundle is an alternative solution to FOSUserBundle, working with keycloak.

For symfony 2/3/4 use version 1.2.0
For symfony 5+ use version 2.0.0 or +

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
    server_url: '%env(resolve:KEYCLOAK_SERVER_BASE_URL)%'
    server_public_url: '%env(resolve:KEYCLOAK_SERVER_PUBLIC_BASE_URL)%'
    server_private_url: '%env(resolve:KEYCLOAK_SERVER_PRIVATE_BASE_URL)%'
    realm: '%env(resolve:KEYCLOAK_REALM)%'
    client_id: '%env(resolve:KEYCLOAK_CLIENT_ID)%'
    client_secret: '%env(resolve:KEYCLOAK_CLIENT_SECRET)%'
    default_target_route_name: 'app_home'
    ssl_verification: true
```

#### Docker

If you want to use keycloak in docker you can base your stack on this [sample](./Resources/docs/example).

Here is a stack example configuration for docker swarm:

```yaml
# config/packages/idci_keycloak_security.yaml
idci_keycloak_security:
    server_public_url: 'http://keycloak.docker' # your keycloak url accessible via your navigator
    server_private_url: 'http://keycloak:8080' # your keycloak container reference in the network
    realm: 'MyRealm'
    client_id: 'my-client'
    client_secret: '21d4cc5c-9ed6-4bf8-8528-6d659b66f216'
    default_target_route_name: 'home' # The route you will be redirected to after sign in
```

Make sure that your php container in the container is attached to a network with keycloak, otherwise it will not be able to resolve "http://keycloak:8080/auth" and the public_server_url must be accessible through the port 80 because keycloak verify the issuer.

NOTE: The keycloak api endpoint as change, so if you used an old version, add the `/auth` to you url:
```yaml
idci_keycloak_security:
    server_public_url: 'http://keycloak.docker/auth'
    server_private_url: 'http://keycloak:8080/auth'
```

### Route configuration

Create a new file in ```config/routes/``` to load pre configured bundle routes.

```yaml
# config/routes/idci_keycloak_security.yaml
IDCIKeycloakSecurityBundle:
    resource: "@IDCIKeycloakSecurityBundle/Resources/config/routing.yaml"
    prefix: /
```

This will add the following routes to your application:

```
idci_keycloak_security_auth_connect       => /auth/connect/keycloak
idci_keycloak_security_auth_connect_check => /auth/connect-check/keycloak
idci_keycloak_security_auth_logout        => /auth/logout
idci_keycloak_security_auth_account       => /auth/account
```

### Symfony security configuration

To link keycloak with symfony you must configure your application security file.

Here is a simple configuration that restrict access to ```/*``` routes only to user with roles "ROLE_USER" or "ROLE_ADMIN" :

```yaml
# config/packages/security.yaml
imports:
    # Import Keycloak security providers
    - { resource: '@IDCIKeycloakSecurityBundle/Resources/config/security.yaml' }

security:

    enable_authenticator_manager: true
    firewalls:

        # This route create the OAuth 2 "User Authorization Request" and must be accessible for unauthenticated users
        auth_connect:
            pattern: /auth/connect/keycloak
            security: false

        # Here is an example to protect your application (API) using OAuth 2 Client Credentials Flow (JWT with Bearer token authentication)
        api:
            pattern: ^/api
            provider: idci_keycloak_bearer_security_provider
            entry_point: IDCI\Bundle\KeycloakSecurityBundle\Security\EntryPoint\BearerAuthenticationEntryPoint
            custom_authenticators:
                - IDCI\Bundle\KeycloakSecurityBundle\Security\Authenticator\KeycloakBearerAuthenticator

        # Here is an exemple to protect your application (UI) using OAuth 2 Authorization Code Flow
        secured_area:
            pattern: ^/
            provider: idci_keycloak_security_provider
            entry_point: IDCI\Bundle\KeycloakSecurityBundle\Security\EntryPoint\AuthenticationEntryPoint
            custom_authenticators:
                - IDCI\Bundle\KeycloakSecurityBundle\Security\Authenticator\KeycloakAuthenticator
            logout:
                path: idci_keycloak_security_auth_logout

    role_hierarchy:
        ROLE_ADMIN: ROLE_USER

    access_control:
        # This following ROLES must be configured in your Keycloak client
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/api, roles: ROLE_API }
```

**Note**:
If you wish to secure your application using OAuth 2 Authorization Code Flow for route starting with `/admin`, you will have to put the provided bundle routes behind the firewall, so here is an exeample on how to do this:

```yaml

    ...

        secured_area:
            pattern: ^/(admin|auth)
            provider: idci_keycloak_security_provider
            entry_point: IDCI\Bundle\KeycloakSecurityBundle\Security\EntryPoint\AuthenticationEntryPoint
            custom_authenticators:
                - IDCI\Bundle\KeycloakSecurityBundle\Security\Authenticator\KeycloakAuthenticator
            logout:
                path: idci_keycloak_security_auth_logout

    ...

```

## Keycloak configuration

If you need help to use keycloak because it is the first time you work on it, we've made a little tutorial step by step describing a basic configuration of a keycloak realm:

 * [Keycloak older than 19.0.0](./Resources/docs/keycloak-help-guide-old.md)
 * [Keycloak equal or newer than 19.0.0](./Resources/docs/keycloak-help-guide.md)

## Logout

To logout users, use the route 'idci_keycloak_security_auth_logout':

```twig
<a href="{{ url('idci_keycloak_security_auth_logout') }}">Logout</a>
```

## Keycloak user account space

If you wants to provide a link to access keycloak user account space, use the route 'idci_keycloak_security_auth_account':

```twig
<a href="{{ url('idci_keycloak_security_auth_account') }}">Account</a>
```