<div class="bg-white rounded-lg shadow px-2 py-2 mb-4 flex flex-wrap gap-1 text-sm">
    @php $tabs = [
        'accounting.dashboard'=>'Dashboard','accounting.vouchers.index'=>'Vouchers','accounting.accounts.index'=>'Chart of Accounts',
        'accounting.reports.trial'=>'Trial Balance','accounting.reports.ledger'=>'Ledger','accounting.reports.daybook'=>'Day Book',
        'accounting.reports.cashbank'=>'Cash/Bank','accounting.reports.pnl'=>'Profit & Loss','accounting.reports.balancesheet'=>'Balance Sheet',
        'accounting.reports.receivables'=>'Receivables','accounting.reports.payables'=>'Payables','accounting.reports.party'=>'Party Ledger','accounting.reports.cashflow'=>'Cash Flow','accounting.reports.tax'=>'Tax/VAT',
    ]; @endphp
    @foreach ($tabs as $route=>$label)
        <a href="{{ route($route) }}" class="px-3 py-1.5 rounded {{ request()->routeIs($route) ? 'bg-[#1F3864] text-white' : 'hover:bg-slate-100 text-slate-600' }}">{{ $label }}</a>
    @endforeach
</div>
