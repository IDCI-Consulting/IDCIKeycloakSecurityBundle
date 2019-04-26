<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Validator;

use League\OAuth2\Client\Provider\AbstractProvider;
use Symfony\Component\HttpFoundation\Request;

interface TokenValidatorInterface
{
    public function validate(Request $request, AbstractProvider $provider);
}
