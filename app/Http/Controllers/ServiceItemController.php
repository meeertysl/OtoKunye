<?php

namespace App\Http\Controllers;

use App\Models\ServiceItem;
use Illuminate\Http\Request;

class ServiceItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_record_id' => ['required', 'exists:service_records,id'],
            'part_name' => ['required', 'string', 'max:150'],
            'labor_or_part_fee' => ['required', 'numeric', 'min:0'],
        ]);

        return ServiceItem::create($validated);
    }

    public function update(Request $request, ServiceItem $serviceItem)
    {
        $validated = $request->validate([
            'service_record_id' => ['required', 'exists:service_records,id'],
            'part_name' => ['required', 'string', 'max:150'],
            'labor_or_part_fee' => ['required', 'numeric', 'min:0'],
        ]);

        $serviceItem->update($validated);

        return $serviceItem->refresh();
    }
}
