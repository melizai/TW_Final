<?php

class DataEncryptor
{
    private const PRIVATE_KEY = 'tehnologiiWeb2023';
    public static function encryptData($data)
    {
        $cipher = "aes-256-cbc"; // AES encryption with 256-bit key in CBC mode
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLength); // Generate a random initialization vector

        $encryptedData = openssl_encrypt($data, $cipher, self::PRIVATE_KEY, OPENSSL_RAW_DATA, $iv);

        $encryptedData = base64_encode($iv . $encryptedData); // Combine IV and encrypted data

        return $encryptedData;
    }

    public static function decryptData($encryptedData)
    {
        $cipher = "aes-256-cbc"; // AES encryption with 256-bit key in CBC mode
        $ivLength = openssl_cipher_iv_length($cipher);

        $encryptedData = base64_decode($encryptedData); // Decode the base64 encoded data

        $iv = substr($encryptedData, 0, $ivLength); // Extract the IV from the encrypted data
        $data = substr($encryptedData, $ivLength); // Extract the actual encrypted data

        $decryptedData = openssl_decrypt($data, $cipher, self::PRIVATE_KEY, OPENSSL_RAW_DATA, $iv);

        return $decryptedData;
    }

}