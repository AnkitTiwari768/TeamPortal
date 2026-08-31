<?php

declare(strict_types = 1);

namespace App\Observers;

use App\Http\Api\V1\AuditTrail\AuditTrail;

class AuditTrailLog 
{
	private string $moduleName;
	private string $activityType;
	private string $activityData;
	

	public function __construct()
	{
		$this->userId = auth()->user()->id??null;
		$this->lastLogin = auth()->user()->last_login_at??null;
		$this->ipAddress = request()->ip();
		$this->createdAt = date('Y-m-d H:i:s');
	}
	
	public function setModuleName(string $moduleName): AuditTrailLog
	{
		$this->moduleName = $moduleName;
		return $this;
	}

	public function setActivityType(string $activityType): AuditTrailLog
	{
		$this->activityType= $activityType.' '. ucwords(strtolower($this->moduleName));
		return $this;
	}

	public function setActivityData(array $activityData): AuditTrailLog
	{	 
		$this->activityData = json_encode($activityData); 
		return $this;
	}


	public function save() : bool
	{
		return (bool) AuditTrail::create([
			'module_name' => $this->moduleName,
            'activity_type' => $this->activityType,
            'activity_data' => $this->activityData,
            'user_id' => $this->userId,
            'last_login' => $this->lastLogin,
            'ip_address' => $this->ipAddress,
            'created_at' => $this->createdAt
		]);
	}
}