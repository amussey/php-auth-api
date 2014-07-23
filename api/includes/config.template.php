<?php

$MYSQL_CONFIG = array(
    "DATABASE" => "",
    "HOSTNAME" => "",
    "USERNAME" => "",
    "PASSWORD" => "",
    "PORT" => "3306"
);

define('DEBUG', false);
define('API_URL', '');
define('SESSION_ID_LENGTH', 34);

// AUTH_REQUIRE_EMAIL  AUTH_EMAIL_EQUALS_USERNAME
// true                true                        # email/password
// true                false                       # email/username/password
// false               true                        # Cannot exist.
// false               false                       # username/password
define('AUTH_REQUIRE_EMAIL', true);
define('AUTH_EMAIL_EQUALS_USERNAME', false);
