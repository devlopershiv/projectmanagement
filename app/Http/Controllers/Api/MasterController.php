<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Master;

class MasterController extends Controller
{
    public function listall()
    {
        $user = Auth::user();
        $list = Master::where('companyid', $user['companyid'])
            ->where('type', '>', 0)
            ->where('rolledback', 0)
            ->get();
        return response()->json($list);
    }

    public function getValues(Request $request)
    {
        $user = Auth::user();
        $list = Master::where('type', $request['type'])
            ->where('rolledback', 0)
            ->get();
        return response()->json($list);
    }

    public function createupdate(Request $request)
    {

        $validator = validator($request->all(), [
            'type' => 'required',
            'label' => 'required'
        ]);

        if ($validator->fails()) {

            return [
                'success' => false,
                'message' => $validator->errors()->first()
            ];
        } else {
            if (isset($request['id']) && $request['id'] != 0 && $request['id'] != null) {
                $master = Master::findOrFail($request['id']);
                // $keys = ['type','label','labelid','value_string','value','sequence','note'];
                $master['type'] = $request['type'] ?? 0;
                $master['label'] = $request['label'];

                // $master['sequence']=$request['sequence'];
                $master['value'] = $request['value'] ?? 0;
                $master['value_string'] = $request['value_string'];
                $master['note'] = $request['note'];
                // foreach($keys as $k){
                // 	if($request[$k]!=null && $request[$k]!="")
                // 		$master[$k]=$request[$k];
                // }
                $master->update();
                $list = Master::where('type', $request['type'])->where('rolledback', 0)->get();
            } else {

                if ($request['type'] > 0) {

                    $labelid = Master::where('type', $request['type'])->where('rolledback', 0)->max('labelid');
                    $master['labelid'] = $labelid + 1;
                } else {

                    $labelid = Master::where('type', 0)->where('rolledback', 0)->max('labelid');
                    $master['labelid'] = $labelid + 1;
                }

                $master['type'] = $request['type'] ?? 0;
                $master['label'] = $request['label'];

                // $master['sequence']=$request['sequence'];
                $master['value'] = $request['value'] ?? 0;
                $master['value_string'] = $request['value_string'];
                $master['note'] = $request['note'];
                $master = Master::create($master);
                $list = Master::where('type', $request['type'])->where('rolledback', 0)->get();
            }
            return response()->json($list);
        }
    }

    public function delete(Request $request)
    {

        $validator = validator($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {

            return [
                'success' => false,
                'message' => $validator->errors()->first()
            ];
        } else {

            try {
                $master = Master::findOrFail($request['id']);
                $master['rolledback'] = 1;
                $master->update();

                $response = [
                    'success' => true,
                    'message' => 'Data deleted successfully'
                ];

                return response()->json($response);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                // Return error message in JSON if ID not found
                return response()->json(['success' => false, 'message' => 'Invalid Id to delete '], 404);
            }
        }
    }
}
