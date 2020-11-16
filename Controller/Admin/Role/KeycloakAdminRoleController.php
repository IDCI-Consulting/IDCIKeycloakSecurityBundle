<?php

namespace NTI\KeycloakSecurityBundle\Controller\Admin\Role;

use AppBundle\Util\DataTable\DataTableOptionsProcessor;
use AppBundle\Util\Rest\DataTableRestResponse;
use AppBundle\Util\Rest\RestResponse;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class KeycloakAdminController
 * @package NTI\KeycloakSecurityBundle\Controller\Admin\Role
 * @Route("/")
 */
class KeycloakAdminRoleController extends Controller {

    // REST Methods
    /**
     * @Route("/roles/getAll", name="keycloak_admin_roles_get_all", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @return RestResponse
     */
    public function getAllAction(Request $request) {
        try{
            $result = $this->get('nti.keycloak.admin.role.service')->getAll();
            return new RestResponse($result);
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while loading the role. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/roles/{role}", name="keycloak_admin_roles_get", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $role
     * @return RestResponse
     */
    public function getByNameAction(string $role) {
        try{
            $result = $this->get('nti.keycloak.admin.role.service')->getByName($role);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 404, "Role not found.");
            else if($ex->getCode() == 403)
                return new RestResponse(null, 403, "You are not allowed to perform this action.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while loading the role. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while loading the role. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/roles", name="keycloak_admin_roles_post", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @return RestResponse
     */
    public function postAction(Request $request) {
        try{
            $data = json_decode($request->getContent(), true);

            if(!isset($data['name']) || !$data['name'])
                return new RestResponse(null, 400, "The role name is required.");

            $role = array(
                'name' => $data['name'],
                'description' => isset($data['description']) && $data['description'] ? $data['description'] : null,
            );

            $result = $this->get('nti.keycloak.admin.role.service')->saveNewRole($role);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 400)
                return new RestResponse(null, 400, "An unknown error occurred while creating the role. Please check the information and try again.");
            else if($ex->getCode() == 409)
                return new RestResponse(null, 400, "You are trying to create a role that already exists.");
            else if($ex->getCode() == 403)
                return new RestResponse(null, 403, "You are not allowed to perform this action.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while creating the role. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while creating the role. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/roles/{role}", name="keycloak_admin_roles_put", options={"expose"=true}, methods={"PUT"})
     * @param Request $request
     * @param $role
     * @return RestResponse
     */
    public function putAction(Request $request, string $role) {
        try{
            $data = json_decode($request->getContent(), true);

            $roleData = array(
                'name' => $role,
                'description' => isset($data['description']) && $data['description'] ? $data['description'] : null,
            );

            $result = $this->get('nti.keycloak.admin.role.service')->updateRole($role, $roleData);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 400)
                return new RestResponse(null, 400, "An unknown error occurred while updating the role. Please check the information and try again.");
            else if($ex->getCode() == 404)
                return new RestResponse(null, 404, "Role not found.");
            else if($ex->getCode() == 403)
                return new RestResponse(null, 403, "You are not allowed to perform this action.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while updating the role. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while updating the role. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/roles/{role}", name="keycloak_admin_roles_delete", options={"expose"=true}, methods={"DELETE"})
     * @param Request $request
     * @param $role
     * @return RestResponse
     */
    public function deleteAction(Request $request, string $role) {
        try{
            $result = $this->get('nti.keycloak.admin.role.service')->deleteRole($role);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 404, "Role not found.");
            else if($ex->getCode() == 403)
                return new RestResponse(null, 403, "You are not allowed to perform this action.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while deleting the role. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while deleting the role. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/roles/{role}/composites", name="keycloak_admin_roles_get_composites", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $role
     * @return RestResponse
     */
    public function getCompositesByNameAction(string $role) {
        try{
            $result = $this->get('nti.keycloak.admin.role.service')->getCompositesByName($role);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 404, "Role not found.");
            else if($ex->getCode() == 403)
                return new RestResponse(null, 403, "You are not allowed to perform this action.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while loading the role. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while loading the role. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/roles/{role}/composites", name="keycloak_admin_roles_post_composites", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @param $role
     * @return RestResponse
     */
    public function postCompositesByNameAction(Request $request, string $role) {
        try{

            /**
             * Example:
             * $data = array(
             *    array("id" => 1),
             *    array("id" => 2)
             * )
             */

            $data = json_decode($request->getContent(), true);
            $result = $this->get('nti.keycloak.admin.role.service')->updateCompositesByName($role, $data);
            return new RestResponse($result);
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while updating the role composites. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/roles/{role}/composites", name="keycloak_admin_roles_delete_composites", options={"expose"=true}, methods={"DELETE"})
     * @param Request $request
     * @param $role
     * @return RestResponse
     */
    public function deleteCompositesByNameAction(Request $request, string $role) {
        try{

            /**
             * Example:
             * $data = array(
             *    array("id" => 1),
             *    array("id" => 2)
             * )
             */

            $data = json_decode($request->getContent(), true);
            $result = $this->get('nti.keycloak.admin.role.service')->deleteCompositesByName($role, $data);
            return new RestResponse($result);
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while deleting the role composites. Please try again or contact support if the problem persists.");
        }
    }

}