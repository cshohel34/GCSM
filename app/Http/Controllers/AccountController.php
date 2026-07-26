<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(LedgerService $ledger)
    {
        $accounts = Account::orderBy('code')->get();
        $balances = [];
        foreach ($accounts as $a) {
            if (! $a->is_group) $balances[$a->id] = $ledger->balance($a);
        }
        return view('accounting.accounts.index', compact('accounts', 'balances'));
    }

    public function create()
    {
        return view('accounting.accounts.form', ['account' => new Account(), 'parents' => Account::orderBy('code')->get()]);
    }

    public function store(Request $request)
    {
        Account::create($this->validated($request));
        return redirect()->route('accounting.accounts.index')->with('status', 'Account created.');
    }

    public function edit(Account $account)
    {
        return view('accounting.accounts.form', ['account' => $account, 'parents' => Account::where('id', '!=', $account->id)->orderBy('code')->get()]);
    }

    public function update(Request $request, Account $account)
    {
        $account->update($this->validated($request, $account->id));
        return redirect()->route('accounting.accounts.index')->with('status', 'Account updated.');
    }

    protected function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('accounts', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:191'],
            'type' => ['required', Rule::in(['asset', 'liability', 'equity', 'income', 'expense'])],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'is_group' => ['nullable', 'boolean'],
            'is_cash_bank' => ['nullable', 'boolean'],
            'currency' => ['required', Rule::in(['BDT', 'USD'])],
            'opening_balance' => ['nullable', 'numeric'],
            'opening_side' => ['nullable', Rule::in(['debit', 'credit'])],
        ]);
    }
}
