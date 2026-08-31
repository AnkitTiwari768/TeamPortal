<?php

namespace App\Http\Api\V1\Setting;

use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;


    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(function () {

            $settings = static::pluck('value', 'key')->toArray();

            $parsable_string = var_export($settings, true);

            $content = "<?php return {$parsable_string};";

            File::put(config_path('settings.php'), $content);
        });
    }


    //...
}