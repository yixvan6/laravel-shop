<?php

namespace App\Http\Requests;

class AddressRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'province'      => 'required|string',
            'city'          => 'required|string',
            'district'      => 'required|string',
            'address'       => 'required|string',
            'zip'           => 'required|integer',
            'contact_name'  => 'required|string',
            'contact_phone' => ['required', 'regex:/^1[3-9]\d{9}$/'],
        ];
    }

    public function attributes()
    {
        return [
            'province' => '省',
            'district' => '地区',
            'address' => '详细地址',
            'zip' => '邮编',
            'contact_name' => '联系人',
            'contact_phone' => '联系电话',
        ];
    }
}
