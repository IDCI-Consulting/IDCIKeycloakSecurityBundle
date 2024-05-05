<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class KeycloakProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'keycloak';

    /**
     * @var string use to identify the "public"" way to call the auth server
     */
    private const MODE_PUBLIC = 'public';

    /**
     * @var string use to identify the "private"" way to call the auth server
     */
    private const MODE_PRIVATE = 'private';

    public ?string $authServerPublicUrl = null;

    public ?string $authServerPrivateUrl = null;

    public ?string $realm = null;

    public function __construct(array $options = [], array $collaborators = [])
    {
        $this->authServerPublicUrl = $options['auth_server_public_url'] ?? $options['auth_server_url'];
        $this->authServerPrivateUrl = $options['auth_server_private_url'] ?? $options['auth_server_url'];
        $this->realm = $options['realm'];

        parent::__construct($options, $collaborators);
    }

    public function decryptResponse($response): array
    {
        if (!is_string($response)) {
            return $response;
        }

        throw new \Exception('Encryption is not yet supported');
    }

    /**
     * Creates base url from provider configuration.
     *
     * @param string $mode ("MODE_PUBLIC" / "MODE_PRIVATE")
     *
     * @return string
     */
    public function getBaseUrl(string $mode = self::MODE_PUBLIC): ?string
    {
        return self::MODE_PRIVATE === $mode ? $this->authServerPrivateUrl : $this->authServerPublicUrl;
    }

    public function getBaseUrlWithRealm($mode): string
    {
        return sprintf('%s/realms/%s', $this->getBaseUrl($mode), $this->realm);
    }

    public function getResourceOwnerManageAccountUrl(): string
    {
        return sprintf('%s/account', $this->getBaseUrlWithRealm(self::MODE_PUBLIC));
    }

    public function getBaseAuthorizationUrl(): string
    {
        return sprintf('%s/protocol/openid-connect/auth', $this->getBaseUrlWithRealm(self::MODE_PUBLIC));
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return sprintf('%s/protocol/openid-connect/token', $this->getBaseUrlWithRealm(self::MODE_PRIVATE));
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return sprintf('%s/protocol/openid-connect/userinfo', $this->getBaseUrlWithRealm(self::MODE_PRIVATE));
    }

    public function getTokenIntrospectionUrl(): string
    {
        return sprintf('%s/protocol/openid-connect/token/introspect', $this->getBaseUrlWithRealm(self::MODE_PRIVATE));
    }

    private function getBaseLogoutUrl(): string
    {
        return sprintf('%s/protocol/openid-connect/logout', $this->getBaseUrlWithRealm(self::MODE_PUBLIC));
    }

    public function getBaseApiUrlWithRealm(): string
    {
        return sprintf('%s/admin/realms/%s', $this->getBaseUrl(self::MODE_PRIVATE), $this->realm);
    }

    public function getLogoutUrl(array $options = []): string
    {
        $base = $this->getBaseLogoutUrl();
        $params = $this->getAuthorizationParameters($options);

        if (isset($options['access_token'])) {
            $accessToken = $options['access_token'];
            $params['id_token_hint'] = $accessToken->getValues()['id_token'];
            $params['post_logout_redirect_uri'] = $params['redirect_uri'];
        }
        unset($params['redirect_uri']);

        $query = $this->getAuthorizationQuery($params);

        return $this->appendQuery($base, $query);
    }

    public function getResourceOwner(AccessToken $token): KeycloakResourceOwner
    {
        $response = $this->fetchResourceOwnerDetails($token);
        $response = $this->decryptResponse($response);

        return $this->createResourceOwner($response, $token);
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    protected function getDefaultScopes(): array
    {
        return ['openid', 'profile', 'email', 'roles'];
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (!empty($data['error'])) {
            $error = sprintf('%s: %s', $data['error'], $data['error_description']);

            throw new IdentityProviderException($error, 0, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): KeycloakResourceOwner
    {
        return new KeycloakResourceOwner($response, $token);
    }

    protected function getAllowedClientOptions(array $options): array
    {
        return ['timeout', 'proxy', 'verify'];
    }
}
