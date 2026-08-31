<?php
declare(strict_types=1);

namespace App\Web\EmailTemplate;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;

class EmailTemplateController extends ClientController
{
    public function __construct(private EmailTemplateService $service){}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'data' => $this->service->getTemplates()
            ]);
        }
        $title = 'Email Template List';
        return view('email_template.index', compact('title'));
    }

    public function create(): View
    {
        $title = 'Add Email Template';
        return view('email_template.form', compact('title'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), EmailTemplateRequest::getRules());

        if ($validator->fails()) {
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ],422);
        }

        $this->service->store($validator->validated());

        return response()->json([
            'status'=>true,
            'message'=>'Email Template Created Successfully'
        ]);
    }

    public function edit($id): View
    {
        $title = 'Edit Email Template';
        $row = $this->service->getById($id);

        return view('email_template.form', compact('title','row','id'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),EmailTemplateRequest::getRules($id));

        if ($validator->fails()) {
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ],422);
        }

        $this->service->store($validator->validated(), $id);

        return response()->json([
            'status'=>true,
            'message'=>'Email Template Updated Successfully'
        ]);
    }
}