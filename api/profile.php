<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    die(json_encode(array(
        "response" => "success",
        "text" => "GET successful on profile info."
    )));
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    die(json_encode(array(
        "response" => "success",
        "text" => "POST successful on profile info."
    )));
} else {
    http_response_code(400);
    die(json_encode(array(
        "response" => "error",
        "text" => "Method not allowed."
    )));
}
