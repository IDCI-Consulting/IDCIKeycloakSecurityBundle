<?php

namespace NTI\KeycloakSecurityBundle\Service;

use NTI\KeycloakSecurityBundle\Service\RequestService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class KeycloakAdminUserService extends RequestService {

    protected $basePath = "/auth/admin/realms/{realm}/users";
    
    const GET_ALL_URL = "";
    const GET_URL = "/{id}";

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->basePath = str_replace("{realm}", $this->container->getParameter(self::KEYCLOAK_REALM), $this->basePath);
    }

    public function getAll($options = array()) {
        $url = $this->basePath.self::GET_ALL_URL;
        $url .= "?".http_build_query($options);
        $result = $this->restGet($url);
        $response = json_decode($result, true);
        return $response;
    }

}