How to configure an user authorization request with Keycloak and your Symfony project
=====================================================================================

---

Here is schema to get an overview of what you can do easily with this bundle to securized your API Symfony apllication with OAuth2.0.

<pre>
User browser        Apllication Backoffice (Symfony)        Keycloak
    |            1               |                              |
    | -------------------------> |                              |
    |            2               |                              |
    | <------------------------- |                              |
    |                                 3                         |
    | --------------------------------------------------------> |
    |                                 4                         |
    | <-------------------------------------------------------- |
    |            5                                              |
    | -------------------------> |               6              |
    |                            | ---------------------------> |
    |                            |               7              |
    |            8               | <--------------------------- |
    | <------------------------- |
</pre>

1. User request a backoffice access
2. Unauthenticated user is redirect to Keycloak
3. Keycloak ask user crendentials (login / password)
4. User browser is redirect to the Application backoffice with a security token
5. User browser give the security token to the application
6. The Symfony application check the given token  with Keycloak
7. If the token is ok, Keycloak return user informations like username, email, roles, ... (following to the scope)
8. The Symfony application create user session and authenticate the user

---

## Requirements section

First of all, a realm named `demo` must exists and you have to create a client.

You can use our [tutorial to help you to create your client](./keycloak-help-guide-client-configuration.md)

---

# Keycloak - Configuration

## Allow valid redirect URIs

Fill **Valid redirect URIs**, **Valid post logout redirect URIs** and click on **Save**:

![Image](screenshots/screen_resource_application_general_settings.png)

## Client roles creation

We are going to create roles "ROLE_ADMIN" and "ROLE_USER" for our client application.

Click on **Create role**:

![Image](screenshots/screen_with_create_role_button.png)

Fill **Role name** and click on **Save**.

![Image](screenshots/screen_roles_created.png)

Your roles have been created successfully!

## User creation

We are going to create a group with the previous client role "ROLE_ADMIN".
Then we will create a user and add this one in the previous group.

1. Group creation

Click on **Groups** on left panel:

![Image](screenshots/screen_of_left_panel_for_groups.png)

Click on **Create group**:

![Image](screenshots/screen_with_create_group_button.png)

Fill **name** and click on **Create**:

![Image](screenshots/create_group.png)

Your group has been created successfully!

Click on developer group:

![Image](screenshots/screen_with_developer_group.png)

In **Role mapping** tab, click on **Assign role**:

![Image](screenshots/screen_with_assign_role_button.png)

Click on **Filter by clients**, search name-of-your-resource-application, check ROLE_ADMIN and click on **Assign**:

![Image](screenshots/screen_assign_roles_to_developer_account_without_filter_applied.png)
![Image](screenshots/screen_assign_roles_to_developer_account_with_filter_applied.png)

Your role has been mapped successfully on developer group!

2. User creation

Click on **Users** on left panel:

![Image](screenshots/screen_of_left_panel_for_users.png)

Click on **Add user**:

![Image](screenshots/screen_with_add_user_button.png)

Fill **Username**, **Email**, **First name**, **Last name** and check **Email verified**:

![Image](screenshots/create_user.png)

Your user has been created successfully!

3. Set user password

Click on **Credentials** tab and on **Set password**:

![Image](screenshots/screen_with_set_password_button.png)

Set password and click on Save:

![Image](screenshots/screen_set_password_for_username.png)

4. Add user in developer group

Click on **Members** tab and on **Add member**:

![Image](screenshots/screen_with_add_member_button.png)

Select user and click on **Add**:

![Image](screenshots/screen_add_member.png)

Your user has been successfully added in developer group!

---

# Symfony - Configuration

## Configure env file

For KEYCLOAK_SERVER_BASE_URL, you need to put your keycloak URL (something like https://keycloak/auth).

For KEYCLOAK_CLIENT_SECRET, you need to copy secret present in your client name-of-your-resource-application for example:

![Image](screenshots/screen_resource_application_credentials.png)

In .env file, update:
```yaml
###> idci/keycloak-security-bundle ###
KEYCLOAK_SERVER_BASE_URL=https://keycloak/auth
KEYCLOAK_SERVER_PUBLIC_BASE_URL=${KEYCLOAK_SERVER_BASE_URL}
KEYCLOAK_SERVER_PRIVATE_BASE_URL=${KEYCLOAK_SERVER_BASE_URL}
KEYCLOAK_REALM=demo
KEYCLOAK_CLIENT_ID=name-of-your-resource-application
KEYCLOAK_CLIENT_SECRET=client_secret
###< idci/keycloak-security-bundle ###
```

## Configure security

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
```

---

# Result

With your browser, go to https://your-symfony-application-URL/admin

If you open Network tab of inspector of your browser, you will see 3 routes:

1. /admin:

![Image](screenshots/screen_admin_with_redirect.png)

This route is redirected to /auth/connect/keycloak (provided by our bundle)

2. /auth/connect/keycloak

![Image](screenshots/screen_with_auth_connect_keycloak_with_redirect.png)

This route is redirected to your Keycloak auth route

3. /auth/realms/demo/protocol/openid-connect/auth

![Image](screenshots/screen_route_redirect_with_keycloak_demo_prompt.png)

A Keycloak login prompt will be displayed. You need to fill with username and password created:

![Image](screenshots/keycloak_demo_prompt.png)

After, click on ***Sign In***, in Network tab of inspector of your browser, you will see 3 new routes:

1. /auth/realms/demo/login-actions/authenticate?session_code=

![Image](screenshots/screen_with_redirect_to_connect-check.png)

This route is redirected to /auth/connect-check/keycloak (provided by our bundle)

2. /auth/connect-check/keycloak

![Image](screenshots/screen_with_last_redirect_to_admin.png)

This route sets of PHPSESSID and is redirected to https://your-symfony-application-URL/admin.

3. /admin

![Image](screenshots/screen_admin_redirect_from_connect-check.png)

![Image](screenshots/screen_symfony_profiler_with_user_details.png)

Congrats, you are connected by your Symfony application through Keycloak!
