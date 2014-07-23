<?php

require_once("includes/init.php");

if (isset($_GET['create'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (AUTH_EMAIL_EQUALS_USERNAME) {
            if (!isset($_POST['email']) || trim($_POST['email']) == '') {
                die(json_encode(array(
                    "response" => "error",
                    "text" => "POST contains missing or invalid 'email' parameter."
                )));
            }
            $_POST['username'] = $_POST['email'];
        } else {
            if (!isset($_POST['username']) || trim($_POST['username']) == '') {
                die(json_encode(array(
                    "response" => "error",
                    "text" => "POST contains missing or invalid 'username' parameter."
                )));
            }

            if (confirm_unused_name($_POST['username'])) {
                die(json_encode(array(
                    "response" => "error",
                    "text" => "This username is already in use."
                )));
            }
        }

        if (AUTH_REQUIRE_EMAIL || isset($_POST['email'])) {
            if (!isset($_POST['email']) || trim($_POST['email']) == '') {
                die(json_encode(array(
                    "response" => "error",
                    "text" => "An email address is required."
                )));
            }

            if (!check_email_address($_POST['email'])) {
                die(json_encode(array(
                    "response" => "error",
                    "text" => "Invalid email address."
                )));
            }

            if (confirm_unused_name($_POST['email'])) {
                die(json_encode(array(
                    "response" => "error",
                    "text" => "This email address is already in use."
                )));
            }
        }

        if (! AUTH_REQUIRE_EMAIL && !isset($_POST['email'])) {
            $_POST['email'] = "";
        }

        if (!isset($_POST['password']) || $_POST['password'] == "") {
            die(json_encode(array(
                "response" => "error",
                "text" => "The password can not be blank."
            )));
        }

        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Create a random salt
        $salt = "$2y$10$".strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

        $hash = crypt($password, $salt);

        $query = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $query->execute(array(":username" => $username, ":email" => $email, ":password" => $hash));

        $session_id = get_session($users['id']);
        if ($session_id) {
            die(json_encode(array(
                "response" => "success",
                "text" => "User was created successfully.",
                "data" => array(
                    "token" => $session_id
                )
            )));
        } else {
            die(json_encode(array(
                "response" => "error",
                "text" => "There was an issue generating or fetching a session for this user."
            )));
        }
    }
    method_not_allowed();
}



if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    die(json_encode(array(
        "response" => "success",
        "text" => "GET auth info successful fetched."
    )));
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['username']) || trim($_POST['username']) == '') {
        die(json_encode(array(
            "response" => "error",
            "text" => "The username cannot be blank."
        )));
    }

    if (!isset($_POST['password']) || $_POST['password'] == '') {
        die(json_encode(array(
            "response" => "error",
            "text" => "The password cannot be blank."
        )));
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $query = $db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $query->execute(array(":username" => $username));
    $users = $query->fetchAll(PDO::FETCH_ASSOC);

    if (count($users) < 1) {
        die(json_encode(array(
            "response" => "error",
            "text" => "Username or password not found."
        )));
    }

    $users = $users[0];

    if (crypt($password, $users['password']) === $users['password']) {
        // Password is valid.
        $session_id = get_session($users['id']);
        if ($session_id) {
            die(json_encode(array(
                "response" => "success",
                "text" => "User authenticated successfully.",
                "data" => array(
                    "token" => $session_id
                )
            )));
        } else {
            die(json_encode(array(
                "response" => "error",
                "text" => "There was an issue generating or fetching a session for this user."
            )));
        }
    }

    die(json_encode(array(
        "response" => "error",
        "text" => "Incorrect password provided for this user."
    )));
}
method_not_allowed();


function get_session($user_id, $session_id = null) {
    global $db;

    // If they have a session id, verify it.  If not, continue.
    if ($session_id !== null) {
        $query = $db->prepare("SELECT * FROM sessions WHERE id = :session_id AND user_id = :user_id LIMIT 1");
        $query->execute(array(":session_id" => $session_id, ":user_id" => $user_id));
        $sessions = $query->fetchAll(PDO::FETCH_ASSOC);

        if (count($sessions) == 0) {
            return false;
        }
        return $sessions[0]['id'];
    }

    $new_session_id = generate_unique_id();

    // Verify that the session ID is not already in use.
    $query = $db->prepare("SELECT * FROM sessions WHERE id = :session_id LIMIT 1");
    $query->execute(array(":session_id" => $new_session_id));
    $sessions = $query->fetchAll(PDO::FETCH_ASSOC);

    while (count($sessions) != 0) {
        $new_session_id = generate_unique_id();
        $query = $db->prepare("SELECT * FROM sessions WHERE id = :session_id LIMIT 1");
        $query->execute(array(":session_id" => $new_session_id));
        $sessions = $query->fetchAll(PDO::FETCH_ASSOC);
    }

    $query = $db->prepare("INSERT INTO sessions (id, user_id) VALUES (:session_id, :user_id)");
    $query->execute(array(":session_id" => $new_session_id, ":user_id" => $user_id));

    return $session_id;
}
