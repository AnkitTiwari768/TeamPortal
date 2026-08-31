<?php 

namespace App\Http\Api\V1\Workshop_Integeration;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Http\Api\V1\Workshop_Integeration\WorkshopService;


class WorkshopController extends Controller
{
    private static string $module = 'event';

    public function __construct(private WorkshopService $service){}

    public function getEventCards()
    {      
        $title = __('Event List');

        $cardData = $this->service->cardData();

        if($cardData)
        {

            $cardData = collect($cardData)->map(function ($item) {

                if (!empty($item->file_path) && !empty($item->file_system_name)) {
                    $item->image_url = asset('storage/app/' . $item->file_path . '/' . $item->file_system_name);
                } else {
                    $item->image_url = null;
                }

                return $item;
            });


            return response()->json([
                'status' => true,
                'data' => $cardData,
                'title' => $title,
                'message' => 'Data fetch successfully.'
            ], 200);
        }
        else{
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
             
               
    }

    public function getAllList()
    {
        $title = __('Event List');

        $listData = $this->service->getList();

        if($listData)
        {
            return response()->json([
                'status' => true,
                'data' => WorkshopResource::collection($listData),
                'title' => $title,
                'message' => 'Data fetch successfully.'
            ], 200);
        }
        else{
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }
    

    public function viewEventDetails($id)
    {
        $title = __('View Event');

        $event = Workshop::find($id);
        
        if($event){

            //$event_for = \DB::table('roles')->where('id', $event->event_for)->select('id', 'name')->first();
            $roleIds = is_string($event->event_for) ? json_decode($event->event_for, true) : $event->event_for;
            $event_for = \DB::table('roles')->whereIn('id', $roleIds)->pluck('name')->implode(', ');
            $uploadedImage = $this->service->getUploadImageData($id);
            $scheduleData = $this->service->getScheduleSummary($event->schedules);
            $imageUrl = null;

            if ($uploadedImage->count() > 0) {
                $image = $uploadedImage[0];

                $imageUrl = asset('storage/app/'.$image->file_path . '/' . $image->file_system_name);
            }

            $listData = [
                'event' => $event,
                'event_for' => $event_for,
                'uploadedImage' => $uploadedImage,
                'scheduleData' => $scheduleData,
                'imageUrl' => $imageUrl
            ];

            return response()->json([
                'status' => true,
                'data' => $listData,
                'title' => $title,
                'message' => 'Data fetch successfully.'
            ], 200);
        }else{

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }  
    }

    /*public function deleteEvent($id)
    {
        //dd($id);
        $workshop = Workshop::find($id);

        if (!$workshop) {
            return response()->json([
                'status' => false,
                'message' => 'Event not found.'
            ], 404);
        }

        if (!empty($workshop->uploaded_ids)) {

            $imageIds = explode(',', $workshop->uploaded_ids);
            \DB::table('file_uploads')->whereIn('id', $imageIds)->delete();
        }

        $workshop->delete();

        return response()->json([
            'status' => true,
            'message' => 'Event deleted successfully.'
        ], 200);
    }*/



}