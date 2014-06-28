# Basic PHP Auth API

This is a small, bare-bones PHP authentication API and client-facing login system.  It includes:

 - The REST JSON API for Authentication.
 - A simple PHP app that allows authentication against said API.
 - .SQL files to create the database tables required for this example.

# API

## Common Errors

# Application

## Common Errors

    config.php is missing or has the wrong permissions.

The server is missing a `config.php` file.  Make a copy of `php/config.template.php` named `php/config.php` and fill in the fields inside the file.

    MySQL credentials are not configured.

Your server's `config.php` file is missing MySQL authentication information.  Fill in the `$MYSQL_CONFIG` array.

    The API URL is not configured.

Your server's `config.php` file is missing an API URL.  Fill in a value for the `$API_URL` variable.

