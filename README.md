# SecurityBundle
Provides user management for Symfony3 Project.


## CONFIGURATION
You have to enable CAST syntax on DQL, add these configuration for doctrine
```
orm:
    entity_managers:
      default:
        dql:
          string_functions:
            CAST: AppBundle\Doctrine\DQL\PostgreSQL\Cast
```
