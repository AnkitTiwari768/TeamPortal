<?php

declare(strict_types=1);

namespace App\Domain\Batch;

enum BatchStatus: int
{
    case DRAFT                          = 1;
    case PENDING                        = 2;
    case BATCH_CREATED                  = 3;
    case SENT_TO_ONDC                   = 4;
    case APPROVED_BY_ONDC               = 5;
    case REJECTED_BY_ONDC               = 6;

    case SENT_TO_NSIC                   = 7;
    case APPROVED_BY_NSIC               = 8;
    case REJECTED_BY_NSIC               = 9;
    case SENT_TO_SNP                    = 10;
    case SENT_TO_CA                     = 11;

    case CA_CERTIFICATE_UPLOADED        = 12;
    case SENT_TO_SNP_FOR_INVOICE        = 13;
    case INVOICE_UPLOADED               = 14;

    case SENT_TO_NSIC_BY_SNP               = 15;
    case SENT_TO_NSIC_FINANCE           = 16;
    case APPROVED                       = 17;
    case REJECTED_NSIC_FINANCE          = 18;

    case PAYMENT_COMPLETED              = 19;

    case RESUBMITTED                    = 20;

    //case APPROVED = 80;


    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::DRAFT => 'Drafts',
            self::BATCH_CREATED => 'Pending',
            self::SENT_TO_ONDC => 'Pending',
            self::APPROVED_BY_ONDC => 'Approved',
            self::REJECTED_BY_ONDC => 'Rejected',
            self::SENT_TO_NSIC => 'Sent to NSIC',
            self::APPROVED_BY_NSIC => 'Approved',
            self::REJECTED_BY_NSIC => 'Rejected',
            self::APPROVED => 'Approved',
            self::REJECTED_NSIC_FINANCE => 'Rejected',
            self::SENT_TO_SNP => 'Upload CA Certificate',
            self::SENT_TO_CA => 'Pending',
            self::CA_CERTIFICATE_UPLOADED => 'CA Certificate Uploaded',
            self::SENT_TO_NSIC_BY_SNP => 'CA & Invoice Uploaded',
            self::SENT_TO_SNP_FOR_INVOICE => 'Upload Invoice',
            self::INVOICE_UPLOADED => 'Invoice Uploaded',
            self::SENT_TO_NSIC_FINANCE => 'Sent to NSIC Finance',
            self::PAYMENT_COMPLETED => 'Payment Completed',
            self::RESUBMITTED => 'Re-submitted',
        };
    }

    public static function getLabelByValue(int $value): string
    {
        return match ($value) {
            // ✅ FIX: Missing cases added
            self::DRAFT->value => 'Drafts', // FIX: was missing
            self::BATCH_CREATED->value => 'Pending', // FIX: was missing

            self::PENDING->value => 'Pending',
            self::SENT_TO_ONDC->value => (hasRole('ondc-admin') || hasRole('nsic-checker')) ? 'Pending' : 'Approved',
            self::APPROVED_BY_ONDC->value => 'Approved',
            self::REJECTED_BY_ONDC->value => 'Rejected',
            self::SENT_TO_NSIC->value => ((hasRole('snp') || hasRole('lsp')) || hasRole('nsic')) ? 'Pending' : 'Approved',
            self::APPROVED_BY_NSIC->value => 'Approved',
            self::REJECTED_BY_NSIC->value => 'Rejected',
            self::SENT_TO_SNP->value => ((hasRole('snp') || hasRole('lsp'))) ? 'Pending' : 'Approved',
            self::SENT_TO_CA->value => ((hasRole('snp') || hasRole('lsp'))) ? 'Pending' : 'Approved',
            self::CA_CERTIFICATE_UPLOADED->value => 'CA Certificate Uploaded',
            self::SENT_TO_SNP_FOR_INVOICE->value => (hasRole('ca')) ? 'Approved' : 'Invoice Required',
            self::INVOICE_UPLOADED->value => 'Invoice Uploaded',
            self::SENT_TO_NSIC_BY_SNP->value => ((hasRole('snp') || hasRole('lsp')) || hasRole('nsic-finance')) ? 'Pending' : 'Approved',
            self::SENT_TO_NSIC_FINANCE->value => ((hasRole('snp') || hasRole('lsp')) || hasRole('nsic-finance')) ? 'Pending' : 'Approved',
            self::APPROVED->value => 'Approved',
            self::REJECTED_NSIC_FINANCE->value => 'Rejected',
            self::PAYMENT_COMPLETED->value => 'Payment Completed',
            self::RESUBMITTED->value => 'Re-submitted',
            // ✅ FIX: Safety fallback to prevent UnhandledMatchError
            default => 'Unknown Status', // FIX: prevents crash if new status added
        };
    }


    public static function getStatusByKey(string $key)
    {
        return match ($key) {
            'draft' => self::DRAFT->value,
            'batch_created' => self::BATCH_CREATED->value,
            'sent_to_onddc' => self::SENT_TO_ONDC->value,
            'sent_to_nsic_checker' => self::SENT_TO_ONDC->value,
            'approved_by_onddc' => self::APPROVED_BY_ONDC->value,
            'rejected_by_onddc' => self::REJECTED_BY_ONDC->value,
            'sent_to_nsic' => self::SENT_TO_NSIC->value,
            'approved_by_nsic' => self::APPROVED_BY_NSIC->value,
            'rejected_by_nsic' => self::REJECTED_BY_NSIC->value,
            'sent_to_snp' => self::SENT_TO_SNP->value,
            'sent_to_ca' => self::SENT_TO_CA->value,
            'ca_certificate_uploaded' => self::CA_CERTIFICATE_UPLOADED->value,
            'sent_to_snp_for_invoice' => self::SENT_TO_SNP_FOR_INVOICE->value,
            'invoice_uploaded' => self::INVOICE_UPLOADED->value,
            'sent_to_nsic_by_snp' => self::SENT_TO_NSIC_BY_SNP->value,
            'sent_to_nsic_finance' => self::SENT_TO_NSIC_FINANCE->value,
            'approved' => self::APPROVED->value,
            'rejected_by_nsic_finance' => self::REJECTED_NSIC_FINANCE->value,
            'payment_completed' => self::PAYMENT_COMPLETED->value,
            // ✅ FIX: safety fallback
            default => null, // FIX: prevents UnhandledMatchError if wrong key passed
        };
    }
}
