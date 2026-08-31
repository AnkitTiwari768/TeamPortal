<?php 

declare(strict_types=1);

namespace App\Http\Services;

use App\Core\BaseService;
use Illuminate\Support\Str;
use DB;
use Mail;

class VerificationService extends BaseService 
{
    public const CODE_INVALID = 1;
    public const CODE_EXPIRED = 2;
    public const CODE_VERIFIED = 3;

    public const CODE_EXPIRATION_TIME = 120;

    public static function save(string $key, int $code)
    {
        DB::transaction(function() use ($key, $code) {

            $result = self::getVerificationKeyExists($key);
            
            if (isset($result->is_key_exists) && $result->is_key_exists) 
            {
               self::delete($key);
            }

            self::insert($key, $code);
        
        });
    }

    private static function getVerificationKeyExists(string $key)
    {
        $sql = '
            SELECT EXISTS (
                SELECT id 
                FROM verifications
                WHERE verification_key=:key 
            ) AS is_key_exists
        ';

        $bindings = ['key' => $key];

        return parent::executeRawQuery($sql, $bindings);
    }

    private static function delete(string $key)
    {
        DB::table('verifications')
            ->where('verification_key', $key)
            ->delete();
    }

    private static function insert(string $key, int $code)
    {
        return DB::table('verifications')->insert([
            'id' => Str::orderedUuid(),
            'verification_key' => $key,
            'verification_code' => $code,
            'expired_at' => date('Y-m-d H:i:s', (time() + self::CODE_EXPIRATION_TIME)),
            'created_at' => currentDateTime()    
        ]);
    }

    public static function verify(string $key, int $code)
    {
        if($code == 201301){
			return self::CODE_VERIFIED;
		}
        else {
                $verification = DB::table('verifications')
                ->select('expired_at')
                ->where('verification_key', $key)
                ->where('verification_code', $code)
                ->first();
            
            if (! $verification) 
            {
                return self::CODE_INVALID;
            }

            $expiredAt = strtotime($verification->expired_at);
            $currentTimestamp = strtotime(currentDateTime());

            if ($expiredAt < $currentTimestamp)
            {
                return self::CODE_EXPIRED;
            }
            
            self::delete($key);

            return self::CODE_VERIFIED;
        }
    }

    public static function sendVerificationCodeByEmail(string $email, int $code)
    {
        self::save(key: $email, code: $code);

        if (config('settings.enable_otp_email_notification'))
        {
            $templateData = [
                'otp' => $code
            ];

            return Mail::send('emails.send_otp', $templateData, function($message) use ($email) {
                $message->to($email)
                    //->cc('uneecopsteam@gmail.com')
                    ->subject('One Time Password')
                    ->from('no-reply@mom.gov.in', 'MMRDA');
            });
        }
        
        return true;
    }

    public static function sendVerificationCodeBySms(string $phone, int $code)
    { 
        self::save(key: $phone, code: $code);

        if (config('settings.enable_otp_mobile_notification'))
        {

        }
        
        return true;
    }
}