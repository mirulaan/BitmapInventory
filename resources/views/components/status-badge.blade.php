@props(['status'])

@php
    $classes = match($status) {
        'in_stock' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'on_loan' => 'bg-blue-50 text-blue-700 ring-blue-700/10',
        'sold' => 'bg-slate-100 text-slate-700 ring-slate-500/10',
        'reserved' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
        'rma_defective' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'decommissioned' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'in_transit' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
        default => 'bg-gray-50 text-gray-600 ring-gray-500/10',
    };

    $labels = [
        'in_stock' => 'In Stock',
        'on_loan' => 'On Loan',
        'sold' => 'Sold',
        'reserved' => 'Reserved',
        'rma_defective' => 'RMA / Repair',
        'decommissioned' => 'Decommissioned',
        'in_transit' => 'In Transit',
    ];
@endphp

<span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $classes }}">
    {{ $labels[$status] ?? ucfirst(str_replace('_', ' ', (string)$status)) }}
</span>
