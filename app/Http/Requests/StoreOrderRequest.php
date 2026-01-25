<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ordertype_id' => 'required|exists:ordertypes,id',
            'paymenttype_id' => 'required|exists:paymenttypes,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'address_id' => 'nullable|exists:addresses,id',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'delivery_fee' => 'required|numeric|min:0',
            'service_fee' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'transaction_id' => 'nullable|string',
            'order_note' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
        ];
    }
}
