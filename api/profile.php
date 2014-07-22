<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    die(json_encode(array(
        "response" => "success",
        "text" => "GET fetch of profile info successful."
    )));
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    die(json_encode(array(
        "response" => "success",
        "text" => "POST update of profile info successful."
    )));
}
method_not_allowed();
