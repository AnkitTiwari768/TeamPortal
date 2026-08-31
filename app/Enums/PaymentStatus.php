<?php 

// declare(strict_types=1);

// namespace App\Enums;

// use App\Contracts\Labelable;
// use App\Traits\EnumToArray;

// enum PaymentStatus: int implements Labelable
// {
//     use EnumToArray;
    
//     case Completed = 1;
//     case Success = 2;
//     case Pending = 3;
//     case Failed = 4;
//     case Processing = 5;

//     public function getLabel(): string
//     {
//         return match ($this) {
//             PaymentStatus::Completed => __('message.completed'),
//             PaymentStatus::Success => __('message.success'),
//             PaymentStatus::Pending => __('message.pending'),
//             PaymentStatus::Failed => __('message.failed'),
//             PaymentStatus::Processing => __('message.processing'),
//             default => ''
//         };
//     }

//     public static function getLabelByValue(int $value): string 
//     {
//         return match ($value) {
//             PaymentStatus::Completed->value => __('message.completed'),
//             PaymentStatus::Success->value => __('message.success'),
//             PaymentStatus::Pending->value => __('message.pending'),
//             PaymentStatus::Failed->value => __('message.failed'),
//             PaymentStatus::Processing->value => __('message.processing'),
//             default => ''
//         };
//     }
// }

declare(strict_types=1);

namespace App\Enums;

use App\Contracts\Labelable;
use App\Traits\EnumToArray;

enum PaymentStatus: int implements Labelable
{
    use EnumToArray;
    
    case Success = 1;
    case Failed = 2;
    case Declined = 3;
    case Initiated = 4;
    case Refunded = 5;
    case Pending = 6;
    
    public function getLabel(): string
    {
        return match ($this) {
            PaymentStatus::Success => __('Success'),
            PaymentStatus::Failed => __('Failed'),
            PaymentStatus::Declined => __('Declined'),
            PaymentStatus::Initiated => __('Initiated'),
            PaymentStatus::Refunded => __('Refunded'),
            PaymentStatus::Pending => __('Pending'),
            default => ''
        };
    }

    public static function getLabelByValue(int $value): string 
    {
        return match ($value) {
            PaymentStatus::Success->value => __('Success'),
            PaymentStatus::Failed->value => __('Failed'),
            PaymentStatus::Declined->value => __('Declined'),
            PaymentStatus::Initiated->value => __('Initiated'),
            PaymentStatus::Refunded->value => __('Refunded'),
            PaymentStatus::Pending->value => __('Pending'),
            default => ''
        };
    }
}
