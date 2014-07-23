<?php

// Initialize the config file.
if (!@include_once("includes/config.php")) {
    die("config.php is missing or has the wrong permissions.");
}

if (DEBUG) {
    error_reporting(-1);
    ini_set('display_errors', 'On');
}

if ($MYSQL_CONFIG["DATABASE"] == "" || $MYSQL_CONFIG["HOSTNAME"] == "" || $MYSQL_CONFIG["USERNAME"] == "") {
    die("MySQL credentials are not configured.");
}

if (API_URL == "") {
    die("config.php: The API URL is not configured.");
}

if (!AUTH_REQUIRE_EMAIL && AUTH_EMAIL_EQUALS_USERNAME) {
    die("config.php: AUTH_REQUIRE_EMAIL cannot be false while AUTH_EMAIL_EQUALS_USERNAME is true.");
}


function method_not_allowed() {
    http_response_code(400);
    die(json_encode(array(
        "response" => "error",
        "text" => "Method not allowed."
    )));
}


function generate_unique_id($maxLength = SESSION_ID_LENGTH) {
    $entropy = '';

    // try ssl first
    if (function_exists('openssl_random_pseudo_bytes')) {
        $entropy = openssl_random_pseudo_bytes(64, $strong);
        // skip ssl since it wasn't using the strong algo
        if($strong !== true) {
            $entropy = '';
        }
    }

    // add some basic mt_rand/uniqid combo
    $entropy .= uniqid(mt_rand(), true);

    // try to read from the windows RNG
    if (class_exists('COM')) {
        try {
            $com = new COM('CAPICOM.Utilities.1');
            $entropy .= base64_decode($com->GetRandom(64, 0));
        } catch (Exception $ex) {
        }
    }

    // try to read from the unix RNG
    if (is_readable('/dev/urandom')) {
        $h = fopen('/dev/urandom', 'rb');
        $entropy .= fread($h, 64);
        fclose($h);
    }

    $hash = hash('whirlpool', $entropy);
    if ($maxLength) {
        return substr($hash, 0, $maxLength);
    }
    return $hash;
}


/**
 * Check a user's email address against the ISO standard.
 * @param  String  $email The email address to check
 * @return Boolean        True if this is a valid ISO email address, false otherwise.
 */
function check_email_address($email) {
    // First, we check that there's one @ symbol, 
    // and that the lengths are right.
    if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
        // Email invalid because wrong number of characters 
        // in one section or wrong number of @ symbols.
        return false;
    }
    // Split it into sections to make life easier
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
        if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
            return false;
        }
    }
    // Check if domain is IP. If not, 
    // it should be valid domain name
    if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
        $domain_array = explode(".", $email_array[1]);
        if (sizeof($domain_array) < 2) {
            return false; // Not enough parts to domain
        }
        for ($i = 0; $i < sizeof($domain_array); $i++) {
            if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
            return false;
            }
        }
    }
    return true;
}


function validate_session($session_id) {
    global $db;

    if (!isset($session_id)) {
        http_response_code(401);
        die(json_encode(array(
            "response" => "error",
            "text" => "An auth token is required."
        )));
    }

    $query = $db->prepare("SELECT user_id FROM sessions WHERE id = :id LIMIT 1");
    $query->execute(array(":id" => $session_id));
    $sessions = $query->fetchAll(PDO::FETCH_ASSOC);

    if (count($sessions) == 0) {
        http_response_code(401);
        die(json_encode(array(
            "response" => "error",
            "text" => "The provided session token has expired or is invalid."
        )));
    }

    $query = $db->prepare("SELECT * FROM users WHERE id = :user_id LIMIT 1");
    $query->execute(array(":user_id" => $user_id));
    $users = $query->fetchAll(PDO::FETCH_ASSOC);

    if (count($sessions) == 0) {
        http_response_code(401);
        die(json_encode(array(
            "response" => "error",
            "text" => "The provided session token has expired or is invalid."
        )));
    }

    return $users[0];
}
