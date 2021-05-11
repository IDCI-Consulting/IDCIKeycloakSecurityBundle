<?php

namespace NTI\KeycloakSecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * KeycloakApiConfiguration
 *
 * @ORM\Table(name="keycloak_api_configuration")
 * @ORM\Entity
 */

class KeycloakApiConfiguration
{

    const ENV_DEVELOPMENT = "dev";
    const ENV_PRODUCTION = "prod";
    const ENV_TEST = "test";

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="environment", type="string", length=255)
     */
    private $environment;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * var string
     * @ORM\Column(name="api_key", type="text")
     */
    private $apiKey;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set environment
     *
     * @param string $environment
     *
     * @return Configuration
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Get environment
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Configuration
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Configuration
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     *
     * @return Configuration
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }
}
