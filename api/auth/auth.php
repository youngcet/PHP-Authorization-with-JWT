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

    if (! isset ($_SERVER['HTTP_APIKEY']))
    {
        http_response_code (406);
        exit (json_encode (array ("error" => 'Missing required headers', 'code' => 406)));
    }

    $apiKey = $_SERVER['HTTP_APIKEY'];
    //  in my casse am using the apikey provided to search for a record matching that and pass the email
    // $data = App\Web\RESTful\API::APIcall 
    //     (
    //         $apiKey, 
    //         'POST', 
    //         'url', 
    //         array 
    //             (
    //                 'query' => 'select emailaddress from table where token = ?', 
    //                 'values' => array 
    //                     (
    //                         $apiKey,
    //                     ), 
    //                 'bind' => 's'
    //             )
    //     );
        
    // if (isset ($data['error']) || empty ($data))
    // {                
    //     return ["error" => 'Unauthorized', 'code' => 401];
    // }
    $data = [];
    $data['rows'][0]['emailaddress'] = 'yungcet'; // hard coded for testing purposes
    $isvalid = App\Custom\API\Auth\OAuth2::IsTokenValid(new DateTimeImmutable(), $data['rows'][0]['emailaddress']);
    if (! isset ($isvalid['error']))
    {
        http_response_code (401);
        exit (json_encode ($isvalid));
    }

    http_response_code (200);
    exit (json_encode ($isvalid));

?>