<?php 

namespace App\Http\Api\V1\Auth;

use Illuminate\Http\Request;

class OpensslController 
{
    public function generateKeys()
    {
        try {

            $privateKey = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);

            openssl_pkey_export_to_file($privateKey, './storage/app/private.key');

            $asymmetricKey = openssl_pkey_get_details($privateKey);

            file_put_contents('./storage/app/public.key', $asymmetricKey['key']);

            openssl_free_key($privateKey);

            return response([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'secretKey' => $asymmetricKey['key']
                ]
            ]);
        }
        catch (\Throwable $th) {
            return response([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function encrypt(Request $request)
    {
        try {
            $plainText = $request->plainText;
            $plainText = gzcompress($plainText);

            $publicKey = openssl_pkey_get_public(file_get_contents('./storage/app/public.key'));
            $asymmetricKey = openssl_pkey_get_details($publicKey);
 
            $chunkSize = ceil($asymmetricKey['bits'] / 8) - 11;
            $output = '';
 
            while ($plainText)
            {
                $chunk = substr($plainText, 0, $chunkSize);
                $plainText = substr($plainText, $chunkSize);
                $encrypted = '';
                if (!openssl_public_encrypt($chunk, $encrypted, $publicKey))
                {
                    die('Failed to encrypt data');
                }
                $output .= $encrypted;
            }
            $output = base64_encode($output);
            openssl_free_key($publicKey);
            $encrypted = $output;

            return response([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'encrypted' => $encrypted
                ]
            ]);
        }
        catch (\Throwable $th) {
            return response([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function decrypt(Request $request)
    {
        try {
            $encrypted = base64_decode($request->encrypted);

            if (!$privateKey = openssl_pkey_get_private(file_get_contents('./storage/app/private.key')))
                {
                    die('Private Key failed');
                }
                $asymmetricKey = openssl_pkey_get_details($privateKey);
                 
                // Decrypt the data in the small chunks
                $chunkSize = ceil($asymmetricKey['bits'] / 8);
                $output = '';
                 
                while ($encrypted)
                {
                    $chunk = substr($encrypted, 0, $chunkSize);
                    $encrypted = substr($encrypted, $chunkSize);
                    $decrypted = '';
                    if (!openssl_private_decrypt($chunk, $decrypted, $privateKey))
                    {
                        die('Failed to decrypt data');
                    }
                    $output .= $decrypted;
                }
                openssl_free_key($privateKey);
                 
                // Uncompress the unencrypted data.
                $output = gzuncompress($output);

                return response([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'decrypted' => $output
                    ]
                ]);
        }
        catch (\Throwable $th) {
            return response([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}