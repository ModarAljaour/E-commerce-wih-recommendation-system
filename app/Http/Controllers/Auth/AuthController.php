<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use GeneralTrait;

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $token = $user->createToken('AdminToken')->plainTextToken;
            return response()->json(['token' => $token], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function CustomerLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $customer = Customer::where('email', $credentials['email'])->first();

        if ($customer && Hash::check($credentials['password'], $customer->password)) {
            $token = $customer->createToken('CustomerToken')->plainTextToken;
            return response()->json(['token' => $token], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function register(Request $request)  // for admin
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('YourAppName')->plainTextToken;

        return response()->json([
            'token' => $token,
            'name' => $request->name,
            'email' => $request->email,
        ]);
    }

    public function customerRegister(Request $request)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:4',
            'phone' => 'required',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'فشل التحقق من البيانات',
                'errors' => $validator->errors()->messages(),
                'error_type' => 'validation_error'
            ], 422);
        }

        try {
            $customerData = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->input('password')),
            ];

            $customer = Customer::create($customerData);

            $token = $customer->createToken('CustomerAppToken')->plainTextToken;

            $data = [
                'token' => $token,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ]
            ];

            return $this->apiResponse($data);
        } catch (\Illuminate\Database\QueryException $ex) {

            $errorCode = $ex->errorInfo[1] ?? null;

            $message = 'حدث خطأ في قاعدة البيانات';
            if ($errorCode == 1062) {
                $message = 'البريد الإلكتروني مسجل مسبقاً';
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => $errorCode,
                'error_type' => 'database_error'
            ], 409);
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع',
                'error_details' => config('app.debug') ? $ex->getMessage() : null,
                'error_type' => 'server_error'
            ], 500);
        }
    }




    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
