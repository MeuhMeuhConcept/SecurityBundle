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