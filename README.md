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
