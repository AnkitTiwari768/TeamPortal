<?php 
declare(strict_types=1);
namespace App\Web\ProductDomainMapping;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Traits\{HasAttribute};
use App\Http\Services\CommonService;
use App\Models\User;
use App\Contracts\GrantType;
use DB;
use Illuminate\Support\Str;
use App\Services\PHPMailerService;

class ProductDomainMappingService extends BaseService
{
    use DataTable,HasAttribute;

    public static function getAllDomainCategory()
    {
        return DB::table('sub_domains')
            ->select(
                'id',
                'name',
                'ondc_domain_id',
                'aov_grouping_type',
                'minimum_order_value'
            )->where('status',true)->orderBy('name','asc')->get();
    }

    public function getDomainOndcId($domainType)
    {
        //dd($domainType);
        $query = DB::table('sub_domains')->select('id', 'name', 'ondc_domain_id');
        if ($domainType) {
            $query->where('id',$domainType);
        }
        return $query->get();
    }
}