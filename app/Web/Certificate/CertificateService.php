<?php
namespace App\Web\Certificate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CertificateService
{
    /**
     * Roles allowed to download the certificate.
     */
    private const ALLOWED_ROLES = ['bnp', 'snp', 'lsp', ];

    public function __construct(private CertificateAction $certificateAction)
    {
    }

    public function generateCertificate()
    {
        try {
            $user = Auth::user();
            $activeRole = session('active_role');

            abort_unless(in_array($activeRole, self::ALLOWED_ROLES, true), 403);

            $snpEntry = DB::table('team_snp_scheme')
                ->where('user_id', $user->id)
                ->first();

            abort_if(!$snpEntry, 404);

            $networkProvider = DB::table('network_providers')
                ->where('id', $snpEntry->network_provider_id)
                ->where('is_snp', true)
                ->first();

            abort_if(!$networkProvider, 404);

            $role = DB::table('roles')
                ->where('slug', $activeRole)
                ->value('name');

            abort_if(!$role, 404);

            return $this->certificateAction->generate($networkProvider, $role);

        } catch (\Throwable $e) {
            \Log::error($e);

            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}

