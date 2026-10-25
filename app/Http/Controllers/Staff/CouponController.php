<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->paginate(15);

        return view('staff.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('staff.coupons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:coupons,code'],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['boolean'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->has('is_active');

        $coupon = Coupon::create($data);

        AuditLog::record('coupon.created', $coupon, [
            'code' => $coupon->code,
            'value' => $coupon->value,
            'type' => $coupon->type,
        ]);

        return redirect()->route('staff.coupons.index')
            ->with('success', "Cupón {$coupon->code} creado con éxito.");
    }

    public function edit(Coupon $coupon)
    {
        return view('staff.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:coupons,code,'.$coupon->id],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['boolean'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->has('is_active');

        $coupon->update($data);

        AuditLog::record('coupon.updated', $coupon, [
            'code' => $coupon->code,
            'value' => $coupon->value,
            'type' => $coupon->type,
        ]);

        return redirect()->route('staff.coupons.index')
            ->with('success', "Cupón {$coupon->code} actualizado.");
    }

    public function destroy(Coupon $coupon)
    {
        $code = $coupon->code;
        $coupon->delete();

        AuditLog::record('coupon.deleted', null, ['code' => $code]);

        return redirect()->route('staff.coupons.index')
            ->with('success', "Cupón {$code} eliminado.");
    }
}
