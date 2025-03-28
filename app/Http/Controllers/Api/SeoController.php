<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Task;
use App\Models\User;
use App\Models\Campaigns;
use App\Models\Seo;

class SeoController extends Controller
{
    public function CreateSeoUpdate(Request $request)
    {
        try {
            // **Validation Rules**
            $validator = Validator::make($request->all(), [
                'date'                        => 'required|date',
                'keywords'                    => 'required|string',
                'search_volume'               => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // **Check if ID exists (Update case)**
            if (!empty($request->id)) {
                // If ID exists, update the SEO record
                $seo = Seo::find($request->id);

                if (!$seo) {
                    return response()->json(['message' => 'SEO record not found.'], 404);
                }
            } else {
                // If no ID is provided, create a new SEO record
                $seo = new Seo();
            }

            // **Set Fields (Keeps Old Values If Not Provided)**
            $seo->date                        = $request->date ?? $seo->date;
            $seo->keywords                    = $request->keywords ?? $seo->keywords;
            $seo->search_volume               = $request->search_volume ?? $seo->search_volume;
            $seo->technical_seo_issues_fixed  = $request->technical_seo_issues_fixed ?? $seo->technical_seo_issues_fixed;
            $seo->schema_markup_added         = $request->schema_markup_added ?? $seo->schema_markup_added;
            $seo->traffic                     = $request->traffic ?? $seo->traffic;
            $seo->onpage_optimization_status  = $request->onpage_optimization_status ?? $seo->onpage_optimization_status;
            $seo->backlink_created            = $request->backlink_created ?? $seo->backlink_created;
            $seo->backlink_source_url         = $request->backlink_source_url ?? $seo->backlink_source_url;
            $seo->page_url                    = $request->page_url ?? $seo->page_url;
            $seo->pages_per_session           = $request->pages_per_session ?? $seo->pages_per_session;
            $seo->conversion_rate             = $request->conversion_rate ?? $seo->conversion_rate;
            $seo->session_duration            = $request->session_duration ?? $seo->session_duration;
            $seo->bounce_rate                 = $request->bounce_rate ?? $seo->bounce_rate;
            $seo->click_through_rate          = $request->click_through_rate ?? $seo->click_through_rate;
            $seo->competitor_analysis_notes   = $request->competitor_analysis_notes ?? $seo->competitor_analysis_notes;
            $seo->status                      = $request->status ?? $seo->status;
            $seo->priority                    = $request->priority ?? $seo->priority;

            // **Save the data to the database**
            $seo->save();

            // **Response Message**
            $message = !empty($request->id) ? 'SEO record updated successfully.' : 'SEO record created successfully.';

            // **Return JSON Response**
            return response()->json([
                'success' => true,
                'message' => $message,
                'seo'     => $seo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while processing the request.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
