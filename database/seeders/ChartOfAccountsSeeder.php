<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Ship-manning chart of accounts (CA-designed). Groups are non-postable headers;
 * leaf accounts are where vouchers post. Codes follow the 1000-Asset … 5000-Expense
 * convention. Crew salary is a LIABILITY (pass-through), not income.
 */
class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // [code, name, type, is_group, parent_code, is_cash_bank, currency]
        $rows = [
            // ===== ASSETS =====
            ['1000', 'Assets', 'asset', 1, null, 0, 'BDT'],
            ['1100', 'Current Assets', 'asset', 1, '1000', 0, 'BDT'],
            ['1110', 'Cash in Hand', 'asset', 0, '1100', 1, 'BDT'],
            ['1120', 'Bank — BDT (Operating)', 'asset', 0, '1100', 1, 'BDT'],
            ['1130', 'Bank — USD (Salary Inflow)', 'asset', 0, '1100', 1, 'USD'],
            ['1140', 'bKash / MFS', 'asset', 0, '1100', 1, 'BDT'],
            ['1200', 'Receivables', 'asset', 1, '1000', 0, 'BDT'],
            ['1210', 'Agency Fee Receivable', 'asset', 0, '1200', 0, 'BDT'],
            ['1220', 'Air Ticket Receivable', 'asset', 0, '1200', 0, 'BDT'],
            ['1230', 'Service Charge Receivable', 'asset', 0, '1200', 0, 'BDT'],
            ['1240', 'Advance to Crew / Staff', 'asset', 0, '1200', 0, 'BDT'],
            ['1300', 'Fixed Assets', 'asset', 1, '1000', 0, 'BDT'],
            ['1310', 'Office Equipment', 'asset', 0, '1300', 0, 'BDT'],

            // ===== LIABILITIES =====
            ['2000', 'Liabilities', 'liability', 1, null, 0, 'BDT'],
            ['2100', 'Current Liabilities', 'liability', 1, '2000', 0, 'BDT'],
            ['2110', 'Crew Salary Payable', 'liability', 0, '2100', 0, 'BDT'],
            ['2120', 'Partner Payable', 'liability', 0, '2100', 0, 'BDT'],
            ['2130', 'Office Staff Salary Payable', 'liability', 0, '2100', 0, 'BDT'],
            ['2140', 'Foreign Company Commission Payable', 'liability', 0, '2100', 0, 'BDT'],
            ['2150', 'Accounts Payable (Sundry)', 'liability', 0, '2100', 0, 'BDT'],
            ['2160', 'Tax / VAT Payable', 'liability', 0, '2100', 0, 'BDT'],

            // ===== EQUITY =====
            ['3000', 'Equity', 'equity', 1, null, 0, 'BDT'],
            ['3010', "Owner's Capital", 'equity', 0, '3000', 0, 'BDT'],
            ['3020', "Owner's Drawings", 'equity', 0, '3000', 0, 'BDT'],
            ['3030', 'Retained Earnings', 'equity', 0, '3000', 0, 'BDT'],

            // ===== INCOME =====
            ['4000', 'Income', 'income', 1, null, 0, 'BDT'],
            ['4010', 'Agency Fee Income', 'income', 0, '4000', 0, 'BDT'],
            ['4020', 'Service Charge Income', 'income', 0, '4000', 0, 'BDT'],
            ['4030', 'FX / Conversion Charge Income', 'income', 0, '4000', 0, 'BDT'],
            ['4040', 'Profit Share Income', 'income', 0, '4000', 0, 'BDT'],
            ['4090', 'Other Income', 'income', 0, '4000', 0, 'BDT'],

            // ===== EXPENSES =====
            ['5000', 'Expenses', 'expense', 1, null, 0, 'BDT'],
            ['5010', 'Partner Commission', 'expense', 0, '5000', 0, 'BDT'],
            ['5020', 'Foreign Company Commission', 'expense', 0, '5000', 0, 'BDT'],
            ['5030', 'Office Staff Salary', 'expense', 0, '5000', 0, 'BDT'],
            ['5040', 'Office Staff Bonus', 'expense', 0, '5000', 0, 'BDT'],
            ['5050', 'Office Rent', 'expense', 0, '5000', 0, 'BDT'],
            ['5060', 'Office & Operational Expense', 'expense', 0, '5000', 0, 'BDT'],
            ['5070', 'License, Audit & Compliance Fee', 'expense', 0, '5000', 0, 'BDT'],
            ['5080', 'Government / Official Fee', 'expense', 0, '5000', 0, 'BDT'],
            ['5090', 'Owner Travel Expense', 'expense', 0, '5000', 0, 'BDT'],
            ['5100', 'Air Ticket Expense', 'expense', 0, '5000', 0, 'BDT'],
            ['5110', 'Bank Charges', 'expense', 0, '5000', 0, 'BDT'],
            ['5120', 'Utilities & Internet', 'expense', 0, '5000', 0, 'BDT'],
            ['5900', 'Other Expense', 'expense', 0, '5000', 0, 'BDT'],
        ];

        // First pass: insert without parent (to resolve codes to ids).
        foreach ($rows as [$code, $name, $type, $group, $parent, $cashbank, $cur]) {
            DB::table('accounts')->updateOrInsert(
                ['code' => $code],
                ['name' => $name, 'type' => $type, 'is_group' => $group,
                 'is_cash_bank' => $cashbank, 'currency' => $cur, 'active' => true,
                 'updated_at' => $now, 'created_at' => $now]
            );
        }
        // Second pass: wire parents.
        $ids = DB::table('accounts')->pluck('id', 'code');
        foreach ($rows as [$code, , , , $parent]) {
            if ($parent) {
                DB::table('accounts')->where('code', $code)->update(['parent_id' => $ids[$parent] ?? null]);
            }
        }
    }
}
