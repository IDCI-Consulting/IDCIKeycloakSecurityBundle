<?php

namespace IDCI\Bundle\KeycloakSecurityBundle\Provider;

use Firebase\JWT\JWT;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Keycloak extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'keycloak';

    /**
     * @var string
     */
    public $authServerPublicUrl = null;

    /**
     * @var string
     */
    public $authServerPrivateUrl = null;

    /**
     * @var string
     */
    public $realm = null;

    /**
     * @var string
     */
    public $encryptionAlgorithm = null;

    /**
     * @var string
     */
    public $encryptionKey = null;

    public function __construct(array $options = [], array $collaborators = [])
    {
        $this->authServerPublicUrl = $options['auth_server_public_url'];
        $this->authServerPrivateUrl = $options['auth_server_private_url'];
        $this->realm = $options['realm'];
        $this->encryptionAlgorithm = isset($options['encryption_algorithm']) ? $options['encryption_algorithm'] : null;
        $this->encryptionKey = isset($options['encryption_key']) ? $options['encryption_key'] : null;

        if (isset($options['encryption_key_path'])) {
            $this->setEncryptionKeyPath($options['encryption_key_path']);
            unset($options['encryption_key_path']);
        }

        parent::__construct($options, $collaborators);
    }

    public function decryptResponse($response): array
    {
        if (!is_string($response)) {
            return $response;
        }

        if (!$this->usesEncryption()) {
            throw EncryptionConfigurationException::undeterminedEncryption();
        }

        return json_decode(
            json_encode(
                JWT::decode(
                    $response,
                    $this->encryptionKey,
                    [$this->encryptionAlgorithm]
                )
            ),
            true
        );
    }

    public function getBaseAuthorizationUrl(): string
    {
        return sprintf('%s/protocol/openid-connect/auth', $this->getPublicBaseUrlWithRealm());
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return sprintf('%s/protocol/openid-connect/token', $this->getPrivateBaseUrlWithRealm());
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return sprintf('%s/protocol/openid-connect/userinfo', $this->getPrivateBaseUrlWithRealm());
    }

    public function getLogoutUrl(array $options = [])
    {
        $base = $this->getBaseLogoutUrl();
        $params = $this->getAuthorizationParameters($options);
        $query = $this->getAuthorizationQuery($params);

        return $this->appendQuery($base, $query);
    }

    public function getAccountUrl(): string
    {
        return sprintf('%s/account', $this->getPublicBaseUrlWithRealm());
    }

    public function getPublicBaseUrlWithRealm(): string
    {
        return sprintf('%s/realms/%s', $this->authServerPublicUrl, $this->realm);
    }

    public function getPrivateBaseUrlWithRealm(): string
    {
        return sprintf('%s/realms/%s', $this->authServerPrivateUrl, $this->realm);
    }

    public function getBaseApiUrlWithRealm(): string
    {
        return sprintf('%s/admin/realms/%s', $this->authServerPrivateUrl, $this->realm);
    }

    private function getBaseLogoutUrl(): string
    {
        return sprintf('%s/protocol/openid-connect/logout', $this->getPublicBaseUrlWithRealm());
    }

    protected function getDefaultScopes(): array
    {
        return ['name', 'email'];
    }

    protected function checkResponse(ResponseInterface $response, $data)
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

    public function getResourceOwner(AccessToken $token): KeycloakResourceOwner
    {
        $response = $this->fetchResourceOwnerDetails($token);
        $response = $this->decryptResponse($response);

        return $this->createResourceOwner($response, $token);
    }

    public function setEncryptionAlgorithm($encryptionAlgorithm): self
    {
        $this->encryptionAlgorithm = $encryptionAlgorithm;

        return $this;
    }

    public function setEncryptionKey($encryptionKey): self
    {
        $this->encryptionKey = $encryptionKey;

        return $this;
    }

    public function setEncryptionKeyPath($encryptionKeyPath): self
    {
        try {
            $this->encryptionKey = file_get_contents($encryptionKeyPath);
        } catch (Exception $e) {
            // Not sure how to handle this yet.
        }

        return $this;
    }

    public function usesEncryption(): bool
    {
        return (bool) $this->encryptionAlgorithm && $this->encryptionKey;
    }
}
