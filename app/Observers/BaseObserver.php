<?php namespace App\Observers;
 
use Illuminate\Support\Facades\Request;
use App\Observers\AuditTrailLog;

class BaseObserver {

    // protected static $module;

     /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */

     public function __construct(AuditTrailLog $auditTrailLog)
     {
        $this->auditTrailLog = $auditTrailLog;
     }

    public function created($model): void
    {   
        $this->auditTrailLog
            ->setModuleName($model->module)
            ->setActivityType('Added')
            ->setActivityData((array) Request::all())
            ->save();
    }
 
    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated($model)
    {

        $this->auditTrailLog
            ->setModuleName($model->module)
            ->setActivityType('Updated')
            ->setActivityData((array) Request::all())
            ->save();
        
    }
 
    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted($model)
    { 
        $this->auditTrailLog
            ->setModuleName($model->module)
            ->setActivityType('Deleted')
            ->setActivityData((array) Request::all())
            ->save();
    }
 
    /**
     * Handle the User "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored()
    {
        //
    }
 
    /**
     * Handle the User "forceDeleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted()
    {
        //
    }
}