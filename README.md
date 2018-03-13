# Drupal Install CLI

Provide a command for installing drupal with drush from the command line 

Using [Drush](http://www.drush.org) for install Drupal site.

-----

## Requirements
- PHP ^7.1
- [Composer](https://getcomposer.org) package manager
- Installed and executable [Drush](http://www.drush.org) a command line shell for DRUPAL
- [symfony/console](https://github.com/symfony/console) ^2.1 || ^3.0 || ^4.0

## Install
Via [Composer](https://getcomposer.org/)
```bash
composer require awd-studio/drupal-install-cli
```

Or add a dependency on **awd-studio/drupal-install-cli** to your project’s composer.json file:
```json
{
    "require": {
        "awd-studio/drupal-install-cli": "dev"
    }
}
```

## Usage:

Add command to your project's composer.json, to the "scripts" directive:
```json
{
    "scripts": {
        "dru-install": "vendor/bin/drupal-install-cli  drupal:site-install"
    }
}
```
And run: 
```bash
composer dru-install
```

Or call from CLI:
```bash
vendor/bin/drupal-install-cli drupal:site-install --db-host='localhost' --db-user='[MY_DB_USER]' --db-name='[MY_DB_NAME]' --db-pass='[MY_DB_PASS]'
```

## Available options:
- **--db-host** - Database host
- **--db-name** - Database name
- **--db-user** - Database user name
- **--db-pass** - Database user password
- **--profile** - Installation profile name
- **--site-name** - Your future site name
- **--site-mail** - Site E-mail
- **--admin-login** - Admin user name
- **--admin-pass** - Admin user password
- **--admin-mail** - Admin user E-mail
