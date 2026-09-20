<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'incomplete_token' => ['nullable', 'uuid'],
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'address' => ['required', 'string', 'min:3', 'max:500'],
            'delivery_area' => ['required', 'string', 'in:inside_dhaka,outside_dhaka'],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('is_active', true)->where('is_modal_product', false)],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'আপনার নাম লিখুন।',
            'name.min' => 'নাম কমপক্ষে ২ অক্ষরের হতে হবে।',
            'phone.required' => 'আপনার মোবাইল নম্বর লিখুন।',
            'phone.regex' => 'সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন (যেমন: 017XXXXXXXX)।',
            'email.email' => 'সঠিক ইমেইল এড্রেস দিন।',
            'address.required' => 'সম্পূর্ণ ডেলিভারি ঠিকানা লিখুন।',
            'address.min' => 'সঠিক ঠিকানা লিখুন।',
            'delivery_area.required' => 'ডেলিভারি এলাকা নির্বাচন করুন।',
            'delivery_area.in' => 'সঠিক ডেলিভারি এলাকা নির্বাচন করুন।',
            'product_id.required' => 'পণ্য নির্বাচন করুন।',
            'product_id.exists' => 'নির্বাচিত পণ্যটি পাওয়া যায়নি।',
            'quantity.min' => 'সঠিক পরিমাণ দিন।',
            'quantity.max' => 'সঠিক পরিমাণ দিন।',
        ];
    }

    protected function prepareForValidation(): void
    {
        $banglaDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $phone = str_replace($banglaDigits, $englishDigits, (string) $this->input('phone'));
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if (str_starts_with($phone, '88') && strlen($phone) === 13) {
            $phone = substr($phone, 2);
        } elseif (strlen($phone) === 10 && str_starts_with($phone, '1')) {
            $phone = '0'.$phone;
        }

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'phone' => $phone,
            'email' => strtolower(trim((string) $this->input('email'))) ?: null,
            'address' => trim((string) $this->input('address')),
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson() || $this->ajax()) {
            throw new HttpResponseException(response()->json([
                'message' => $validator->errors()->first() ?: 'অনুগ্রহ করে তথ্যগুলো সঠিকভাবে দিন।',
                'errors' => $validator->errors()->messages(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
