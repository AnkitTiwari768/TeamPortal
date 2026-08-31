<?php

namespace App\Web\SNP;

use App\Utils\UuidGenerator;
use Illuminate\Support\Facades\DB;

class MsmeSnpMappingAction
{
    public function __construct()
    {
        // Initialization code if needed
    }

    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $result = [];

            $msmeIdIsExists = DB::table('team_snpmsme_mapping')->where('msme_id', $data['msme_id'])->exists();
			//dd($data['msme_id']);

            if ($msmeIdIsExists) {
                DB::table('team_snpmsme_mapping')
                    ->where('msme_id', $data['msme_id'])
                    ->update([
					    'snp_id' => $this->getSnpId(auth()->user()->id),
                        'status' => true,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('team_snpmsme_mapping')->insert([
                    'id' => UuidGenerator::uuid7(),
                    'snp_id' => $this->getSnpId(auth()->user()->id),
                    'msme_id' => $data['msme_id'],
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('team_msme_schemes')->where('id', $data['msme_id'])
                ->update([
                    'bpp_id' => $data['bpp_id'],
                    'bpp_updated_at' => now(),
                ]);

            return ['url' => url('/onboarded-msme')];
        });
    }

    public function getSnpId(string $userId): string
    {
        return DB::table('team_snp_scheme')->where('user_id', $userId)->value('id') ?? '';
    }
}
