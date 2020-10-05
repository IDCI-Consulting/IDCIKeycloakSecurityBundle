<?php

namespace NTI\KeycloakSecurityBundle\Service;

use NTI\KeycloakSecurityBundle\Service\RequestService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class KeycloakSecurityService extends RequestService {

    protected $basePath = "/auth/realms/{realm}";
    
    const GET_TOKEN_URL = "/protocol/openid-connect/token";
    
    const PASSWORD_GRANT_TYPE = 'password';

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

}