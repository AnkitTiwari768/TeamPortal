<?php 

declare(strict_types=1);

namespace App\Enums;

enum NotificationType : string
{
    case Submit = 'submit';
    case Approve  = 'approve';
    case Reject  = 'reject';
    case Revert  = 'revert';
    case Reply  = 'reply';
    case Forward  = 'forward';
    case AllowModification  = 'allow-modification-permission';
    case ModificationComplete  = 'complete-modification-permission';
    case GrievanceRaised = 'grievance-raised';
    case GrievanceTicketClosed = 'grievance-ticket-closed';
    case GrievanceTicketReopen = 'grievance-ticket-reopened';
    case HoldResume = 'hold-resume';
}