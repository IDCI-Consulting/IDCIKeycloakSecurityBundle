# Keycloak help guide

### Create a realm


### Create a client


### Create client roles

### Create users

#### Define a new password

When you create a user, he doesn't have a password.

To defined one, go to ```Manage > Users > View all users > admin > Credentials```

![Change user password](screenshots/change-user-password.png)

Note: If you want to make the password fixed you must disable the "Temporary" option.

#### Affect a role

Now we can affect roles we created before to the new user.

To add role, go to ```Manage > Users > View all users > admin > Role Mappings```. You must specify the client the user has access to.

![Affect user role](screenshots/affect-user-role.png)
