<?php

use Illuminate\Support\Str;
use App\Modules\Role\Role;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Services\CommonService;

if (!function_exists('getTotalTax')) {
    function getTotalTax()
    {

        //using other change
        /*$tax = DB::table('tax')->select('gst_charge','other_charge')->first();
        $totalTax = 1+($tax->gst_charge + $tax->other_charge)/100;

        return [
            'gst_charge' => $tax->gst_charge,
            'other_charge' => $tax->other_charge,
            'totalTax' => $totalTax,
        ];*/

        $tax = DB::table('tax')->select('gst_charge')->first();
        $totalTax = 1 + ($tax->gst_charge) / 100;

        return [
            'gst_charge' => $tax->gst_charge,
            'totalTax' => $totalTax,
        ];
    }
}

if (!function_exists('make_list')) {
    function make_list($result, $key, $value, $withEmpty = true)
    {

        $list = [];

        if ($withEmpty)
            $list[''] = __('message.select');

        foreach ($result as $row) {
            if (is_array($row)) {
                $row = (object) $row;
            }
            $list[$row->{$key}] = $row->{$value};
        }

        return $list;
    }
}

if (!function_exists('__default_list')) {
    function __default_list()
    {
        return ['' => __('message.select')];
    }
}


if (!function_exists('static_common_list')) {
    function static_common_list($data, $key = null)
    {
        return isset($key) ? $data[$key] : $data;
    }
}


if (!function_exists('dynamic_common_list')) {
    function dynamic_common_list($data = null)
    {
        if (!empty($data))
            return $data ? make_list($data, 'id', 'name') : [];
        else
            return __default_list();
    }
}

if (!function_exists('remove_select_dynamic_common_list')) {
    function remove_select_dynamic_common_list($data = null)
    {
        if (!empty($data))
            return $data ? make_list($data, 'id', 'name', false) : [];
        else
            return __default_list();
    }
}












if (!function_exists('status_list')) {
    function status_list($key = null)
    {
        $status = [
            config('constant.ACTIVE') => __('message.active'),
            config('constant.INACTIVE') => __('message.in_active')
        ];

        return $key ? $status[$key] : $status;
    }
}




if (!function_exists('uuid')) {
    function uuid()
    {
        return \Illuminate\Support\Str::orderedUuid()->toString();
    }
}

if (!function_exists('AuthId')) {
    function AuthId()
    {
        return Auth::id();
    }
}

if (!function_exists('authIdsWithSubUsers')) {
    /**
     * Resolve the set of user ids that share the same organizational data:
     * the account owner (parent) plus every sub-user created under it.
     * Works whether the currently logged-in user IS the parent or one of its sub-users.
     */
    function authIdsWithSubUsers($userId = null)
    {
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            return [];
        }

        $user = DB::table('users')->where('id', $userId)->first();
        $parentId = !empty($user->parent_user_id) ? $user->parent_user_id : $userId;

        $subUserIds = DB::table('users')
            ->where('parent_user_id', $parentId)
            ->pluck('id')
            ->toArray();

        return array_values(array_unique(array_merge([$parentId], $subUserIds)));
    }
}

if (!function_exists('like_both')) {
    function like_both($str)
    {
        return '%' . $str . '%';
    }
}

if (!function_exists('search_list')) {
    function search_list(array $list)
    {
        return $result['result'] = array_map(
            fn($item) => (['id' => $item->id, 'text' => $item->name,]),
            $list
        );
    }
}

if (!function_exists('currentDateTime')) {
    function currentDateTime()
    {
        return date("Y-m-d H:i:s");
    }
}

if (!function_exists('currentDateTime_minus30sec')) {
    function currentDateTime_minus30sec()
    {
        return date("Y-m-d H:i:s", time() - 5);
    }
}

if (!function_exists('datetime')) {
    function datetime($dateStr)
    {
        return date('Y-m-d H:i:s', strtotime($dateStr));
    }
}


if (!function_exists('dateonly')) {
    function dateonly($dateStr)
    {
        return date('Y-m-d', strtotime($dateStr));
    }
}

if (!function_exists('app_date')) {
    function app_date($dateStr)
    {
        return date('d-M-Y', strtotime($dateStr));
    }
}

function numberPrecision($number, $decimals = 0)
{
    $negation = ($number < 0) ? (-1) : 1;
    $coefficient = 10 ** $decimals;
    return $negation * floor((string) (abs($number) * $coefficient)) / $coefficient;
}




if (!function_exists('application_status_list')) {
    function application_status_list($key = null)
    {
        $statuses = \App\Enums\ReviewStatus::getStatuses();
        array_unshift($statuses, __('Select'));
        return $key ? $statuses[$key] : $statuses;
    }
}

if (!function_exists('loggedIn_status_list')) {
    function loggedIn_status_list($key = null)
    {
        $statuses = \App\Enums\ReviewStatus::getLoggedInStatuses();
        //array_unshift($statuses, __('Select'));
        $statuses[0] = 'Select';
        ksort($statuses);
        return $key ? $statuses[$key] : $statuses;
    }
}

if (!function_exists('payment_status_list')) {
    function payment_status_list($key = null)
    {
        $statuses = \App\Enums\PaymentStatus::array();
        unset($statuses[\App\Enums\PaymentStatus::Initiated->value]);
        unset($statuses[\App\Enums\PaymentStatus::Refunded->value]);
        unset($statuses[\App\Enums\PaymentStatus::Pending->value]);
        array_unshift($statuses, __('Select'));
        return $key ? $statuses[$key] : $statuses;
    }
}





if (!function_exists('module_list')) {
    function module_list($data, $key = null)
    {
        return $data ? make_list($data, 'id', 'name') : [];
    }
}



if (!function_exists('isSelfCreated')) {
    function isSelfCreated($id, $table, $primaryColumn = 'id', $createdByColumn = 'created_by')
    {
        $count = DB::table($table)
            ->where($primaryColumn, $id)
            ->where($createdByColumn, auth_id())
            ->count();
        if ($count === 0) {
            return abort(404);
        }
    }
}

if (!function_exists('acl')) {
    function acl($permission)
    {
        $permissions = session('permissions');
        if ($permissions) {
            return in_array(strtolower($permission), $permissions) ? true : false;
        }
    }
}

if (!function_exists('guard')) {
    function guard($permission)
    {
        abort_if(!acl($permission), 403);
    }
}

if (!function_exists('random_key')) {
    function random_key()
    {
        return bin2hex(random_bytes(16));
    }
}

if (!function_exists('crypto_secrets')) {
    function crypto_secrets()
    {
        session([
            'crypto_key' => random_key(),
            'crypto_salt' => random_key(),
            'crypto_iv' => random_key(),
            'crypto_key_size' => 64 / 8,
            'crypto_iterations' => 999
        ]);
    }
}

if (!function_exists('crypto_decrypt')) {
    function crypto_decrypt($encrypt)
    {
        if (!empty($encrypt)) {
            $encrypt = base64_decode($encrypt);
            $iterations = session('crypto_iterations');
            $salt = hex2bin(session('crypto_salt'));
            $iv = hex2bin(session('crypto_iv'));
            $key = hash_pbkdf2("sha512", session('crypto_key'), $salt, $iterations, 64);

            return openssl_decrypt(
                $encrypt,
                'AES-256-CBC',
                hex2bin($key),
                OPENSSL_RAW_DATA,
                $iv
            );
        }
    }
}



if (!function_exists('permission_list_by_role')) {
    function permission_list_by_role($role_id)
    {
        $permissions = DB::table('permissions')->select('*')
            ->join('permission_role_mapping', 'permission_role_mapping.permission_id', '=', 'permissions.id')
            ->where(['permission_role_mapping.role_id' => $role_id])
            ->get();
        $list = [];

        if ($permissions) {
            foreach ($permissions as $permission) {
                $list[$permission->id] = $permission->name;
            }
        }
        return $list;
    }
}

if (!function_exists('permission_list_by_user')) {
    function permission_list_by_user($role_id)
    {
        $permissions = DB::table('permissions')->select('*')
            ->join('permission_role', 'permission_role.permission_id', '=', 'permissions.id')
            ->where(['permission_role.role_id' => $role_id])
            ->get();
        $list = [];

        if ($permissions) {
            foreach ($permissions as $permission) {
                $list[$permission->id] = $permission->name;
            }
        }
        return $list;
    }
}


if (!function_exists('country_list')) {
    function country_list($key = null)
    {
        return CommonService::getDropdownList('countries', $key);
    }
}

if (!function_exists('int_country_list')) { // for international 
    function int_country_list($key = null)
    {
        return CommonService::getInternationaCountries('countries', $key);
    }
}

if (!function_exists('state_list')) {
    function state_list($key = null)
    {
        return CommonService::getDropdownList('states', $key);
    }
}

if (!function_exists('district_list')) {
    function district_list($key = null)
    {
        return CommonService::getDropdownList('locations', $key);
    }
}


if (!function_exists('role_list')) {
    function role_list($key = null)
    {
        return CommonService::getDropdownList('roles', $key);
    }
}

if (!function_exists('role_type_list')) {
    function role_type_list($key = null)
    {
        return CommonService::getDropdownList('role_types', $key);
    }
}

if (!function_exists('department_list')) {
    function department_list($key = null)
    {
        return CommonService::getDropdownList('departments', $key);
    }
}

if (!function_exists('designation_list')) {
    function designation_list($key = null)
    {
        return CommonService::getDropdownList('designations', $key);
    }
}



if (!function_exists('formatSizeUnits')) {
    function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}

if (!function_exists('generateOtp')) {
    function generateOtp()
    {
        //$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters = '0123456789';
        $randomString = substr(str_shuffle($characters), 0, 6);
        //return 123456; 
        return rand(100000, 999999);
    }
}

if (!function_exists('isEmail')) {
    function isEmail($email)
    {
        return preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i', $email) === 1 ? true : false;
    }
}

if (!function_exists('isPhoneNumber')) {
    function isPhoneNumber($phone)
    {
        return preg_match('/^[0-9]{10}+$/', $phone) === 1 ? true : false;
    }
}


if (!function_exists('sendSingleUnicode')) {
    function sendSingleUnicode($message, $mobileno, $details)
    {
        $message = strip_tags($message);
        $senderid = config('constant.SMS_SENDER_ID');
        $deptSecureKey = config('constant.SMS_SECURITY_KEY');
        $username = config('constant.SMS_USERNAME');
        $password = config('constant.SMS_PASSWORD');
        $encryp_password = sha1(trim($password));

        $key = hash('sha512', trim($username) . trim($senderid) . trim($message) . trim($deptSecureKey));

        $data = array(
            "username" => trim($username),
            "password" => trim($encryp_password),
            "senderid" => trim($senderid),
            "content" => trim($message),
            "smsservicetype" => 'singlemsg',
            "mobileno" => trim($mobileno),
            "templateid" => trim($details['templateid']),
            "key" => trim($key)
        );
        post_to_url_unicode("https://msdgweb.mgov.gov.in/esms/sendsmsrequestDLT", $data);
    }
}


if (!function_exists('forgotPasswordSmsTemplate')) {
    function forgotPasswordSmsTemplate()
    {
        $details = array();
        $details['templateid'] = '1007205238009578305';
        $details['msg'] = 'Your password is changed successfully Your password is {#var#} .';
        return $details;
    }
}

if (!function_exists('slugify')) {
    function slugify($str)
    {
        return Illuminate\Support\Str::of($str)->slug('-');
    }
}

if (!function_exists('hasRole')) {
    function hasRole($key)
    {
        if (empty($key)) {
            return false;
        }

        $activeRole = session('active_role');
        if ($activeRole === null) {
            return false;
        }

        $activeRoleStr = strtolower((string) $activeRole);

        if (is_array($key)) {
            $keyLower = array_map(function ($item) {
                return strtolower((string) $item);
            }, $key);
            return in_array($activeRoleStr, $keyLower, true);
        }

        return $activeRoleStr === strtolower((string) $key);
    }
}



if (!function_exists('getAuthUserRoles')) {
    function getAuthUserRoles()
    {
        return \DB::table('user_roles')
            ->select('roles.slug', 'roles.name', 'roles.is_custom')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', AuthId())
            ->get()
            ->toArray();
    }
}

if (!function_exists('getActiveRole')) {
    function getActiveRole()
    {
        return session('active_role');
    }
}


if (!function_exists('isDisabled')) {
    function isDisabled($val)
    {
        return $val === 0 ? true : false;
    }
}

if (!function_exists('createUUID')) {
    function createUUID()
    {
        return (string) \Illuminate\Support\Str::orderedUuid();
    }
}

if (!function_exists('users_list')) {
    function users_list($data)
    {
        return $data ? make_list($data, 'id', 'full_name') : [];
    }
}

if (!function_exists('download_file')) {
    function download_file($filepath, $filename)
    {
        return response()->download(config('base_path') . '/' . $filepath . '/' . $filename);
    }
}
if (!function_exists('getUserRoles')) {
    function getUserRoles($user_id)
    {
        return \DB::table('user_roles')
            ->select('roles.slug', 'roles.name')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $user_id)
            ->get()
            ->toArray();
    }
}

if (!function_exists('getCityById')) {
    function getCityById($cityId)
    {
        $query = DB::table('locations as c')
            ->where('id', $cityId)
            ->selectRaw('name');
        return $query->first()->name;
    }
}
if (!function_exists('getStateById')) {
    function getStateById($stateId)
    {
        $query = DB::table('states as s')
            ->where('id', $stateId)
            ->selectRaw('name');
        return $query->first()->name;
    }
}
if (!function_exists('getCountryById')) {
    function getCountryById($countryId)
    {
        $query = DB::table('countries as s')
            ->where('id', $countryId)
            ->selectRaw('name');
        return $query->first()->name;
    }
}


if (!function_exists('array_find')) {
    function array_find(string $search, string $key, array $array): bool
    {
        return (bool) in_array($search, array_column($array, $key));
    }
}


if (!function_exists('format_indian_currency')) {
    function format_indian_currency($number)
    {
        if (is_null($number) || $number === '') {
            return '0.00';
        }
        if (class_exists('\NumberFormatter')) {
            $formatter = new \NumberFormatter('en_IN', \NumberFormatter::DECIMAL);
            $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
            $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
            return $formatter->format((float)$number);
        }

        // Fallback custom formatting for Indian numbering system
        $decimal = '';
        if (strpos((string)$number, '.') !== false) {
            list($number, $decimal) = explode('.', sprintf('%.2f', $number));
            $decimal = '.' . $decimal;
        } else {
            $decimal = '.00';
        }
        $number = (string)$number;
        $len = strlen($number);
        if ($len > 3) {
            $last_three = substr($number, -3);
            $remaining = substr($number, 0, -3);
            $remaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
            return $remaining . ',' . $last_three . $decimal;
        }
        return $number . $decimal;
    }
}


function getIndianCurrency(float $number, $format)
{
    if ($number == 0) {
        return ('Zero ' . $format);
    }
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(
        0 => 'Zero',
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => 'eight',
        9 => 'nine',
        10 => 'ten',
        11 => 'eleven',
        12 => 'twelve',
        13 => 'thirteen',
        14 => 'fourteen',
        15 => 'fifteen',
        16 => 'sixteen',
        17 => 'seventeen',
        18 => 'eighteen',
        19 => 'nineteen',
        20 => 'twenty',
        30 => 'thirty',
        40 => 'forty',
        50 => 'fifty',
        60 => 'sixty',
        70 => 'seventy',
        80 => 'eighty',
        90 => 'ninety'
    );
    $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
    while ($i < $digits_length) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
        } else
            $str[] = null;
    }

    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal > 0) ? " " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';

    if ($paise != '')
        return ($Rupees ? $format . " " . ucfirst($Rupees) : '') . " and " . $paise;
    else
        return ($Rupees ? $format . " " . ucfirst($Rupees) : '');
}



if (!function_exists('getModuleByUrl')) {
    function getModuleByUrl($url)
    {
        $query = DB::table('modules as m')
            ->where('url', $url)
            ->selectRaw('name');
        return $query->first()->name;
    }
}



if (!function_exists('date_only')) {
    function date_only(string $dateStr)
    {
        return date('Y-m-d', strtotime($dateStr));
    }
}

if (!function_exists('time_only')) {
    function time_only(string $timeStr)
    {
        return date('H:i', strtotime($timeStr));
    }
}



if (!function_exists('status_value')) {
    function status_value($current_status)
    {

        switch ($current_status) {
            case '1':
                $status_value = '<span class="badge bg-success">Approved</span>';
                break;
            case '2':
                $status_value = '<span class="badge bg-info">Pending</span>';
                break;
            case '3':
                $status_value = '<span class="badge bg-danger">Rejected</span>';
                break;
            default:
                $status_text = 'Pending';
                $status_value = '<span class="badge bg-warning">Pending</span>';
                break;
        }
        return $status_value;
    }
}



if (!function_exists('location_categories_list')) {
    function location_categories_list($key = null)
    {
        return CommonService::getDropdownList('location_categories', $key);
    }
}
if (!function_exists('location_categories')) {
    function location_categories($state_id)
    {
        return CommonService::getDropdownListByState('location_categories', $state_id);
    }
}
if (!function_exists('district_by_state_list')) {
    function district_by_state_list($state_id)
    {
        return CommonService::getDropdownListOfDistrictByState('locations', $state_id);
    }
}

if (!function_exists('get_auth_user_name')) {
    function get_auth_user_name()
    {
        return \DB::table('users')
            ->select('first_name')
            ->where('id', AuthId())
            ->first()
            ?->first_name;
    }
}



if (!function_exists('state_list_india')) {
    function state_list_india()
    {
        return CommonService::getDropdownListOfIndianState('states', null);
    }
}



if (!function_exists('convertToIndianCurrency')) {
    function convertToIndianCurrency($number)
    {
        // Define Indian number system
        $words = array(
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety'
        );

        // Define suffixes for Indian numbering system
        $suffixes = array(
            1 => '',
            2 => 'Thousand',
            3 => 'Lakh',
            4 => 'Crore'
        );

        // Split the number into integer and decimal parts
        $parts = explode('.', $number);

        // Integer part
        $integer_part = (int) $parts[0];
        $result = '';

        // Convert integer part
        if ($integer_part != 0) {
            $number_parts = str_split(strrev((string) $integer_part), 3);

            foreach ($number_parts as $key => $part) {
                if ((int) $part !== 0) {
                    $part_words = [];
                    $part = str_pad(strrev($part), 3, '0', STR_PAD_LEFT);
                    $part = strrev($part);

                    // Convert hundreds place
                    if ($part[2] !== '0') {
                        $part_words[] = $words[(int) $part[2]] . ' Hundred';
                    }

                    // Convert tens and ones place
                    $tens = (int) ($part[1] . $part[0]);
                    if ($tens !== 0) {
                        if ($tens < 20) {
                            $part_words[] = $words[$tens];
                        } else {
                            $part_words[] = $words[(int) $part[1] * 10];
                            $part_words[] = $words[(int) $part[0]];
                        }
                    }

                    // Add suffix
                    $suffix = isset($suffixes[$key + 1]) ? $suffixes[$key + 1] : '';
                    $result = implode(' ', $part_words) . ' ' . $suffix . ' ' . $result;
                }
            }
        } else {
            $result = 'Zero';
        }

        // Decimal part
        if (isset($parts[1])) {
            $decimal_part = (int) $parts[1];
            if ($decimal_part != 0) {
                $result .= ' and ' . convertToIndianCurrency($decimal_part) . ' Paise';
            }
        }

        return trim($result);
    }
}


if (!function_exists('year_list')) {
    function year_list($key = null)
    {
        $list[''] = 'Select Year';
        //$currentYear = date('Y', strtotime(date("Y/m/d")));
        //$maxyear = date('Y') + 1;
        $maxyear = date('Y');
        for ($i = 2022; $i <= $maxyear; $i++) {
            $list[$i] = $i;
        }
        return ($key) ? $list[$key] : $list;
    }
}


if (!function_exists('get_lang')) {
    function get_lang()
    {
        $session = session('lang_code');
        return !empty($session) ? $session : 'en';
    }
}

if (!function_exists('financial_year')) {
    function financial_year()
    {
        $minyear = config('settings.financial_year_start_year');
        $maxyear = date('Y') + 1;
        $list = ["" => 'Select'];

        for ($i = $minyear; $i < $maxyear; $i++) {
            $label = $i . '-' . ($i + 1);
            $list[$label] = $label;
        }

        return $list;
    }
}

if (!function_exists('duration')) {
    function duration()
    {
        $list = ["" => 'Select', 'Yearly' => 'Yearly', 'Half Yearly' => 'Half Yearly', 'Quarterly' => 'Quarterly', 'Monthly' => 'Monthly'];
        return $list;
    }
}

if (!function_exists('half_yearly')) {
    function half_yearly()
    {
        $list = ['First Half' => 'First Half', 'Second Half' => 'Second Half'];
        return $list;
    }
}


if (!function_exists('quaterly')) {
    function quaterly()
    {
        $list = ['First Quarter' => 'First Quarter', 'Second Quarter' => 'Second Quarter', 'Third Quarter' => 'Third Quarter', 'Fourth Quarter' => 'Fourth Quarter'];
        return $list;
    }
}

if (!function_exists('quater')) {
    function quater()
    {
        $list = ['First Quarter' => 'First Quarter', 'Second Quarter' => 'Second Quarter', 'Third Quarter' => 'Third Quarter'];
        return $list;
    }
}

if (!function_exists('month_list')) {
    function month_list($key = null)
    {
        $list = ['1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'];
        return ($key) ? $list[$key] : $list;
    }
}

if (!function_exists('gender')) {
    function gender()
    {
        $list = ['' => 'Select', 'male' => 'Male', 'female' => 'Female', 'others' => 'Others'];
        return $list;
    }
}
if (!function_exists('msmeClassification')) {
    function msmeClassification()
    {
        $list = ['' => 'Select', 'Micro' => 'Micro', 'Small' => 'Small', 'Medium' => 'Medium'];
        return $list;
    }
}
if (!function_exists('majorActivity')) {
    function majorActivity()
    {
        $list = ['' => 'Select', 'Services' => 'Services', 'Manufacturing' => 'Manufacturing', '	Trading' => 'Trading'];
        return $list;
    }
}

if (!function_exists('status_filter')) {
    function status_filter()
    {

        $list = ['' => 'Select', '3' => 'Approved', '4' => 'Rejected', '	5' => 'Reverted', '0' => 'Pending'];
        return $list;
    }
}
if (!function_exists('mapping_filter')) {
    function mapping_filter()
    {

        $list = ['' => 'Select', '0' => 'MSE Initiative Mapping', '1' => 'SNP Initiative Mapping'];
        return $list;
    }
}

if (!function_exists('campaign_period')) {
    function campaign_period()
    {
        $list = ["" => 'Select', 'Hours' => 'Hours', 'Days' => 'Days', 'Months' => 'Months'];
        return $list;
    }
}

if (!function_exists('authRoleId')) {
    function authRoleId()
    {
        return \DB::table('user_roles')
            ->select('roles.id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', AuthId())
            ->first()->id;
    }
}

if (!function_exists('authRoleName')) {
    function authRoleName()
    {
        if (session('active_role')) {
            return session('active_role');
        }

        return \DB::table('roles')
            ->where('id', authRoleId())
            ->value('name');
    }
}

if (!function_exists('authFullName')) {
    function authFullName()
    {
        return auth()->user()->full_name;
    }
}

if (!function_exists('getSnpId')) {
    function getSnpId()
    {
        return \DB::table('users')
            ->select('team_snp_scheme.snp_id')
            ->join('team_snp_scheme', 'team_snp_scheme.user_id', '=', 'users.id')
            ->where('users.id', AuthId())
            ->first()->snp_id;
    }
}

if (!function_exists('designType')) {
    function designType()
    {
        $list = ['' => 'Select', 'Structure' => 'Structure', 'Graphic' => 'Graphic', 'Label' => 'Label', 'Combo' => 'Combo'];
        return $list;
    }
}

if (!function_exists('event_for')) {
    function event_for(): array
    {
        return app(App\Web\Workshop\WorkshopService::class)->getEventForRoles();
    }
}


if (!function_exists('getSubDomainIdsByOndcDomains')) {
    function getSubDomainIdsByOndcDomains($subDomains)
    {
        $ondcDomainIds = $subDomains ? json_decode($subDomains) : [];
        return DB::table('sub_domains')
            ->whereIn('ondc_domain_id', $ondcDomainIds)
            ->select('id')
            ->pluck('id')
            ->toArray();
    }
}

if (!function_exists('msmeStatusTypeOptions')) {
    function msmeStatusTypeOptions()
    {
        return [
            '' => 'Select',
            'open' => 'Open MSE',
            'direct' => 'Direct Selection by MSE',
            'bulk' => 'Bulk Upload',
            'onboarded' => 'Onboarded MSEs',
        ];
    }
}




/**
 * Generate next team_id like TEAM000001
 */
if (!function_exists('getNextTeamId')) {

    function getNextTeamId(
        string $table = 'team_msme_schemes',
        string $column = 'team_id',
        string $prefix = 'TEAM'
    ): string {
        // Prefix length + start position
        $start = strlen($prefix) + 1;
        // Get last inserted numeric part
        $row = DB::table($table)
            ->selectRaw("
                CAST(SUBSTRING($column, $start) AS UNSIGNED) AS num_part
            ")
            ->orderByRaw("
                CAST(SUBSTRING($column, $start) AS UNSIGNED) DESC
            ")
            ->first();

        // Generate next number
        $nextNumber = isset($row->num_part) ? ($row->num_part + 1) : 1;
        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}



if (!function_exists('sub_district_list')) {
    function sub_district_list($key = null)
    {
        return CommonService::getDropdownList('sub_districts', $key);
    }
}

if (!function_exists('nsic_branch_offices')) {
    function nsic_branch_offices($key = null)
    {
        return app(App\Web\Workshop\WorkshopService::class)->getNsicBranchOffices($key);
    }
}


if (!function_exists('getSubUserAndParentIds')) {
    function getSubUserAndParentIds($userId = null)
    {
        if (is_null($userId)) {
            $userId = authId();
        }

        $user = DB::table('users')->select('id', 'parent_user_id')->where('id', $userId)->first();
        if (!$user) {
            return [];
        }

        $parentId = $user->parent_user_id ?? $user->id;

        $ids = DB::table('users')
            ->where('parent_user_id', $parentId)
            ->orWhere('id', $parentId)
            ->pluck('id')
            ->toArray();

        return array_unique($ids);
    }
}

