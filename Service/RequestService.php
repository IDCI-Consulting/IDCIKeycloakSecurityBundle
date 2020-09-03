<?php

namespace NTI\KeycloakSecurityBundle\Service;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User\User;
use AppBundle\Service\User\UserService;
use Symfony\Component\HttpFoundation\Request;

class RequestService {

    // Endpoints CraueConfigBundle configuration keys
    const KEYCLOAK_SERVER_BASE_URL = "keycloak_server_base_url";
    const KEYCLOAK_REALM = "keycloak_realm";
    const KEYCLOAK_CLIENT_ID = "keycloak_client_id";
    const KEYCLOAK_CLIENT_ID_CODE = "keycloak_client_id_code";
    const KEYCLOAK_CLIENT_SECRET = "keycloak_client_secret";

    // Authentication types
    const AUTH_TYPE_BASIC = "basic";
    const AUTH_TYPE_BEARER = "bearer";

    /** @var ContainerInterface $container */
    protected $container;

    /** @var array $auth */
    protected $auth;

    /** @var array $headers */
    protected $headers;

    /** @var string $baseUrl */
    protected $baseUrl;

    /**
     * RequestService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;

        $this->baseUrl = $this->container->getParameter(self::KEYCLOAK_SERVER_BASE_URL);

        /** @var User $user */
        $user = $this->container->get(UserService::class)->getLoggedUser();
        if($user){
            $token = $user->getAccessToken()->getToken();
            $this->auth = array("Authorization" => "Bearer " . $token);

            $request = Request::createFromGlobals();
            $this->auth["IP"] = $request->server->get('HTTP_X_REAL_IP');
        } else {
            $this->auth = array();
        }

        $this->headers = array(
            "headers" => $this->auth
        );
    }

    /**
     * @param $path
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function restGet($path){
        $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
        $response = $client->request('GET', $path, $this->headers);
        return $response->getBody()->getContents();
    }

    /**
     * @param $path
     * @param $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function restPost($path, $data){
        $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
        $response = $client->request('POST', $path, array_merge($this->headers, array(
            "json" => $data
        )));
        return $response->getBody()->getContents();
    }

    protected function restPut($path, $data){
        $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));


        $response = $client->request('PUT', $path, array_merge($this->headers, array(
            "json" => $data
        )));
        return $response->getBody()->getContents();
    }

    /**
     * @param $path
     * @param $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function restPatch($path, $data){
        $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
        $response = $client->request('PATCH', $path, array_merge($this->headers, array(
            "json" => $data
        )));
        return $response->getBody()->getContents();
    }

    /**
     * @param $path
     * @return Response
     */
    protected function restDelete($path, $data = null){
        $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
        $response = null;
        try {
            $response = $client->request('DELETE', $path, array_merge($this->headers, array(
                "json" => $data
            )));
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            }
        } catch (GuzzleException $e) {
            $this->container->get('logger')->log(Logger::ERROR, $e->getTraceAsString());
        }
        if($response) {
            return new Response($response->getBody()->getContents(), $response->getStatusCode(), $response->getHeaders());
        }
        return new Response("An unknown error occurred while processing the request.", 500, array());
    }

}