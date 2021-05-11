<?php

namespace NTI\KeycloakSecurityBundle\Service;

use AppBundle\Util\StringUtils;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\RequestException;
use League\OAuth2\Client\Token\AccessToken;
use NTI\KeycloakSecurityBundle\Security\User\KeycloakUser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class KeycloakSecurityService {

    const
        GET_TOKEN_URL = "/protocol/openid-connect/token",
        INTROSPECT_TOKEN_URL = "/protocol/openid-connect/token/introspect",
        PASSWORD_GRANT_TYPE = 'password',
        TOKEN_EXCHANGE_GRANT_TYPE = 'urn:ietf:params:oauth:grant-type:token-exchange',

        // Endpoints CraueConfigBundle configuration keys
        KEYCLOAK_SERVER_BASE_URL = "keycloak_server_base_url",
        KEYCLOAK_REALM = "keycloak_realm",
        KEYCLOAK_CLIENT_ID = "keycloak_client_id",
        KEYCLOAK_CLIENT_ID_CODE = "keycloak_client_id_code",
        KEYCLOAK_CLIENT_SECRET = "keycloak_client_secret",

        // Authentication types
        AUTH_TYPE_BASIC = "basic",
        AUTH_TYPE_BEARER = "bearer"
    ;

    protected $securityPath = "/auth/realms/{realm}";

    /** @var ContainerInterface $container */
    protected $container;

    /** @var EntityManagerInterface $em */
    private $em;

    /** @var array $auth */
    protected $auth;

    /** @var array $headers */
    protected $headers = [];

    /** @var string $baseUrl */
    protected $baseUrl;

    protected $environment;
    protected $username;
    protected $password;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();

        $this->baseUrl = $this->container->getParameter(self::KEYCLOAK_SERVER_BASE_URL);
        $this->securityPath = str_replace("{realm}", $this->container->getParameter(self::KEYCLOAK_REALM), $this->securityPath);
        $this->environment = $this->container->getParameter("environment");

        $configuration = $this->em->getRepository('KeycloakSecurityBundle:KeycloakApiConfiguration')->findOneBy(array("environment" => $this->environment));
        if(!$configuration)  {
            throw new Exception("No configuration was found for the Keycloak Api Config (".strtoupper($this->environment).").");
        }

        $this->username = $configuration->getEmail();
        $this->password = $configuration->getPassword();
        $accessToken = $configuration->getApiKey();

        if(!$accessToken){
            $token = $this->getToken($this->username, $this->password);
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

    public function getToken($username, $password) {
        $url = $this->securityPath.self::GET_TOKEN_URL;

        $options = array(
            'client_id' => $this->container->getParameter(self::KEYCLOAK_CLIENT_ID),
            'client_secret' => $this->container->getParameter(self::KEYCLOAK_CLIENT_SECRET),
            'username' => $username,
            'password' => $password,
            'grant_type' => $this::PASSWORD_GRANT_TYPE
        );

        $result = $this->restPost($url, $options, 'form_params');

        $response = json_decode($result, true);

        return $response;
    }

    public function introspectToken($username, $token) {
        $url = $this->baseUrl.$this->securityPath.self::INTROSPECT_TOKEN_URL;

        $options = array(
            'client_id' => $this->container->getParameter(self::KEYCLOAK_CLIENT_ID),
            'client_secret' => $this->container->getParameter(self::KEYCLOAK_CLIENT_SECRET),
            'username' => $username,
            'token' => $token
        );

        $result = $this->restPost($url, $options, 'form_params');
        $response = json_decode($result, true);

        if(isset($response['active']) && $response['active'] == false){
            return false;
        }

        return $response;
    }

    /** 
     * User Id to impersonate
     * @var $userId 
     */
    public function impersonateUser($userId) {

        // Load the current token from the logged user (Must have impersonation access)
        $user = $this->container->get('app.user')->getLoggedUser();
        $token = $user->getKeycloakAccessToken()->getToken();

        // Prepare the impersonation request. This will request a token exchange, sending the current token and receiving a new token on behalf of the new userId
        $options = array(
            'client_id' => $this->container->getParameter(self::KEYCLOAK_CLIENT_ID),
            'client_secret' => $this->container->getParameter(self::KEYCLOAK_CLIENT_SECRET),
            'grant_type' => $this::TOKEN_EXCHANGE_GRANT_TYPE,
            'subject_token' => $token,
            'requested_subject' => $userId
        );

        $url = $this->baseUrl.$this->securityPath.self::GET_TOKEN_URL;
        $result = $this->restPost($url, $options, 'form_params');
        $response = json_decode($result, true);

        // Parse the new impersonated the access token 
        $accessToken = new AccessToken($response);
        $newUser = $this->container->get('NTI\KeycloakSecurityBundle\Security\User\KeycloakUserProvider')->loadUserByUsername($accessToken);
        $newToken = new PostAuthenticationGuardToken($newUser, 'main', $newUser->getRoles());

        // Invalidate previous session
        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->container->get('security.token_storage');
        $oldToken = $tokenStorage->getToken();
        $oldUser = $oldToken->getUser();

        if (!newUser instanceof KeycloakUser) {
            throw new \RuntimeException('The user must be an instance of KeycloakUser');
        }

        $tokenStorage->setToken(null);
        $this->container->get('session')->clear();

        // Add impersonated token to new session
        $tokenStorage->setToken($newToken);
        $this->container->get('session')->set('session_impersonation_user_id', $newUser->getId());

        return $newUser;
    }



    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function refreshToken(){
        $this->em = $this->container->get('doctrine')->getManager();

        $configuration = $this->em->getRepository('KeycloakSecurityBundle:KeycloakApiConfiguration')->findOneBy(array("environment" => $this->environment));

        if(!$configuration)  {
            throw new Exception("No configuration was found for the Keycloak Api Config (".strtoupper($this->environment).").");
        }

        $token = $this->getToken($this->username, $this->password);
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
    protected function restGet($path,$isAdmin = false){
        $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
        try {
            //Check if cookies exists
            self::_checkCookie();
            $response = $client->request('GET', $path, $this->headers);
            //Make request and verify if response with 302
            if($response->getStatusCode() === 302){
                $this->container->get('session')->set('keycloak-cookie',$response->headers["set-cookie"]);
                $reponse_header = array_merge($this->headers,[
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
    protected function restPost($path, $data, $type = "json",$isAdmin = false){
        try {
            $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
            //Check if cookies exists
            $response = $client->request('POST', $path, array_merge($this->headers ?? [], array($type => $data)));
            //Make request and verify if response with 302
            if($response->getStatusCode() === 302){
                $this->container->get('session')->set('keycloak-cookie',$response->headers["set-cookie"]);
                $reponse_header = array_merge($this->headers,[
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
    protected function restPut($path, $data, $type = "json",$isAdmin = false){
        try {
            $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
            //Check if cookies exists
            self::_checkCookie();
            $response = $client->request('PUT', $path, array_merge($this->headers, array($type => $data)));
            //Make request and verify if response with 302
            if($response->getStatusCode() === 302){
                $this->container->get('session')->set('keycloak-cookie',$response->headers["set-cookie"]);
                $reponse_header = array_merge($this->headers,[
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
    protected function restPatch($path, $data, $type = "json",$isAdmin = false){
        try {
            $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
            //Check if cookies exists
            self::_checkCookie();
            $response = $client->request('PATCH', $path, array_merge($this->headers, array($type => $data)));
            //Make request and verify if response with 302
            if($response->getStatusCode() === 302){
                $this->container->get('session')->set('keycloak-cookie',$response->headers["set-cookie"]);
                $reponse_header = array_merge($this->headers,[
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
    protected function restDelete($path, $data = null, $type = "json", $isAdmin = false){
        try {
            $client = new \GuzzleHttp\Client(array('base_uri' => $this->baseUrl));
            //Check if cookies exists
            self::_checkCookie();
            $response = $client->request('DELETE', $path, array_merge($this->headers, array($type => $data)));
            //Make request and verify if response with 302
            if($response->getStatusCode() === 302){
                $this->container->get('session')->set('keycloak-cookie',$response->headers["set-cookie"]);
                $reponse_header = array_merge($this->headers,[
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
