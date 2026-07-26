<?php

namespace App\Http\Controllers;

use App\Models\CrewBankAccount;
use App\Models\CrewProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CrewBankAccountController extends Controller
{

    public function store(Request $request, CrewProfile $crew)
    {
        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:191'],
            'account_name' => ['required', 'string', 'max:191'],
            'account_number' => ['required', 'string', 'max:120'],
            'branch' => ['required', 'string', 'max:191'],
            'routing_number' => ['nullable', 'string', 'max:120'],
            'swift_code' => ['nullable', 'string', 'max:120'],
            'mobile_number' => ['nullable', 'string', 'max:40'],
            'is_own_account' => ['required', 'boolean'],
            'cheque' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
            // Third-party account: relationship + owner NID number + owner NID scan are mandatory;
            // the owner's passport-size photo is optional.
            'owner_relationship' => ['nullable', 'required_if:is_own_account,0', 'string', 'max:120'],
            'owner_nid' => ['nullable', 'required_if:is_own_account,0', 'string', 'max:120'],
            'owner_nid_scan' => ['nullable', 'required_if:is_own_account,0', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
            'owner_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:8192'],
        ]);

        // File uploads → their *_path columns.
        foreach (['cheque' => 'cheque_scan_path', 'owner_nid_scan' => 'owner_nid_scan_path', 'owner_photo' => 'owner_photo_path'] as $input => $column) {
            if ($request->hasFile($input)) {
                $data[$column] = $request->file($input)->store('crew/bank', 'public');
            }
            unset($data[$input]);
        }

        // An own account carries no third-party owner details.
        if ((bool) $data['is_own_account']) {
            $data['owner_relationship'] = null;
            $data['owner_nid'] = null;
        }

        $crew->bankAccounts()->create($data);
        return back()->with('status', 'Bank account added.');
    }

    public function destroy(CrewProfile $crew, CrewBankAccount $bankAccount)
    {
        abort_unless($bankAccount->crew_profile_id === $crew->id, 404);
        $bankAccount->delete();
        return back()->with('status', 'Bank account removed.');
    }
}
