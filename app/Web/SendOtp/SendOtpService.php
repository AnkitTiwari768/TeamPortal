<?php

declare(strict_types=1);

namespace App\Web\SendOtp;

use App\Models\User;
use App\Traits\HasRawQuery;
use App\Utils\UuidGenerator; 

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Web\Sms\SmsTrigger; 
use App\Services\PHPMailerService;



class SendOtpService
{
    use HasRawQuery, SmsTrigger;

    public const CODE_EXPIRATION_TIME = 120;

    public function processSendOtpRequest(string $userId): void
    {
        DB::transaction(function () use ($userId) {

            $user = User::findOrFail($userId);

            if ($this->keyIsExists(key: $user->username)) {
                $this->deleteByKey(key: $user->username);
            }

            $otpCode = $this->generateOtpCode();

            $this->create([
                'key' => $user->username,
                'code' => $otpCode
            ]);

            $this->sendLoginOtp($otpCode, $user->mobile); //Trait
            $this->sendOtpOnEmail(otpCode: $otpCode, email: $user->email);
        });
    }

   /* public function processSendOtpRequestByMobile(string $mobile): void
    {
        DB::transaction(function () use ($mobile) {

            if ($this->keyIsExists(key: $mobile)) {
                $this->deleteByKey(key: $mobile);
            }

            $otpCode = $this->generateOtpCode();

            $this->create([
                'key' => $mobile,
                'code' => $otpCode
            ]);

            //$this->sendOtpDuringSighup($otpCode, $mobile); //Trait
            $this->sendOtpMsmeMapping($otpCode, $mobile); //Trait
            
            //$this->sendOtpOnEmail(otpCode: $otpCode, email: 'mmrdarts@yopmail.com');
        });
    }


    public function processSendOtpMsmeRegistraionRequestByMobile(string $mobile): void
    {
        DB::transaction(function () use ($mobile) {

            if ($this->keyIsExists(key: $mobile)) {
                $this->deleteByKey(key: $mobile);
            }

            $otpCode = $this->generateOtpCode();

            $this->create([
                'key' => $mobile,
                'code' => $otpCode
            ]);

            //$this->sendOtpDuringSighup($otpCode, $mobile); //Trait
            $this->sendOtpMsmeRegistration($otpCode, $mobile); //Trait
            
            //$this->sendOtpOnEmail(otpCode: $otpCode, email: 'mmrdarts@yopmail.com');
        });
    }*/


    public function processSendOtpForMsmeMappingRequest(string $userId): void
    {
        DB::transaction(function () use ($userId) {

            $user = User::findOrFail($userId);

            if ($this->keyIsExists(key: $user->mobile)) {
                $this->deleteByKey(key: $user->mobile);
            }

            $otpCode = $this->generateOtpCode();

            $this->create([
                'key' => $user->mobile,
                'code' => $otpCode
            ]);

            $this->sendOtpMsmeMapping($otpCode, $user->mobile);

            $templateData = [
                'otp' => $otpCode,
                'name' => auth()->user()->first_name,
            ];
            
            $body = view('emails.msme_mapping_email',$templateData)->render();
            app(PHPMailerService::class)->sendEmail($user->email,'MSME TEAM Initiative Portal',$body);

        });
    }



    public function processSendOtpForMsmeRegistrationRequest(string $username,string $email): void
    {
        DB::transaction(function () use ($username, $email) {

            if ($this->keyIsExists(key: $username)) {
                $this->deleteByKey(key: $username);
            }

            $otpCode = $this->generateOtpCode();

            $this->create([
                'key' => $username,
                'code' => $otpCode
            ]);

            $this->sendOtpMsmeRegistration($otpCode, $username);
            
            $templateData = [
                'otp' => $otpCode,
            ];
            
            $body = view('emails.msme_registraion_email',$templateData)->render();
            app(PHPMailerService::class)->sendEmail($email,'MSME TEAM Initiative registration',$body);

        });
    }
    


    public function deleteByKey(string $key): void
    {
        DB::table('verifications')
            ->where('verification_key', $key)
            ->delete();
    }

    private function create(array $data): void
    {
        DB::table('verifications')->insert([
            'id' => UuidGenerator::uuid7(),
            'verification_key' => $data['key'],
            'verification_code' => $data['code'],
            'expired_at' => $this->expiredAt(),
            'created_at' => currentDateTime()
        ]);
    }

    private function keyIsExists(string $key): bool
    {
        $sql = '
            SELECT EXISTS (
                SELECT id 
                FROM verifications
                WHERE verification_key=:key 
            ) AS is_key_exists
        ';

        $bindings = ['key' => $key];

        $result = $this->executeRawQuery($sql, $bindings);

        return isset($result->is_key_exists) && $result->is_key_exists;
    }

    private function generateOtpCode(): int
    {
        return rand(100000, 999999);
    }

    private function expiredAt()
    {
        return date('Y-m-d H:i:s', (time() + self::CODE_EXPIRATION_TIME));
    }

    public function getVerificationDetails($key, $code)
    {
        return DB::table('verifications')
            ->select('expired_at')
            ->where('verification_key', $key)
            ->where('verification_code', $code)
            ->first();
    }

    public function sendOtpOnEmail(int $otpCode, string $email): void
    {
        $templateData = [
            'otp' => $otpCode
        ];

        Mail::send('emails.send_otp', $templateData, function ($message) use ($email) {
            $message->to($email)
                ->cc('mmrdarts@yopmail.com')
                ->subject('One Time Password')
                ->from('no-reply@mom.gov.in', 'ICH');
        });
    } 


   
     
}
