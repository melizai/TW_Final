<?php

use http\Client\Response;

include("BaseController.php");
include_once(dirname(__FILE__) . '/../Helpers/DataEncryptor.php');

class UsersController extends BaseController
{
    public function login()
    {
        $body = $this->getRequestBody();

        if ($body === NULL) {
            $this->returnJsonResponse(['errors' => 'Corrupt JSON !!'], 400);
        }

        if (!isset($body['username']) || !isset($body['password'])) {
            $this->returnJsonResponse(['errors' => 'Missing field !!'], 400);
        }

        $user = $this->db->select('users', '*', ' username = "' . $body['username'] . '"', [], true);

        if (!$user) {
            $this->returnJsonResponse(['errors' => 'User does not exists in our db!'], 400);
            return;
        }

        if ($body['password'] === DataEncryptor::decryptData($user['password'])) {
            $authData = $user['id'].','.$user['password'];
            $this->returnJsonResponse(['data' => $user, 'auth' => 'Bearer ' . base64_encode($authData)], 200);
        } else {
            $this->returnJsonResponse(['errors' => 'Wrong password!'], 400);
        }
    }


    public function register()
    {
        $body = $this->getRequestBody();

        if ($body === NULL) {
            $this->returnJsonResponse(['errors' => 'Corrupt JSON !!'], 400);
        }

        if (!isset($body['email']) || !isset($body['username']) || !isset($body['password']) || !isset($body['age']) || !isset($body['country'])) {
            $this->returnJsonResponse(['errors' => 'Missing field !!'], 400);
        }

        $userData = [
            'email' => $body['email'],
            'type' => 'user',
            'username' => $body['username'],
            'password' => DataEncryptor::encryptData($body['password']),
            'age' => $body['age'],
            'country' => $body['country'],
        ];

        $userExists = $this->db->select('users', '*', ' username = "' . $body['username'] . '" OR email = "' . $body['email'] . '"');

        if (empty($userExists)) {
            $this->db->insert('users', $userData);
        } else {
            $this->returnJsonResponse(['errors' => 'User already exists in our db'], 400);

            return;
        }

        $this->returnJsonResponse(['message' => 'User created!'], 200);
    }

}