<?php

namespace NTI\KeycloakSecurityBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use NTI\KeycloakSecurityBundle\Entity\KeycloakApiConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;

class KeycloakApiConfigurationService
{
    /** @var ContainerInterface $container */
    private $container;

    /** @var EntityManagerInterface $em */
    private $em;

    private $environment;

    /**
     * KeycloakApiConfigurationService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->environment = $this->container->getParameter("environment");
    }

    /**
     * @param null $accessToken
     * @return KeycloakApiConfiguration
     */
    public function updateAccessToken($accessToken = null)
    {
        if(!$accessToken) throw new \Exception("The accessToken is required.");

        /** @var KeycloakApiConfiguration $configuration */
        $configuration = $this->em->getRepository(KeycloakApiConfiguration::class)->findOneBy(array("environment" => $this->environment));

        if(!$configuration)  {
            throw new \Exception("No configuration was found for the Keycloak Api Config (".strtoupper($this->environment).").");
        }

        try{
            $configuration->setApiKey($accessToken);
            $this->em->persist($configuration);
            $this->em->flush();

            return $configuration;
        }catch (\Exception $ex){
            throw new \Exception("An unknown error occurred while updating the Keycloak Api Configuration.");
        }
    }
}