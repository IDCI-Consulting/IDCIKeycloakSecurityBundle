<?php

namespace NTI\KeycloakSecurityBundle\Controller\ApiAdmin\Role;

use AppBundle\Util\Rest\RestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class KeycloakAdminController
 * @package NTI\KeycloakSecurityBundle\Controller\ApiAdmin\Role
 * @Route("/")
 */
class KeycloakApiAdminRoleController extends Controller {

    // REST Methods
    /**
     * @Route("/roles/getAll", name="keycloak_api_admin_roles_get_all", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @return RestResponse
     */
    public function getAllAction(Request $request) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Role\KeycloakAdminRoleController::getAllAction",["request" => $request]);
    }

    /**
     * @Route("/roles/{role}", name="keycloak_api_admin_roles_get", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $role
     * @return RestResponse
     */
    public function getByNameAction(string $role) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Role\KeycloakAdminRoleController::getByNameAction",["role" => $role]);
    }

    /**
     * @Route("/roles", name="keycloak_api_admin_roles_post", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @return RestResponse
     */
    public function postAction(Request $request) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Role\KeycloakAdminRoleController::postAction",["request" => $request]);
    }

    /**
     * @Route("/roles/{role}", name="keycloak_api_admin_roles_put", options={"expose"=true}, methods={"PUT"})
     * @param Request $request
     * @param $role
     * @return RestResponse
     */
    public function putAction(Request $request, string $role) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Role\KeycloakAdminRoleController::putAction",["request" => $request, "role" => $role]);
    }

    /**
     * @Route("/roles/{role}", name="keycloak_api_admin_roles_delete", options={"expose"=true}, methods={"DELETE"})
     * @param Request $request
     * @param $role
     * @return RestResponse
     */
    public function deleteAction(Request $request, string $role) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Role\KeycloakAdminRoleController::deleteAction",["request" => $request, "role" => $role]);
    }

    /**
     * @Route("/roles/{role}/composites", name="keycloak_api_admin_roles_get_composites", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $role
     * @return RestResponse
     */
    public function getCompositesByNameAction(string $role) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Role\KeycloakAdminRoleController::getCompositesByNameAction",["role" => $role]);
    }

    /**
     * @Route("/roles/{role}/composites", name="keycloak_api_admin_roles_post_composites", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @param $role
     * @return RestResponse
     */
    public function postCompositesByNameAction(Request $request, string $role) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Role\KeycloakAdminRoleController::postCompositesByNameAction",["request" => $request, "role" => $role]);
    }

    /**
     * @Route("/roles/{role}/composites", name="keycloak_api_admin_roles_delete_composites", options={"expose"=true}, methods={"DELETE"})
     * @param Request $request
     * @param $role
     * @return RestResponse
     */
    public function deleteCompositesByNameAction(Request $request, string $role) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Role\KeycloakAdminRoleController::deleteCompositesByNameAction",["request" => $request, "role" => $role]);
    }

}