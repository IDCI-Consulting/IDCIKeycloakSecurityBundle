<?php

namespace NTI\KeycloakSecurityBundle\Controller\ApiAdmin\User;

use AppBundle\Util\Rest\DataTableRestResponse;
use AppBundle\Util\Rest\RestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class KeycloakAdminController
 * @package NTI\KeycloakSecurityBundle\Controller\ApiAdmin\User
 * @Route("/")
 */
class KeycloakApiAdminUserController extends Controller {

    // REST Methods
    /**
     * @Route("/user/getAll", name="keycloak_api_admin_user_get_all", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @return DataTableRestResponse
     */
    public function getAllAction(Request $request) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\User\KeycloakAdminUserController::getAllAction",["request" => $request]);
    }

    /**
     * @Route("/user/{id}", name="keycloak_api_admin_user_get", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\User\KeycloakAdminUserController::getAction",["request" => $request, "id" => $id]);
    }

    /**
     * @Route("/user", name="keycloak_api_admin_user_post", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @return RestResponse
     */
    public function postAction(Request $request) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\User\KeycloakAdminUserController::postAction",["request" => $request]);
    }

    /**
     * @Route("/user/{id}", name="keycloak_api_admin_user_put", options={"expose"=true}, methods={"PUT"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function putAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\User\KeycloakAdminUserController::putAction",["request" => $request, "id" => $id]);
    }

    /**
     * @Route("/user/{id}/roles", name="keycloak_api_admin_user_get_roles", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getRolesAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\User\KeycloakAdminUserController::getRolesAction",["request" => $request, "id" => $id]);
    }

    /**
     * @Route("/user/{id}/roles/available", name="keycloak_api_admin_user_get_roles_available", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getRolesAvailableAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\User\KeycloakAdminUserController::getRolesAvailableAction",["request" => $request, "id" => $id]);
    }

    /**
     * @Route("/user/{id}/roles/composite", name="keycloak_api_admin_user_get_roles_composite", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getRolesCompositeAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\User\KeycloakAdminUserController::getRolesCompositeAction",["request" => $request, "id" => $id]);
    }

    /**
     * @Route("/user/{id}/addRoles", name="keycloak_api_admin_user_add_roles", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function addRolesAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\User\KeycloakAdminUserController::addRolesAction",["request" => $request, "id" => $id]);
    }
    
    /**
     * @Route("/user/{id}/removeRoles", name="keycloak_api_admin_user_remove_roles", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function removeRolesAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\User\KeycloakAdminUserController::removeRolesAction",["request" => $request, "id" => $id]);
    }
    
    /**
     * @Route("/user/{id}/resetPassword", name="keycloak_api_admin_user_reset_password", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function resetPasswordAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\User\KeycloakAdminUserController::resetPasswordAction",["request" => $request, "id" => $id]);
    }

}