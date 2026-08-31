<?php
namespace App\Web\RootManager\Secret;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class SecretCode extends Model
{
    use HasFactory;

    protected $table = 'secret_codes';

    protected $fillable = [
        'code_value',
        'code_name',
        'status'
    ];

    // Check if code is valid
    public function isValid()
    {
        return $this->status === 'active';
    }
    
    // Verify the entered code against stored hash
    public function verifyCode($enteredCode)
    {
        return Hash::check($enteredCode, $this->code_value);
    }
}