<?php

namespace NTI\KeycloakSecurityBundle\Service;

use AppBundle\Util\StringUtils;
use Doctrine\ORM\EntityManager;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
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

    /** @var EntityManager $em */
    private $em;

    /** @var array $auth */
    protected $auth;

    /** @var array $headers */
    protected $headers;

    /** @var string $baseUrl */
    protected $baseUrl;

    protected $environment;
    protected $username;
    protected $password;

    /**
     * RequestService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();

        $this->baseUrl = $this->container->getParameter(self::KEYCLOAK_SERVER_BASE_URL);
        $this->environment = $this->container->getParameter("environment");

        $configuration = $this->em->getRepository('KeycloakSecurityBundle:KeycloakApiConfiguration')->findOneBy(array("environment" => $this->environment));
        if(!$configuration)  {
            throw new Exception("No configuration was found for the Keycloak Api Config (".strtoupper($this->environment).").");
        }

        $this->username = $configuration->getEmail();
        $this->password = $configuration->getPassword();
        $accessToken = $configuration->getApiKey();

        if(!$accessToken){
            $token = $this->container->get('nti.keycloak.security.service')->getToken($this->username, $this->password);
            $accessToken = $token['access_token'];
            $configuration->setApiKey($accessToken);
            $this->em->persist($configuration);
            $this->em->flush();
        }

        $request = Request::createFromGlobals();
        $this->headers = array(
            "headers" => array(
                "Authorization" => "Bearer " . $accessToken,
                "IP", $request->server->get('HTTP_X_REAL_IP')
            )
        );
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function refreshToken(){
        $configuration = $this->em->getRepository('KeycloakSecurityBundle:KeycloakApiConfiguration')->findOneBy(array("environment" => $this->environment));
        if(!$configuration)  {
            throw new Exception("No configuration was found for the Keycloak Api Config (".strtoupper($this->environment).").");
        }

        $token = $this->container->get('nti.keycloak.security.service')->getToken($this->username, $this->password);
        $accessToken = $token['access_token'];

        $configuration->setApiKey($accessToken);
        $this->em->persist($configuration);
        $this->em->flush();

        // Prepare request options
        $request = Request::createFromGlobals();
        $this->headers = array(
            "headers" => array(
                "Authorization" => "Bearer " . $accessToken,
                "IP", $request->server->get('HTTP_X_REAL_IP')
            )
        );
    }

    /**
     * @param $path
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function restGet($path){
        try {
            $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
            //Check if cookies exists
            self::_checkCookie();
            $response = $client->request('GET', $path, $this->headers);
            //Make request and verify if response with 302
            if($response->code === 302){
                $this->container->get('session')->set('keycloak-cookie',$response->headers["set-cookie"]);
                $reponse_header = array_merge($this->options,[
                    "cookie" => $response->headers["set-cookie"],
                    'allow_redirects' => false
                ]);
                $response = $client->request('GET', $path, $this->headers);
            }
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            if($e->getResponse()->getStatusCode() === 401 || $e->getResponse()->getStatusCode() === 403){
                // Unauthorized, lets refresh the token
                $this->refreshToken();
                $response = $client->request('GET', $path, $this->headers);
            }
            return $response->getBody()->getContents();
        } catch(\Exception $e){
            return new Response("An unknown error occurred while processing the request.", 500, array());
        }
    }

    /**
     * @param $path
     * @param $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function restPost($path, $data, $type = "json"){
        try {
            $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
            //Check if cookies exists
            self::_checkCookie();
            $response = $client->request('POST', $path, array_merge($this->headers, array($type => $data)));
            //Make request and verify if response with 302
            if($response->code === 302){
                $this->container->get('session')->set('keycloak-cookie',$response->headers["set-cookie"]);
                $reponse_header = array_merge($this->options,[
                    "cookie" => $response->headers["set-cookie"],
                    'allow_redirects' => false
                ]);
                $response = $client->request('POST', $path, array_merge($this->headers, array($type => $data)));
            }
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            if($e->getResponse()->getStatusCode() === 401 || $e->getResponse()->getStatusCode() === 403){
                // Unauthorized, lets refresh the token
                $this->refreshToken();
                $response = $client->request('POST', $path, array_merge($this->headers, array($type => $data)));
            }
            return $response->getBody()->getContents();
        } catch(\Exception $e){
            return new Response("An unknown error occurred while processing the request.", 500, array());
        }
    }

    /**
     * @param $path
     * @param $data
     * @param string $type
     * @return string|Response
     * @throws GuzzleException
     */
    protected function restPut($path, $data, $type = "json"){
        try {
            $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
            //Check if cookies exists
            self::_checkCookie();
            $response = $client->request('PUT', $path, array_merge($this->headers, array($type => $data)));
            //Make request and verify if response with 302
            if($response->code === 302){
                $this->container->get('session')->set('keycloak-cookie',$response->headers["set-cookie"]);
                $reponse_header = array_merge($this->options,[
                    "cookie" => $response->headers["set-cookie"],
                    'allow_redirects' => false
                ]);
                $response = $client->request('PUT', $path, array_merge($this->headers, array($type => $data)));
            }
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            if($e->getResponse()->getStatusCode() === 401 || $e->getResponse()->getStatusCode() === 403){
                // Unauthorized, lets refresh the token
                $this->refreshToken();
                $response = $client->request('PUT', $path, array_merge($this->headers, array($type => $data)));
            }
            return $response->getBody()->getContents();
        } catch(\Exception $e){
            return new Response("An unknown error occurred while processing the request.", 500, array());
        }
    }

    /**
     * @param $path
     * @param $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function restPatch($path, $data, $type = "json"){
        try {
            $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
            //Check if cookies exists
            self::_checkCookie();
            $response = $client->request('PATCH', $path, array_merge($this->headers, array($type => $data)));
            //Make request and verify if response with 302
            if($response->code === 302){
                $this->container->get('session')->set('keycloak-cookie',$response->headers["set-cookie"]);
                $reponse_header = array_merge($this->options,[
                    "cookie" => $response->headers["set-cookie"],
                    'allow_redirects' => false
                ]);
                $response = $client->request('PATCH', $path, array_merge($this->headers, array($type => $data)));
            }
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            if($e->getResponse()->getStatusCode() === 401 || $e->getResponse()->getStatusCode() === 403){
                // Unauthorized, lets refresh the token
                $this->refreshToken();
                $response = $client->request('PATCH', $path, array_merge($this->headers, array($type => $data)));
            }
            return $response->getBody()->getContents();
        } catch(\Exception $e){
            return new Response("An unknown error occurred while processing the request.", 500, array());
        }
    }

    /**
     * @param $path
     * @return Response
     */
    protected function restDelete($path, $data = null, $type = "json"){
        try {
            $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
            //Check if cookies exists
            self::_checkCookie();
            $response = $client->request('DELETE', $path, array_merge($this->headers, array($type => $data)));
            //Make request and verify if response with 302
            if($response->code === 302){
                $this->container->get('session')->set('keycloak-cookie',$response->headers["set-cookie"]);
                $reponse_header = array_merge($this->options,[
                    "cookie" => $response->headers["set-cookie"],
                    'allow_redirects' => false
                ]);
                $response = $client->request('DELETE', $path, array_merge($this->headers, array($type => $data)));
            }
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            if($e->getResponse()->getStatusCode() === 401 || $e->getResponse()->getStatusCode() === 403){
                // Unauthorized, lets refresh the token
                $this->refreshToken();
                $response = $client->request('DELETE', $path, array_merge($this->headers, array($type => $data)));
            }
            return $response->getBody()->getContents();
        } catch(\Exception $e){
            return new Response("An unknown error occurred while processing the request.", 500, array());
        }
    }

    /**
     * @throws Exception
     */
    public function _checkCookie(){
        $cookie = $this->container->get('session')->get('keycloak-cookie') ?? null;
        if(null !== $cookie){
            $cookieObj = StringUtils::CreateCookieFromString($cookie);
            $now = new \DateTime();
            if($cookieObj["expires"] > $now->getTimestamp()){
                $this->headers = array_merge($this->headers,[
                    "cookie" => $cookie,
                    'allow_redirects' => false
                ]);
            }
        }
    }
}