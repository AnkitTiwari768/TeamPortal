<?php 

return [
    'algo_options' => [
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ],
    'public_key_path' => './storage/app/public.key',
    'private_key_path' => './storage/app/private.key',
];