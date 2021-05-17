<?php

namespace NTI\KeycloakSecurityBundle\Controller\ApiAdmin\Group;

use AppBundle\Util\Rest\DataTableRestResponse;
use AppBundle\Util\Rest\RestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class KeycloakAdminController
 * @package NTI\KeycloakSecurityBundle\Controller\ApiAdmin\Group
 * @Route("/")
 */
class KeycloakApiAdminGroupController extends Controller {

    // REST Methods
    /**
     * @Route("/group/getAll", name="keycloak_api_admin_group_get_all", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @return DataTableRestResponse
     */
    public function getAllAction(Request $request) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Group\KeycloakAdminGroupController::getAllAction",["request" => $request]);
    }

    /**
     * @Route("/group/{id}", name="keycloak_api_admin_group_get", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Group\KeycloakAdminGroupController::getAction",["request" => $request, "id" => $id]);
    }

    /**
     * @Route("/group", name="keycloak_api_admin_group_post", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @return RestResponse
     */
    public function postAction(Request $request) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Group\KeycloakAdminGroupController::postAction",["request" => $request]);
    }

    /**
     * @Route("/group/{id}", name="keycloak_api_admin_group_put", options={"expose"=true}, methods={"PUT"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function putAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Group\KeycloakAdminGroupController::putAction",["request" => $request, "id" => $id]);
    }

    /**
     * @Route("/group/{id}/roles", name="keycloak_api_admin_group_get_roles", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getRolesAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Group\KeycloakAdminGroupController::getRolesAction",["request" => $request, "id" => $id]);
    }

    /**
     * @Route("/group/{id}/roles/available", name="keycloak_api_admin_group_get_roles_available", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getRolesAvailableAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Group\KeycloakAdminGroupController::getRolesAvailableAction",["request" => $request, "id" => $id]);
    }

    /**
     * @Route("/group/{id}/roles/composite", name="keycloak_api_admin_group_get_roles_composite", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getRolesCompositeAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Group\KeycloakAdminGroupController::getRolesCompositeAction",["request" => $request, "id" => $id]);
    }

    /**
     * @Route("/group/{id}/addRoles", name="keycloak_api_admin_group_add_roles", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function addRolesAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Group\KeycloakAdminGroupController::addRolesAction",["request" => $request, "id" => $id]);
    }
    
    /**
     * @Route("/group/{id}/removeRoles", name="keycloak_api_admin_group_remove_roles", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function removeRolesAction(Request $request, string $id) {
        return $this->forward("NTI\KeycloakSecurityBundle\Controller\Admin\Group\KeycloakAdminGroupController::removeRolesAction",["request" => $request, "id" => $id]);
    }
}