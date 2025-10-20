<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnydeskInformations;

class UserController extends Controller
{
    public function submitAnydeskID(Request $request)
    {
        // Validate inputs
        $request->validate([
            'anydesk_id' => 'required|string|max:255',
            'pc_info' => 'required|string|max:255',
        ]);

        // Save to database
        AnydeskInformations::create([
            'anydesk_id' => $request->anydesk_id,
            'pc_info' => $request->pc_info,
        ]);

        // If request came from API (expects JSON)
        if ($request->wantsJson()) {
            return response()->json([
                'message' => "Anydesk ID {$request->anydesk_id} submitted successfully.",
                'status' => 'success'
            ]);
        }

        // If from web form — redirect back
        return redirect()->back()->with('success', 'New PC added successfully!');
    }

    public function test()
    {
        return response()->json([
            'message' => 'http://192.168.1.61:8000/api/moreventures/submit?anydesk_id=sdasdsa&pc_info=sudauwdwa'
        ]);
    }
    
    public function deletePC($id)
    {
        $pc = AnydeskInformations::find($id);

        if (!$pc) {
            return response()->json(['status' => 'error', 'message' => 'Record not found.']);
        }

        $pc->delete();

        return response()->json(['status' => 'success', 'message' => 'PC deleted successfully.']);
    }


    public function MonitoringAccess()
    {
        // ✅ Use your actual model
        $pcs = AnydeskInformations::select('pc_info as name', 'anydesk_id')->get();

        // ✅ Return the Blade view
        return view('dashboard', compact('pcs'));
    }
}
