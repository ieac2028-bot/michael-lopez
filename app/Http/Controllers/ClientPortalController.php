<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use Illuminate\Http\Request;

class ClientPortalController extends Controller
{
    public function show()
    {
        return view('pages.client-portal', ['caseRecord' => null, 'notFound' => false]);
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'reference_no' => ['required', 'string', 'max:50'],
        ]);

        $caseRecord = CaseRecord::with(['attorney', 'practiceArea'])
            ->whereRaw('LOWER(client_email) = ?', [mb_strtolower($request->user()->email)])
            ->where('reference_no', $data['reference_no'])
            ->first();

        return response()->view('pages.client-portal', [
            'caseRecord' => $caseRecord,
            'notFound' => ! $caseRecord,
        ], $caseRecord ? 200 : 404);
    }
}
