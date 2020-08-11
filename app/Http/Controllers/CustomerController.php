<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index()
    {
        $data['customers'] = User::all();
        $data['nav_item'] = 'customers';

        return view('customers', $data);
    }

    public function addCustomer(Request $request)
    {
        $rules = [
            'name' => 'required|max:100',
            'email' => 'required|email|max:150',
            'password' => [
                'required',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) {
                    if (!$this->validPassword($value)) {
                        $fail('Password must contain letters and numbers.');
                    }
                }
            ],
            'password_confirmation' => 'required'
        ];

        $messages = [

        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['error' => implode('<br>', $validator->errors()->all())]);
            } else {
                return redirect()->route('customers')
                    ->withErrors($validator)
                    ->withInput($request->all());
            }
        }

        /*$request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|max:150',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required'
        ]);*/

        $customer = new User(
            [
                'name' => $request->post('name'),
                'email' => $request->post('email'),
                'password' => Hash::make($request->post('password'))
            ]
        );

        if ($customer->save()) {
            $successMsg = 'Customer created successfully.';

            if ($request->ajax()) {
                return response()->json(['success' => $successMsg]);
            } else {
                return redirect()->route('customers')->with('success', $successMsg);
            }
        } else {
            $errorMsg = 'Failed to create customer, please try again.';

            if ($request->ajax()) {
                return response()->json(['error' => $errorMsg]);
            } else {
                return redirect()->route('customers')->with('error', $errorMsg);
            }
        }
    }

    private function validPassword($password)
    {
        return preg_match('/[a-zA-Z]/', $password) && preg_match('/[0-9]/', $password);
    }
}
