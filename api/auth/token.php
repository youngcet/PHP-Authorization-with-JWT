<?php

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, APIKEY");
    header("Cache-Control: must-revalidate");
    $offset = 60 * 60 * 24 * 3;
    $ExpStr = "Expires: ". gmdate("D, d M Y H:i:s", time() + $offset) . "GMT";
    header($ExpStr);

    require ('OAuth2.php');


    if ($_SERVER['REQUEST_METHOD'] != 'POST')
    {
        http_response_code (405);
        exit (json_encode (array ("error" => 'HTTP request method not allowed', 'code' => 405)));
    }

    if ($_SERVER['CONTENT_TYPE'] != 'application/json' || ! isset ($_SERVER['HTTP_APIKEY']))
    {
        http_response_code (406);
        exit (json_encode (array ("error" => 'Missing required headers', 'code' => 406)));
    }

    $postData = json_decode (file_get_contents ("php://input"), true);
    if (empty ($postData))
    {
        http_response_code (400);
        exit (json_encode (array ("error" => 'Missing data', 'code' => 400)));
    }

    if (! isset ($postData['payload']) || empty ($postData['payload']))
    {
        http_response_code (400);
        exit (json_encode (array ("error" => 'Missing payload', 'code' => 400)));
    }

    http_response_code (200);
    exit (json_encode (['message' => 'success', 'token' => App\Custom\API\Auth\OAuth2::generateToken($postData['payload'], $_SERVER['HTTP_APIKEY'], 'HS256')]));
?>