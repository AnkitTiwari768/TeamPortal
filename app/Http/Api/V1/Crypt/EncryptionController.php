<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Crypt;

class EncryptionController 
{
    public function __construct(private EncryptionService $encryptionService) 
    {
        $this->encryptionService = $encryptionService;
    }

    public function generateEncryptionKeys()
    {
        if (! ($encryptionKey = $this->encryptionService->generateEncryptionKeys())) {
            return $this->respondError(__('encryption.failed_key_generation'));
        }

        return $this->respondSuccess(data: compact('encryptionKey'));
    }

    public function encrypt(EncryptionRequest $request)
    {
        $validated = $request->validate();

        if (! ($encrypted = $this->encryptionService->encrypt($validated->plainText))) {
            return $this->respondError(__('encryption.encrypt_failed'));
        }

        return $this->respondSuccess(data: compact('encrypted'));
    }
}