<?php

namespace App\Http\Controllers;

use App\Models\Principal;
use App\Models\PrincipalContact;
use Illuminate\Http\Request;

class PrincipalContactController extends Controller
{
    public function store(Request $request, Principal $principal)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'designation' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:191'],
            'whatsapp' => ['nullable', 'string', 'max:60'],
            'wechat_id' => ['nullable', 'string', 'max:120'],
            'linkedin' => ['nullable', 'string', 'max:191'],
            'facebook' => ['nullable', 'string', 'max:191'],
            'office_address' => ['nullable', 'string'],
            'is_primary' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('principals/contacts', 'public');
        }
        unset($data['photo']);
        $principal->contacts()->create($data);
        return back()->with('status', 'Contact added.');
    }

    public function destroy(Principal $principal, PrincipalContact $contact)
    {
        abort_unless($contact->principal_id === $principal->id, 404);
        $contact->delete();
        return back()->with('status', 'Contact removed.');
    }
}
