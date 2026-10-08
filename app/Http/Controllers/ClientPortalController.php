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
            'email' => ['required', 'email'],
            'reference_no' => ['required', 'string', 'max:50'],
        ]);

        $caseRecord = CaseRecord::with('attorney')
            ->where('client_email', $data['email'])
            ->where('reference_no', $data['reference_no'])
            ->first();

        return view('pages.client-portal', [
            'caseRecord' => $caseRecord,
            'notFound' => ! $caseRecord,
        ]);
    }
}
