<?php

namespace NTI\KeycloakSecurityBundle\Controller\Admin\User;

use AppBundle\Util\DataTable\DataTableOptionsProcessor;
use AppBundle\Util\Rest\DataTableRestResponse;
use AppBundle\Util\Rest\RestResponse;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class KeycloakAdminController
 * @package NTI\KeycloakSecurityBundle\Controller\Admin\User
 * @Route("/")
 */
class KeycloakAdminUserController extends Controller {

    const API_MAX_RESULTS = 10;
    const UPDATE_PASSWORD_ACTION = "UPDATE_PASSWORD";

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
            'email' => isset($options['filters']['email']) ? $options['filters']['email'] : null,
            'firstName' => isset($options['filters']['firstName']) ? $options['filters']['firstName'] : null,
            'lastName' => isset($options['filters']['lastName']) ? $options['filters']['lastName'] : null,
            'username' => isset($options['filters']['username']) ? $options['filters']['username'] : null,
            'search' => isset($options['search']) ? $options['search'] : null, // Search in username, first or last name, or email
            'first' => isset($options['start']) ? $options['start'] : 0,
            'max' => isset($options['length']) ? $options['length'] : self::API_MAX_RESULTS,
        );
        $result = $this->get('nti.keycloak.admin.user.service')->getAll($options);
        return new RestResponse($result);
    }

    /**
     * @Route("/user/{id}", name="keycloak_admin_user_get", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getAction(Request $request, string $id) {

        try{
            $result = $this->get('nti.keycloak.admin.user.service')->get($id);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 404, "User not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while loading the User. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while loading the User. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/user", name="keycloak_admin_user_post", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @return RestResponse
     */
    public function postAction(Request $request) {
        try{
            $data = json_decode($request->getContent(), true);

            if(!isset($data['email']) || !$data['email'])
                return new RestResponse(null, 400, "The email is required.");

            $user = array(
                'username' => $data['username'],
                'email' => $data['email'],
                'enabled' => $data['enabled'],
                'emailVerified' => true,
                'firstName' => $data['firstName'],
                'lastName' => $data['lastName'],
                'credentials' => $data['credentials'],
                'attributes' => isset($data['attributes']) && $data['attributes'] ? $data['attributes'] : null,
                'requiredActions' => isset($data['requiredActions']) ? $data['requiredActions'] : null,
            );

            $result = $this->get('nti.keycloak.admin.user.service')->saveNewUser($user);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 400)
                return new RestResponse(null, 400, "An unknown error occurred while creating the user. Please check the information and try again.");
            else if($ex->getCode() == 403)
                return new RestResponse(null, 403, "You are not allowed to perform this action.");
            else if($ex->getCode() == 409)
                return new RestResponse(null, 400, "You are trying to create a user that already exists.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while creating the user. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while creating the user. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/user/{id}", name="keycloak_admin_user_put", options={"expose"=true}, methods={"PUT"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function putAction(Request $request, string $id) {
        try{
            $data = json_decode($request->getContent(), true);

            $userData = array(
                'enabled' => isset($data['enabled']) ? $data['enabled'] : null,
                'firstName' => isset($data['firstName']) ? $data['firstName'] : null,
                'lastName' => isset($data['lastName']) ? $data['lastName'] : null,
                'attributes' => isset($data['attributes']) && $data['attributes'] ? $data['attributes'] : null,
                'requiredActions' => isset($data['requiredActions']) ? $data['requiredActions'] : null,
            );

            // Validate password
            if (isset($data["password"]) && $data["password"] != "" && isset($data["confirmPassword"]) && $data["confirmPassword"] != "") {
                if ($data["password"] != $data["confirmPassword"]) {
                    return new RestResponse(null, 400, "Both passwords must match.");
                }

                $userData['credentials'] = array(
                    array(
                        'type' => 'password',
                        'value' => $data['password']
                    )
                );
            }

            $result = $this->get('nti.keycloak.admin.user.service')->updateUser($id, $userData);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 400)
                return new RestResponse(null, 400, "An unknown error occurred while updating the user. Please check the information and try again.");
            else if($ex->getCode() == 403)
                return new RestResponse(null, 403, "You are not allowed to perform this action.");
            else if($ex->getCode() == 409)
                return new RestResponse(null, 400, "You are trying to create a user that already exists.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while updating the user. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while updating the user. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/user/{id}/roles", name="keycloak_admin_user_get_roles", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getRolesAction(Request $request, string $id) {

        try{
            $result = $this->get('nti.keycloak.admin.user.service')->getRoles($id);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 404, "User roles not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while loading the User roles. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while loading the User roles. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/user/{id}/roles/available", name="keycloak_admin_user_get_roles_available", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getRolesAvailableAction(Request $request, string $id) {

        try{
            $result = $this->get('nti.keycloak.admin.user.service')->getRolesAvailable($id);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 404, "User roles not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while loading the User roles. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while loading the User roles. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/user/{id}/roles/composite", name="keycloak_admin_user_get_roles_composite", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return DataTableRestResponse
     */
    public function getRolesCompositeAction(Request $request, string $id) {

        try{
            $result = $this->get('nti.keycloak.admin.user.service')->getRolesComposite($id);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 404, "User roles not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while loading the User roles. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while loading the User roles. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/user/{id}/addRoles", name="keycloak_admin_user_add_roles", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function addRolesAction(Request $request, string $id) {
        try{
            /**
             * Example:
             * $data = array(
             *    array("id" => "abc123", "name" => "ROLE_ONE"),
             *    array("id" => "abc123", "name" => "ROLE_TWO")
             * )
             */

            $data = json_decode($request->getContent(), true);
            $result = $this->get('nti.keycloak.admin.user.service')->addRoles($id, $data);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 400, "An error occurred while adding roles to the user. Role not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while adding roles to the user. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while adding roles to the user. Please try again or contact support if the problem persists.");
        }
    }
    
    /**
     * @Route("/user/{id}/removeRoles", name="keycloak_admin_user_remove_roles", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function removeRolesAction(Request $request, string $id) {
        try{
            /**
             * Example:
             * $data = array(
             *    array("id" => "abc123", "name" => "ROLE_ONE"),
             *    array("id" => "abc123", "name" => "ROLE_TWO")
             * )
             */
            $data = json_decode($request->getContent(), true);
            $result = $this->get('nti.keycloak.admin.user.service')->removeRoles($id, $data);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 400, "An error occurred while removing roles from the user. Role not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while removing roles from the user. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while removing roles from the user. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/user/{id}/resetPassword", name="keycloak_admin_user_reset_password", options={"expose"=true}, methods={"POST"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function resetPasswordAction(Request $request, string $id) {
        try{
            $result = $this->get('nti.keycloak.admin.user.service')->resetPassword($id);
            return new RestResponse($result);
        } catch (ClientException $ex){
            if($ex->getCode() == 404)
                return new RestResponse(null, 400, "An error occurred while sending the reset password request. Role not found.");
            else
                return new RestResponse(null, 500, "An unknown error occurred while sending the reset password request. Please try again or contact support if the problem persists.");
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while sending the reset password request. Please try again or contact support if the problem persists.");
        }
    }

    /**
     * @Route("/user/{id}/impersonate", name="keycloak_admin_impersonate", options={"expose"=true}, methods={"GET"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function impersonateAction(Request $request, string $id) {
        try{
            $this->get('nti.keycloak.security.service')->impersonateUser($id);
            $defaultTargetPath = $this->container->getParameter('nti_keycloak_security.default_target_path');
            $redirect_route = $this->container->get('router')->generate($defaultTargetPath);
            return new RedirectResponse($redirect_route);
        } catch (\Exception $ex){
            return new RestResponse(null, 500, "An unknown error occurred while impersonating the user. Please try again or contact support if the problem persists.");
        }
    }
}