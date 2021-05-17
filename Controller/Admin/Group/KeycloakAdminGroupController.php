<?php

namespace NTI\KeycloakSecurityBundle\Controller\Admin\Group;

use AppBundle\Util\DataTable\DataTableOptionsProcessor;
use AppBundle\Util\Rest\DataTableRestResponse;
use AppBundle\Util\Rest\RestResponse;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class KeycloakAdminGroupController
 * @package NTI\KeycloakSecurityBundle\Controller\Admin\Group
 * @Route("/")
 */
class KeycloakAdminGroupController extends Controller
{
    const API_MAX_RESULTS = 10;

    // REST Methods
    /**
     * @Route("/user/getAll", name="keycloak_admin_user_get_all", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @return DataTableRestResponse
     */
    public function getAllAction(Request $request) {
        // Get standard options
        $options = DataTableOptionsProcessor::GetOptions($request);

        // Translate options to keycloak api standard
        $options = array(
            'name' => isset($options['filters']['name']) ? $options['filters']['name'] : null,
            'firstName' => isset($options['filters']['firstName']) ? $options['filters']['firstName'] : null,
            'lastName' => isset($options['filters']['lastName']) ? $options['filters']['lastName'] : null,
            'username' => isset($options['filters']['username']) ? $options['filters']['username'] : null,
            'search' => isset($options['search']) ? $options['search'] : null, // Search in username, first or last name, or email
            'first' => isset($options['start']) ? $options['start'] : 0,
            'max' => isset($options['length']) ? $options['length'] : self::API_MAX_RESULTS,
        );
        $result = $this->get('nti.keycloak.admin.group.service')->getAll($options);
        return new RestResponse($result);
    }

    /**
     * @Route("/group/{id}", name="keycloak_admin_group_get", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getAction(Request $request, string $id) {

        try{
            $result = $this->get('nti.keycloak.admin.group.service')->get($id);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 404, "Group not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while loading the Group. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while loading the Group. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/group", name="keycloak_admin_group_post", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @return RestResponse
     */
    public function postAction(Request $request) {
        try{
            $data = json_decode($request->getContent(), true);

            if(!isset($data['name']) || !$data['name'])
                return new RestResponse(null, 400, "The name is required.");

            $user = array(
                'name' => $data['name']
            );

            $result = $this->get('nti.keycloak.admin.group.service')->saveNewGroup($user);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 400)
                return new RestResponse(null, 400, "An unknown error occurred while creating the group. Please check the information and try again.");
            else if($ex->getCode() == 403)
                return new RestResponse(null, 403, "You are not allowed to perform this action.");
            else if($ex->getCode() == 409)
                return new RestResponse(null, 400, "You are trying to create a group that already exists.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while creating the group. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while creating the group. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/group/{id}", name="keycloak_admin_group_put", options={"expose"=true}, methods={"PUT"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function putAction(Request $request, string $id) {
        try{
            $data = json_decode($request->getContent(), true);

            $groupData = array(
                'name' => $data['name']
            );

            $result = $this->get('nti.keycloak.admin.group.service')->updateGroup($id, $groupData);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 400)
                return new RestResponse(null, 400, "An unknown error occurred while updating the group. Please check the information and try again.");
            else if($ex->getCode() == 403)
                return new RestResponse(null, 403, "You are not allowed to perform this action.");
            else if($ex->getCode() == 409)
                return new RestResponse(null, 400, "You are trying to create a group that already exists.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while updating the group. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while updating the group. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/group/{group}", name="keycloak_admin_groups_delete", options={"expose"=true}, methods={"DELETE"})
     * @param Request $request
     * @param $group
     * @return RestResponse
     */
    public function deleteAction(Request $request, string $group) {
        try{
            $result = $this->get('nti.keycloak.admin.group.service')->deleteRole($group);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 404, "Group not found.");
            else if($ex->getCode() == 403)
                return new RestResponse(null, 403, "You are not allowed to perform this action.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while deleting the group. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while deleting the group. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/group/{id}/roles", name="keycloak_admin_group_get_roles", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getRolesAction(Request $request, string $id) {

        try{
            $result = $this->get('nti.keycloak.admin.group.service')->getRoles($id);
            return new RestResponse($result);
        } catch (ClientException $ex) {
            if($ex->getCode() == 404)
                return new RestResponse(null, 404, "Group roles not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while loading the Group roles. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while loading the Group roles. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/group/{id}/roles/available", name="keycloak_admin_group_get_roles_available", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getRolesAvailableAction(Request $request, string $id) {

        try{
            $result = $this->get('nti.keycloak.admin.group.service')->getRolesAvailable($id);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 404, "Group roles not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while loading the Group roles. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while loading the Group roles. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/group/{id}/roles/composite", name="keycloak_admin_group_get_roles_composite", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getRolesCompositeAction(Request $request, string $id) {

        try{
            $result = $this->get('nti.keycloak.admin.group.service')->getRolesComposite($id);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 404, "Group roles not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while loading the Group roles. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while loading the Group roles. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/group/{id}/addRoles", name="keycloak_admin_group_add_roles", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function addRolesAction(Request $request, string $id) {
        try{
            $data = json_decode($request->getContent(), true);
            $result = $this->get('nti.keycloak.admin.group.service')->addRoles($id, $data);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 400, "An error occurred while adding roles to the group. Role not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while adding roles to the group. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while adding roles to the group. Please try again or contact support if the problem persists.");
        }
    }
    
    /**
     * @Route("/group/{id}/removeRoles", name="keycloak_admin_group_remove_roles", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function removeRolesAction(Request $request, string $id) {
        try{
            $data = json_decode($request->getContent(), true);
            $result = $this->get('nti.keycloak.admin.group.service')->removeRoles($id, $data);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 400, "An error occurred while removing roles from the group. Role not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while removing roles from the group. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while removing roles from the group. Please try again or contact support if the problem persists.");
        }
    }
}
