<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //returns all users with their orders
    public function index() {
        return response()->json(User::with('orders')->get());
    }

    //Authenticates a user and generates an access token for that user.
    //The createToken method is one of the methods Laravel Passport adds to the user model.
    public function login(Request $request) {
        $status = 401;
        $response = ['error' => 'Unauthorised'];

        if(Auth::attempt($request->only('email', 'password'))) {
            $status = 200;
            $response = [
                'user' => Auth::user(),
                'token' => Auth::user()->create('bigstore')->accessToken,
            ];
        }

        return response()->json($response, $status);
    }

    //Creates a user account, authenticates it and generates an access token for it.
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
           'name' => 'required|max:50',
           'email' => 'required|email',
           'password' => 'required|min:6',
           'c_password' =>  'required|same:password'
        ]);

        if($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $data = $request->only('name', 'email', 'password');
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);
        $user->is_admin = 0;

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('bigStore')->accessToken,
        ]);
    }

    //Gets the details of a user and returns them.
    public function show(User $user) {
        return response()->json($user);
    }

    //Gets all the orders of a user and returns them.
    public function showOrders(User $user) {
        return response()->json($user->orders()->with(['product'])->get());
    }
}
