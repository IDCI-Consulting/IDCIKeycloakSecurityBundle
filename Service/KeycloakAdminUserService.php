<?php

namespace NTI\KeycloakSecurityBundle\Service;

use NTI\KeycloakSecurityBundle\Service\KeycloakSecurityService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class KeycloakAdminUserService extends KeycloakSecurityService {

    protected $basePath = "/auth/admin/realms/{realm}/users";
    
    const GET_ALL_URL = "";
    const GET_URL = "/{id}";
    const COUNT_URL = "/count";
    const UPDATE_BY_ID_URL = "/{id}";
    const GET_ROLES_URL = "/{id}/role-mappings/clients/{clientId}";
    const GET_ROLES_AVAILABLE_URL = "/{id}/role-mappings/clients/{clientId}/available";
    const GET_ROLES_COMPOSITE_URL = "/{id}/role-mappings/clients/{clientId}/composite";
    const UPDATE_ROLES_URL = "/{id}/role-mappings/clients/{clientId}";
    const RESET_PASSWORD_URL = "/{id}/execute-actions-email";
    const UPDATE_GROUP_USER = "/{id}/groups/{groupId}";
    const DENIED_ROLES = "denied_roles";

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

    public function get($id) {
        $url = $this->basePath.self::GET_URL;
        $url = str_replace("{id}", $id, $url);
        $result = $this->restGet($url);
        $response = $this->attributesDecode(json_decode($result, true));
        return $response;
    }

    public function getFromEmail($email, $roles = false) {
        $clientId = $this->container->getParameter(self::KEYCLOAK_CLIENT_ID);

        try{
            // Load user with email
            $res = $this->getAll(array('email' => $email));

            if(!isset($res[0])) return null;

            $userData = $this->attributesDecode($res[0]);

            if($roles == true){
                // Parse denied roles
                $deniedRoles = [];
                if(isset($userData['attributes']) && isset($userData['attributes']['denied_roles'])){
                    $deniedRoles = $deniedRoles[$clientId];
                }

                // Load roles and remove denied roles
                $roles = $this->getRolesComposite($userData['id']);
                $roles = array_map(function ($role) { return $role['name']; }, $roles );

                $rolesTmp = array(); // Remove denied roles
                foreach($roles as $val) $rolesTmp[$val] = 1;
                foreach($deniedRoles as $val) unset($rolesTmp[$val]);
                $userData['roles'] = array_keys($rolesTmp);
            }

            return $userData;
        }catch (\Exception $ex){
            return null;
        }
    }

    public function count() {
        $url = $this->basePath.self::COUNT_URL;
        $result = $this->restGet($url);
        $response = json_decode($result, true);
        return $response;
    }

    public function saveNewUser($data) {
        $url = $this->basePath;
        $result = $this->restPost($url, $data);
        $response = json_decode($result, true);
        return $response;
    }

    public function updateUser($id, $data) {
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

    public function resetPassword($id) {
        $url = $this->basePath.self::RESET_PASSWORD_URL;
        $url = str_replace("{id}", $id, $url);
        $data = ["UPDATE_PASSWORD"];
        $result = $this->restPut($url, $data);
        $response = json_decode($result, true);
        return $response;
    }

    public function updateUserGroup($id, $groupId, $data) {
        $url = $this->basePath.self::UPDATE_GROUP_USER;
        $url = str_replace("{id}", $id, $url);
        $url = str_replace("{groupId}", $id, $groupId);
        $result = $this->restPut($url, $data);
        $response = json_decode($result, true);
        return $response;
    }

    private function attributesDecode($data = null)
    {
        if(array_key_exists("attributes",$data) && $data["attributes"]){
            foreach ($data['attributes'] as $attribute => $value) {
                if(strpos($attribute,self::DENIED_ROLES) !== false && is_array($value)){
                    $data['attributes'][$attribute] = $this->isJSON($value[0]) ? json_decode($value[0],true) : [];
                }else if(is_array($value)){
                    $data['attributes'][$attribute] = $this->isBoolean($value[0]) ? filter_var($value[0], FILTER_VALIDATE_BOOLEAN, false) : ($this->isJSON($value[0]) ? json_decode($value[0],true) : ($this->isNullOrEmptyString($value[0]) ? null : $value[0]));
                }else{
                    $data['attributes'][$attribute] = $this->isBoolean($value) ? filter_var($value, FILTER_VALIDATE_BOOLEAN, false) : ($this->isJSON($value) ? json_decode($value,true) : ($this->isNullOrEmptyString($value) ? null : $value));
                }
            }
        }
        return $data;
    }

    private function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    private function isBoolean($string){
        if(!$string) return false;
        return null !== filter_var($string, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    private function isNullOrEmptyString($string){
        return (!isset($string) || trim($string) === "" || trim($string) == "null");
    }
}