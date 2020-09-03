<?php

namespace NTI\KeycloakSecurityBundle\Controller\Admin\User;

use AppBundle\Util\DataTable\DataTableOptionsProcessor;
use AppBundle\Util\Rest\DataTableRestResponse;
use AppBundle\Util\Rest\RestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class KeycloakAdminController
 * @package NTI\KeycloakSecurityBundle\Controller\Admin\User
 * @Route("/")
 */
class KeycloakAdminUserController extends Controller {

    const API_MAX_RESULTS = 10;

    // REST Methods
    /**
     * @Route("/users/getAll", name="glbs_keycloak_admin_users_get_all", options={"expose"=true}, methods={"GET"})
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

}