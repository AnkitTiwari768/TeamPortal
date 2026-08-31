<?php 

namespace App\Helpers;

class Openssl 
{
    public function generateOpensslPrivateKey() 
    {
        return openssl_pkey_new(config('openssl.algo_options'));
    }

    public function createOpensslPrivateKeyFile($privateKey) 
    {
        openssl_pkey_export_to_file($privateKey, config('openssl.private_key_path'));
    }

    public function generateOpensslPublicKey($privateKey) 
    {
        $asymmetricKey = openssl_pkey_get_details($privateKey);
        return $asymmetricKey['key'];
    }

    public function createOpensslPublicKeyFile($publicKey) 
    {
        file_put_contents(config('openssl.public_key_path'), $publicKey);
    }

    public function getOpensslPublicKey()
    {
        return openssl_pkey_get_public(file_get_contents(config('openssl.public_key_path')));
    }

    public function getOpensslPrivateKey()
    {
        return openssl_pkey_get_private(file_get_contents(config('openssl.private_key_path')));
    }

    public function getBits($publicKey)
    {
        $asymmetricKey = openssl_pkey_get_details($publicKey);
        return $asymmetricKey['bits'];
    }

    public function getChunkSize($bits)
    {
        return ceil($bits / 8) - 11;
    }

    public function freeOpensslKey($privateKey) 
    {
        openssl_free_key($privateKey);
    }

    public function compress($text) 
    {
        return gzcompress($text);
    }

    public function uncompress($text) 
    {
        return gzuncompress($text);
    }
    
    public function encrypt($chunk, $encrypted, $publicKey)
    {
        return openssl_public_encrypt($chunk, $encrypted, $publicKey);
    }

    public function decrypt($chunk, $decrypted, $privateKey)
    {
        return openssl_private_decrypt($chunk, $decrypted, $privateKey);
    }
}