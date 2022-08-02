# Symfony 5 Stack

## Features

## stack

- Docker (Database, PHP, Nginx)
- Symfony 5.1 User/Security
- Bootstrap
- EasyAdmin 3

## app

- security
- login
- create user as admin
- reset password
- mailer (register, reset)


# Install dev environment
```sh
sudo docker-compose up -d
make setup-app
```


## Usage
Open browser: [Localhost](http://localhost:8001)

Login with one of created users:
```
superadmin@foo.bar / admin (superadmin user)
admin@foo.bar / admin (admin user)
user@foo.bar / admin (regular user)
```

Send email tests with:
```
php bin/console app:send-email --template=register --email=user@example.com
```

## Config
Configure Mailer for register and password reset:
```
MAILER_DSN
MAILER_FROM
MAILER_FROM
```
[How to configure mailtrap for mailer](https://blog.mailtrap.io/send-emails-in-symfony/)

[How to configure gmail for mailer](https://symfony.com/doc/current/email.html#using-gmail-to-send-emails)

Modify and generate emails with mjml:
```
npm install
npm run build:email
```
Put your custom css in:
```
public/css/admin.css
```
Check examples of templates overriding in:
```
/templates/bundles/
/templates/reset_password/
```

## Code style
Run PHP CS Fixer with:
```
vendor/bin/php-cs-fixer fix -v
```
You will find rules in: `.php_cs.dist`