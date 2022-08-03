# Setup Protocol


## docker
```sh



```
## symfony base

### Skeleton / Easyadmin

```
make inside

composer create-project symfony/website-skeleton .
composer require easycorp/easyadmin-bundle
php bin/console make:admin:dashboard 
```

## Security

https://symfony.com/doc/5.2/security.html#1-installation
```
composer require symfony/security-bundle
php bin/console ma:user
php bin/console make:auth
php bin/console make:registration-form
```

composer require symfonycasts/verify-email-bundle
    1) Install some missing packages:
        composer require symfonycasts/verify-email-bundle
    2) In RegistrationController::verifyUserEmail():
        * Customize the last redirectToRoute() after a successful email verification.
        * Make sure you're rendering success flash messages or change the $this->addFlash() line.
    3) Review and customize the form, controller, and templates as needed.
    4) Run "php bin/console make:migration" to generate a migration for the newly added User::isVerified property.

```


composer require symfonycasts/reset-password-bundle 
php bin/console make:reset-password

 Next:
   1) Run "php bin/console make:migration" to generate a migration for the new "App\Entity\ResetPasswordRequest" entity.
   2) Review forms in "src/Form" to customize validation and labels.
   3) Review and customize the templates in `templates/reset_password`.
   4) Make sure your MAILER_DSN env var has the correct settings.
   5) Create a "forgot your password link" to the app_forgot_password_request route on your login form.

 Then open your browser, go to "/reset-password" and enjoy!
```

## Password Hash
composer require symfony/password-hasher


## Fix Mailer
symfony/mailer wont send
in config/packages/messenger.yaml comment out

    messenger:
        #failure_transport: failed
    [...]
    #Symfony\Component\Mailer\Messenger\SendEmailMessage: async

## Make migration
```
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

# timezone
into .env / services.yml
Kernel boot()

# ORM
composer require symfony/orm-pack


## Traits
composer require stof/doctrine-extensions-bundle
/config/bundles.php => Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class => ['all' => true],

### Softdelete
composer require gedmo/doctrine-extensions

use ActiveTrait;
use TimestampableCreatedTrait;
use TimestampableUpdatedTrait;
use TimestampableDeletedTrait;

### UUID Trait

composer require symfony/uid
composer require ramsey/uuid

use Ramsey\Uuid\Uuid;

https://symfony.com/doc/current/frontend/encore/installation.html
composer require symfony/webpack-encore-bundle

## fixtures
composer require orm-fixtures --dev 
php bin/console doctrine:fixtures:load --group=setup

## symfony/mailer wont send
in config/packages/messenger.yaml comment out

    messenger:
        #failure_transport: failed
    [...]
    #Symfony\Component\Mailer\Messenger\SendEmailMessage: async