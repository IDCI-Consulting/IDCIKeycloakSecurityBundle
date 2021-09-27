<?php

namespace NTI\KeycloakSecurityBundle\Service;

use NTI\KeycloakSecurityBundle\Service\KeycloakSecurityService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class KeycloakAdminGroupService extends KeycloakSecurityService 
{
    protected $basePath = "/auth/admin/realms/{realm}/groups";
    
    const GET_ALL_URL = "";
    const GET_URL = "/{id}";
    const COUNT_URL = "/count";
    const UPDATE_BY_ID_URL = "/{id}";
    const GET_ROLES_URL = "/{id}/role-mappings/clients/{clientId}";
    const GET_ROLES_AVAILABLE_URL = "/{id}/role-mappings/clients/{clientId}/available";
    const GET_ROLES_COMPOSITE_URL = "/{id}/role-mappings/clients/{clientId}/composite";
    const UPDATE_ROLES_URL = "/{id}/role-mappings/clients/{clientId}";


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

    public function getById($id = "") {
        $url = $this->basePath.self::GET_URL;
        $url = str_replace("{id}", $id, $url);
        $result = $this->restGet($url);
        $response = json_decode($result, true);
        return $response;
    }

    public function getByName($name) {
        $clientId = $this->container->getParameter(self::KEYCLOAK_CLIENT_ID);

        try{
            // Load user with email
            $res = $this->getAll(array('name' => $name));

            if(!isset($res[0])) return null;

            $groupData = $res[0];

            return $groupData;
        }catch (\Exception $ex) {
            return null;
        }
    }

    public function saveNewGroup($data) {
        $url = $this->basePath;
        $result = $this->restPost($url, $data);
        $response = json_decode($result, true);
        return $response;
    }

    public function updateGroup($id, $data) {
        $url = $this->basePath.self::UPDATE_BY_ID_URL;
        $url = str_replace("{id}", $id, $url);
        $result = $this->restPut($url, $data);
        $response = json_decode($result, true);
        return $response;
    }

    public function getRoles($id) {
        $url = $this->basePath.self::GET_ROLES_URL;
        $url = str_replace("{id}", $id, $url);
        $url = str_replace("{clientId}", $this->container->getParameter(self::KEYCLOAK_CLIENT_ID_CODE), $url);
        $result = $this->restGet($url);
        $response = json_decode($result, true);
        return $response;
    }

    public function deleteGroup($id) {
        $url = $this->basePath.self::UPDATE_BY_ID_URL;
        $url = str_replace("{id}", $id, $url);
        $result = $this->restDelete($url);
        $response = json_decode($result, true);
        return $response;
    }

    public function getRolesAvailable($id) {
        $url = $this->basePath.self::GET_ROLES_AVAILABLE_URL;
        $url = str_replace("{id}", $id, $url);
        $url = str_replace("{clientId}", $this->container->getParameter(self::KEYCLOAK_CLIENT_ID_CODE), $url);
        $result = $this->restGet($url);
        $response = json_decode($result, true);
        return $response;
    }

    public function getRolesComposite($id) {
        $url = $this->basePath.self::GET_ROLES_COMPOSITE_URL;
        $url = str_replace("{id}", $id, $url);
        $url = str_replace("{clientId}", $this->container->getParameter(self::KEYCLOAK_CLIENT_ID_CODE), $url);
        $result = $this->restGet($url);
        $response = json_decode($result, true);
        return $response;
    }

    public function addRoles($id, $data) {
        $url = $this->basePath.self::UPDATE_ROLES_URL;
        $url = str_replace("{id}", $id, $url);
        $url = str_replace("{clientId}", $this->container->getParameter(self::KEYCLOAK_CLIENT_ID_CODE), $url);
        $result = $this->restPost($url, $data);
        $response = json_decode($result, true);
        return $response;
    }

    public function removeRoles($id, $data) {
        $url = $this->basePath.self::UPDATE_ROLES_URL;
        $url = str_replace("{id}", $id, $url);
        $url = str_replace("{clientId}", $this->container->getParameter(self::KEYCLOAK_CLIENT_ID_CODE), $url);
        $result = $this->restDelete($url, $data);
        $response = json_decode($result, true);
        return $response;
    }

}