<?php

namespace NTI\KeycloakSecurityBundle\Service;

use NTI\KeycloakSecurityBundle\Service\KeycloakSecurityService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class KeycloakAdminRoleService extends KeycloakSecurityService {

    protected $basePath = "/auth/admin/realms/{realm}/clients/{clientIdCode}/roles";
    
    const GET_ALL_URL = "";
    const GET_BY_NAME_URL = "/{name}";
    const UPDATE_BY_NAME_URL = "/{name}";
    const GET_BY_NAME_COMPOSITES_URL = "/{name}/composites";
    const UPDATE_BY_NAME_COMPOSITES_URL = "/{name}/composites";

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->basePath = str_replace("{realm}", $this->container->getParameter(self::KEYCLOAK_REALM), $this->basePath);
        $this->basePath = str_replace("{clientIdCode}", $this->container->getParameter(self::KEYCLOAK_CLIENT_ID_CODE), $this->basePath);
    }

    public function getAll($options = array()) {
        $url = $this->basePath.self::GET_ALL_URL;
        $url .= "?".http_build_query($options);
        $result = $this->restGet($url);
        $response = json_decode($result, true);
        return $response;
    }
    
    public function getByName($name, $options = array()) {
        $url = $this->basePath.self::GET_BY_NAME_URL;
        $url = str_replace("{name}", $name, $url);
        $url .= "?".http_build_query($options);
        $result = $this->restGet($url);
        $response = json_decode($result, true);
        return $response;
    }

    public function saveNewRole($data) {
        $url = $this->basePath;
        $result = $this->restPost($url, $data);
        $response = json_decode($result, true);
        return $response;
    }

    public function updateRole($role, $data) {
        $url = $this->basePath.self::UPDATE_BY_NAME_URL;
        $url = str_replace("{name}", $role, $url);
        $result = $this->restPut($url, $data);
        $response = json_decode($result, true);
        return $response;
    }

    public function deleteRole($role) {
        $url = $this->basePath.self::UPDATE_BY_NAME_URL;
        $url = str_replace("{name}", $role, $url);
        $result = $this->restDelete($url);
        $response = json_decode($result, true);
        return $response;
    }

    public function getCompositesByName($name, $options = array()) {
        $url = $this->basePath.self::GET_BY_NAME_COMPOSITES_URL;
        $url = str_replace("{name}", $name, $url);
        $url .= "?".http_build_query($options);
        $result = $this->restGet($url);
        $response = json_decode($result, true);
        return $response;
    }
    
    public function updateCompositesByName($name, $data) {
        $url = $this->basePath.self::UPDATE_BY_NAME_COMPOSITES_URL;
        $url = str_replace("{name}", $name, $url);
        $result = $this->restPost($url, $data);
        $response = json_decode($result, true);
        return $response;
    }
    
    public function deleteCompositesByName($name, $data) {
        $url = $this->basePath.self::UPDATE_BY_NAME_COMPOSITES_URL;
        $url = str_replace("{name}", $name, $url);
        $result = $this->restDelete($url, $data);
        $response = json_decode($result, true);
        return $response;
    }

}