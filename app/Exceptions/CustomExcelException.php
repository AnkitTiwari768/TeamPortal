<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use App\Traits\Respond;

class CustomExcelException extends Exception 
{
	use Respond;

	public function __construct($currentRow)
	{
		$this->currentRow = $currentRow;		
	}

	public function render($request)
	{
		return $this->error("The passport expiry date must be greater than passport issue date on row[{$this->currentRow}]");
	}
}