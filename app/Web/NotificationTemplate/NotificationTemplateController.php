<?php
declare(strict_types=1);

namespace App\Web\NotificationTemplate;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;

class NotificationTemplateController extends ClientController
{
    public function __construct(private NotificationTemplateService $service){}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'data' => $this->service->getTemplates()
            ]);
        }
        $title = 'Notification Template List';
        return view('notification_template.index', compact('title'));
    }

    public function create(): View
    {
        $title = 'Add Notification Template';
        return view('notification_template.form', compact('title'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), NotificationTemplateRequest::getRules());

        if ($validator->fails()) {
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ],422);
        }

        $this->service->store($validator->validated());

        return response()->json([
            'status'=>true,
            'message'=>'Notification Template Created Successfully'
        ]);
    }

    public function edit($id): View
    {
        $title = 'Edit Notification Template';
        $row = $this->service->getById($id);

        return view('notification_template.form', compact('title','row','id'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),NotificationTemplateRequest::getRules($id));

        if ($validator->fails()) {
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ],422);
        }

        $this->service->store($validator->validated(), $id);

        return response()->json([
            'status'=>true,
            'message'=>'Notification Template Updated Successfully'
        ]);
    }
}