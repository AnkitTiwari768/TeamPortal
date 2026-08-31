<?php

return [

    'title' => 'Create New Account',
    'basic_info' => 'Basic Info',
    'otp' => 'OTP',
    'password' => 'Password',
    'address' => 'Address',
    'full_name' => 'Full Name',
    'email_address' => 'Email Address',
    'mobile_number' => 'Mobile Number',
    'aadhar_number' => 'Aadhaar Number',
    'send_otp' => 'Send OTP',
    'as_per_aadhar' => 'Enter name as on your Aadhar',
    'enter_email' => 'Enter your email',
    'enter_mobile' => 'Aadhaar-linked mobile number',
    'enter_aadhar' => '12-digit Aadhaar number',
    'otp_message' => 'We\'ve sent a 6-digit OTP to your mobile number ending with',
    'otp_verified' => 'OTP has been verified successfully',
    'credentials_validated' => 'Password validated successfully',
    'signup_success' => 'Signed-up successfully',

    'validation' => [
        'full_name' => [
            'required' => 'Please enter your full name.',
        ],
        'email' => [
            'required' => 'Please enter your email address.',
            'email'    => 'Please enter a valid email address.',
        ],
        'mobile' => [
            'required' => 'Please enter your mobile number.',
            'digits'   => 'Mobile number must be exactly 10 digits.',
        ],
        'aadhar' => [
            'required' => 'Please enter your Aadhaar number.',
            'digits'   => 'Aadhaar number must be exactly 12 digits.',
        ],
        'house_no' => [
            'required' => 'Please enter the house or flat number.',
        ],
        'street' => [
            'required' => 'Please enter the street or building name.',
        ],
        'district' => [
            'required' => 'Please select a district.',
            'exists'   => 'The selected district is invalid.',
        ],
        'state' => [
            'required' => 'Please select a state.',
            'exists'   => 'The selected state is invalid.',
        ],
        'pin_code' => [
            'required' => 'Please enter the PIN code.',
            'digits'   => 'The PIN code must be exactly 6 digits.',
        ],
    ],

];
