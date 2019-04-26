<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Validator;

use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\AbstractProvider;
use Symfony\Component\HttpFoundation\Request;

class BearerTokenValidator implements TokenValidatorInterface
{
    public function validate(Request $request, AbstractProvider $provider)
    {
        if (!$request->headers->has('Authorization')) {
            return false;
        }

        $authorization = $request->headers->get('Authorization');
        $token = trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $authorization));

        try {
            $response = (new Client())->request('POST', $provider->getTokenIntrospectionUrl(), [
                'auth' => [$provider->getClientId(), $provider->getClientSecret()],
                'form_params' => [
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            return false;
        }

        $result = json_decode($response->getBody(), true);

        if (!$result['active']) {
            return false;
        }

        dump($result);
        die();

        return $jwt;
    }
}
