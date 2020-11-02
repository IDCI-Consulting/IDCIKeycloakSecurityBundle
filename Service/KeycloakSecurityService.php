<?php

namespace NTI\KeycloakSecurityBundle\Service;

use League\OAuth2\Client\Token\AccessToken;
use NTI\KeycloakSecurityBundle\Security\User\KeycloakUser;
use NTI\KeycloakSecurityBundle\Service\RequestService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class KeycloakSecurityService extends RequestService {

    protected $basePath = "/auth/realms/{realm}";
    
    const GET_TOKEN_URL = "/protocol/openid-connect/token";
    const INTROSPECT_TOKEN_URL = "/protocol/openid-connect/token/introspect";
    
    const PASSWORD_GRANT_TYPE = 'password';
    const TOKEN_EXCHANGE_GRANT_TYPE = 'urn:ietf:params:oauth:grant-type:token-exchange';

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->basePath = str_replace("{realm}", $this->container->getParameter(self::KEYCLOAK_REALM), $this->basePath);
    }

    public function getToken($username, $password) {
        $url = $this->basePath.self::GET_TOKEN_URL;

        $options = array(
            'client_id' => $this->container->getParameter(self::KEYCLOAK_CLIENT_ID),
            'client_secret' => $this->container->getParameter(self::KEYCLOAK_CLIENT_SECRET),
            'username' => $username,
            'password' => $password,
            'grant_type' => $this::PASSWORD_GRANT_TYPE
        );

        $result = $this->restPost($url, $options, 'form_params');
        $response = json_decode($result, true);
        return $response;
    }

    public function introspectToken($username, $token) {
        $url = $this->basePath.self::INTROSPECT_TOKEN_URL;

        $options = array(
            'client_id' => $this->container->getParameter(self::KEYCLOAK_CLIENT_ID),
            'client_secret' => $this->container->getParameter(self::KEYCLOAK_CLIENT_SECRET),
            'username' => $username,
            'token' => $token
        );

        $result = $this->restPost($url, $options, 'form_params');
        $response = json_decode($result, true);

        if(isset($response['active']) && $response['active'] == false){
            return false;
        }

        return $response;
    }

    /** 
     * User Id to impersonate
     * @var $userId 
     */
    public function impersonateUser($userId) {

        // Load the current token from the logged user (Must have impersonation access)
        $user = $this->container->get('app.user')->getLoggedUser();
        $token = $user->getKeycloakAccessToken()->getToken();

        // Prepare the impersonation request. This will request a token exchange, sending the current token and receiving a new token on behalf of the new userId
        $options = array(
            'client_id' => $this->container->getParameter(self::KEYCLOAK_CLIENT_ID),
            'client_secret' => $this->container->getParameter(self::KEYCLOAK_CLIENT_SECRET),
            'grant_type' => $this::TOKEN_EXCHANGE_GRANT_TYPE,
            'subject_token' => $token,
            'requested_subject' => $userId
        );

        $url = $this->basePath.self::GET_TOKEN_URL;
        $result = $this->restPost($url, $options, 'form_params');
        $response = json_decode($result, true);

        // Parse the new impersonated the access token 
        $accessToken = new AccessToken($response);
        $newUser = $this->container->get('NTI\KeycloakSecurityBundle\Security\User\KeycloakUserProvider')->loadUserByUsername($accessToken);
        $newToken = new PostAuthenticationGuardToken($newUser, 'main', $newUser->getRoles());

        // Invalidate previous session
        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->container->get('security.token_storage');
        $oldToken = $tokenStorage->getToken();
        $oldUser = $oldToken->getUser();

        if (!$oldUser instanceof KeycloakUser) {
            throw new \RuntimeException('The user must be an instance of KeycloakUser');
        }

        $tokenStorage->setToken(null);
        $this->container->get('session')->clear();

        // Add impersonated token to new session
        $tokenStorage->setToken($newToken);
        $this->container->get('session')->set('session_impersonation_user_id', $newUser->getId());

        return $newUser;
    }

}