<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Domain\Sales\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderTrackController extends Controller
{
    public function lookup()
    {
        return view('storefront.track-lookup');
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|string|max:40',
            'phone'  => 'required|string|max:20',
        ]);

        $number = strtoupper(trim($data['number']));
        $phone  = preg_replace('/\D/', '', $data['phone']);

        $order = Order::query()
            ->where('number', $number)
            ->with('customer')
            ->first();

        if (! $order) {
            return back()->withInput()->with('error', 'لم نجد طلبًا بهذا الرقم. تأكد من رقم الطلب وحاول مجددًا.');
        }

        $orderPhone = preg_replace(
            '/\D/',
            '',
            (string) ($order->shipping_address['phone'] ?? $order->customer?->phone ?? '')
        );

        $matches = $orderPhone !== ''
            && (str_ends_with($orderPhone, substr($phone, -10)) || $orderPhone === $phone);

        if (! $matches) {
            return back()->withInput()->with('error', 'رقم الهاتف لا يطابق بيانات الطلب.');
        }

        return redirect()->route('storefront.track', $order->number);
    }

    public function show(string $number)
    {
        $order = Order::where('number', $number)
            ->with(['lines', 'statusHistory', 'customer'])
            ->firstOrFail();

        return view('storefront.track', compact('order'));
    }
}
