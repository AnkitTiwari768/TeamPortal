<?php 
declare(strict_types=1);
namespace App\Web\Email;
use Illuminate\Http\Request;
use App\Http\Controllers\ClientController;

class ProductionCategoryController extends ClientController
{
    
    public function __construct(){
	
	}

    Public function sendEmail(Request $request)
	{

	/* This method will call SendEmailJob Job*/

	dispatch(new SendEmailJob($request));

	}

    
}