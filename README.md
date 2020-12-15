# SecurityBundle
Provides user management for Symfony3 Project.


## CONFIGURATION
Typical `config/packages/mmc_security.yaml`
```
mmc_security:
    sessionTTL:
        anonymous: 600
    logout:
        - 'api' // Connect LogoutLister to the firewall 'api'

```

Typical `config/packages/security.yaml`
```
security:
    # https://symfony.com/doc/current/security.html#where-do-users-come-from-user-providers
    encoders:
        Symfony\Component\Security\Core\User\User: bcrypt
        Mmc\Security\User\User: bcrypt
        Mmc\Security\Entity\User: bcrypt

    providers:
        chain_provider:
            chain:
                providers: [users_in_memory, mmc_user]

        users_in_memory:
            memory:
                users:
                    admin: { password: '%env(ADMIN_PASSWORD)%', roles: [ 'ROLE_ADMIN' ] }
        mmc_user:
            id: security.user.provider.mmc

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        public:
            pattern:  ^/api/public
            stateless: true
            anonymous: true

        login:
            pattern:  ^/api/login
            stateless: true
            anonymous: true
            mmc_login:
                check_path:               /api/login_check
                success_handler:          lexik_jwt_authentication.handler.authentication_success
                failure_handler:          lexik_jwt_authentication.handler.authentication_failure
                authenticators:
                    - security.authentication.provider.mmc.authenticators.token_by_email
                    - security.authentication.provider.mmc.authenticators.username_password
                    - security.authentication.provider.mmc.authenticators.anonymous
            provider: chain_provider

        api:
            pattern:   ^/api
            stateless: true
            guard:
                authenticators:
                    - lexik_jwt_authentication.jwt_token_authenticator
            logout:
                path:    /api/logout
            provider: chain_provider

    # Easy way to control access for large sections of your site
    # Note: Only the *first* access control that matches will be used
    access_control:
        - { path: ^/api/public, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/api,       roles: IS_AUTHENTICATED_FULLY }

```

### Connect security factory
In your `src/Kernel.php`, add the folowing lines
```
...
use Mmc\Security\DependencyInjection\Security\Factory\MmcLoginFactory;
...

    public function build(ContainerBuilder $container)
    {
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new MmcLoginFactory());
    }
...
```

### Add login_check route
Don't forget to add this route to your `config/routes.yaml`
```
api_login_check:
    path: /api/login_check
```

#### Logout
To enable logout, add this route
```
api_logout:
    path: /api/logout
```

### If you use lexit/jwt-authentication-bundle
Add this service in your project
```
<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Mmc\Security\Authentication\Token\MmcToken;
use Mmc\Security\Service\SessionTTLProvider;
use Mmc\Security\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JWTCreatedListener
{
    protected $tokenStorage;

    protected $sessionTTLProvider;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        SessionTTLProvider $sessionTTLProvider
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->sessionTTLProvider = $sessionTTLProvider;
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $token = $this->tokenStorage->getToken();

        if (!$token || !$token instanceof MmcToken) {
            return;
        }

        $user = $token->getUser();

        if (!$user || !$user instanceof UserInterface) {
            return;
        }

        $ttl = $this->sessionTTLProvider->getSessionTTL($token->getType());
        if ($token->hasExtra('rememberMe') && $token->getExtra('rememberMe')) {
            $ttl = 2678400; // 60 * 60 * 24 * 31
        }

        $payload = $event->getData();

        $payload['exp'] = time() + $ttl;

        $payload['authType'] = $user->getType();
        //$payload['name'] = $user->getName();
        //$payload['firstname'] = $user->getFirstname();

        $event->setData($payload);
    }
}
```

### Doctrine configuration
Create a `config/packages/doctrine-extensions.yaml` with this content to enable Gedmo TimestampableListener
```
services:
    Gedmo\Timestampable\TimestampableListener:
        class: Gedmo\Timestampable\TimestampableListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ "@annotation_reader" ] ]
```

and add that configuration to your `config/packages/doctrine.yaml`
```
doctrine:
    orm:
        dql:
            string_functions:
                CAST: Mmc\Security\Doctrine\DQL\Postgre\Cast
```
