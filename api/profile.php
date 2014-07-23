<?php

require_once("includes/init.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_info = validate_session($_GET['auth']);

    $profile = array(
        "username" => $user_info['username'],
        "email" => $user_info['email'],
        "first_name" => $user_info['first_name'],
        "last_name" => $user_info['last_name']
    );

    $preferences = array();
    $query = $db->prepare("SELECT * FROM user_preferences WHERE user_id = :user_id LIMIT 1");
    $query->execute(array(":user_id" => $user_info['id']));
    $user_preferences = $query->fetchAll(PDO::FETCH_ASSOC);

    for ($i = 0; $i < count($user_preferences); $i++) {
        $preferences[$user_preferences[$i]['key']] = $user_preferences[$i]['value'];
    }

    die(json_encode(array(
        "response" => "success",
        "text" => "GET fetch of profile info successful.",
        "data" => array(
            "profile" => $profile,
            "preferences" => $preferences
        )
    )));
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_info = validate_session($_POST['auth']);

    die(json_encode(array(
        "response" => "success",
        "text" => "POST update of profile info successful."
    )));
}
method_not_allowed();
