<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Crypt;

use App\Helpers\Openssl;

class EncryptionService 
{
    public function generateEncryptionKeys()
    {
        $privateKey = Openssl::generateOpensslPrivateKey();
        Openssl::createOpensslPrivateKeyFile($privateKey);

        $publicKey = Openssl::generateOpensslPublicKey($privateKey);
        Openssl::createOpensslPublicKeyFile($privateKey);
        
        Openssl::freeOpensslKey($privateKey);

        return $publicKey;
    }

    public function encrypt(string $plainText)
    {
        $compressed = Openssl::compress($plainText);
        $publicKey = Openssl::getOpensslPublicKey();
        $bits = Openssl::getBits($publicKey);
        $chunkSize = Openssl::getChunkSize($bits);

        $cipherText = '';
 
        while ($plainText)
        {
            $chunk = substr($plainText, 0, $chunkSize);
            $plainText = substr($plainText, $chunkSize);
            $encrypted = '';
            
            if (! Openssl::encrypt($chunk, $encrypted, $publicKey))
            {
                throw new \Exception('Failed to encrypt data');
            }
            
            $cipherText .= $encrypted;
        }

        $cipherText = base64_encode($cipherText);
        
        Openssl::freeOpensslKey($publicKey);

        return $cipherText;
    }

    public function decrypt(string $encrypted)
    {
        $encrypted = base64_decode($encrypted);

        if (! ($privateKey = Openssl::getOpensslPrivateKey()))
        {
            throw new \Exception('Private Key failed');
        }
            
        $bits = Openssl::getBits($privateKey);
        $chunkSize = ceil($bits / 8);
        
        $output = '';
                
        while ($encrypted)
        {
            $chunk = substr($encrypted, 0, $chunkSize);
            $encrypted = substr($encrypted, $chunkSize);
            
            $decrypted = '';
            
            if (! Openssl::decrypt($chunk, $decrypted, $privateKey))
            {
                throw new \Exception('Failed to decrypt data');
            }

            $output .= $decrypted;
        }
        
        Openssl::freeOpensslKey($privateKey);
        
        return Openssl::uncompress($output);
    }
}