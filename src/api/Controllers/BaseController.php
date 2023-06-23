<?php

include_once(dirname(__FILE__) . '/../Database.php');

class BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function getRequestBody(): ?array
    {
        $requestBody = json_decode(file_get_contents('php://input'), true);

        if (is_array($requestBody)) {
            return $requestBody;
        } else {
            return null;
        }
    }

    protected function checkAuthentication()
    {
        if(isset($_SERVER['HTTP_AUTHORIZATION'])) {
            if (preg_match('/Bearer\s+(.*)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                $token = $matches[1];
            }

            $decodedToken = base64_decode($token); //decodam tokenul
            //user_id,parola_criptata
            $userTokenDetails = explode(',', $decodedToken);

            if(count($userTokenDetails) !== 2){
                return null;
            }

            $user = $this->db->select('users', '*', ' id = "' . $userTokenDetails['0'] . '" AND password = "' . $userTokenDetails['1'] . '"', [], true);

            if($user){
                return $user;
            }
        }

        return null;
    }

    protected function returnJsonResponse($data, $statusCode): void {
        // Set the response status code
        http_response_code($statusCode);

        // Set the content type to JSON
        header('Content-Type: application/json');

        // Encode the data as JSON
        $jsonResponse = json_encode($data);

        // Return the JSON response
        echo $jsonResponse;
    }

}