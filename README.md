# Symfony6 Admin Stack 

This stack contains some essentials to quick start  your symfony project:

- **Docker**: dockerized development environment
- **Authentication**: [Registration](https://symfonycasts.com/screencast/symfony-security/registration-auth), [Email verfication](https://symfonycasts.com/screencast/symfony-security/verify-email), [Password Reset](https://symfonycasts.com/screencast/symfony-security/verify-email), [two-factor authentication (2FA)](https://symfony.com/bundles/SchebTwoFactorBundle/6.x/index.html) with BackupCodes, TrustedDevice
- **EasyAdmin 4.0**: [EasyAdmin](https://github.com/EasyCorp/EasyAdminBundle) is a fast, beautiful and modern admin generator for Symfony applications.
- **Email Templates**:  most used elements  (header, footer, button) in separated files for an easy include
- **Multiple Languages**: the core features are in translation fiels, easy implementation of other languages
- **API Plattform**: The [API Platform](https://api-platform.com/) with token Authentication is ready to use.
- **Traits**: [Reusable Entityfields](https://github.com/ganti/symfony6-stack/tree/main/app/src/Entity/Traits): createdAt, updatedAt, deletedAt (softdelete), isActive, UUID
- **Database Logging**: Most critical and technical issues shall be logged into files, but some user actions may be logged into the database. A simple [Logging engine](https://github.com/ganti/symfony6-stack/blob/main/app/.env.dev) is used to log events into the database
- **PHP Coding Standards Fixer**: normalize your PHP code with [CS-fixer](https://cs.symfony.com/)



<table>
  <tr>
    <td colspan=2>
        <img src="/.github/img/login_forms.png?" alt="Ready to use Forms (Registration, mail verification, Login, 2FA, password reset"><br>
        <img src="/.github/img/logs.png" alt="simple user event logging">
    </td>
  </tr>
  <tr>
    <td valign="top">
        <img src="/.github/img/2fa_setup.png" alt="two setup autentication setup">
        <img src="/.github/img/light_dark.png" alt="Easyadmin with light and dark mode">
    </td>
    <td valign="top" width=50%>
        <img src="/.github/img/email.png" alt="Structured emailtemplate">
    </td>
  </tr>
 </table>


## Installation

```bash
    sudo docker-compose up -d
    make setup-app
```

Browse to [localhost:8001/login](http://localhost:8001/login) and use admin:admin as credentials. Do not forget to change the password!

### Main configuration points
- **app/.env.dev**/**app/.env.prod**: DATABASE_URL, MAILER_DSN
- **app/config/packages/parameters.yaml**: App configuration (Locale, Timezone, Mailer settings)
-  **app/config/*.yaml**: many other settings

### Makefile
Here are a few make commands

 - `make setup-app`: Setup
 - `make inside` jump inside php-container 
 - `make db-reset`: Reset DB in development (mostly used after changing an entity, but no new migration is created
 - `make cs-check` displays what cs fixes are executed when using `make cs-fix`
 - `make symfony command="make:entity"` execute symfony console commands with make
 - `make composer command="require foobar"` executed composer commands with make

