<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use Larapi;
use Validator;

class AuthController extends Controller
{
  public function login()
  {
    // Return $dates array to view
    return view('auth/login');
  }

  public function register()
  {
    // Return $dates array to view
    return view('auth/register');
  }

  public function signup(Request $request)
  {
    // Check if user is already logged in
    $user = $this->getUser($request);
    if ($user !== NULL) {
      return Larapi::forbidden('You are already registered');
    }

    $validator = Validator::make(
      $request->all(),
      User::$validation_rules,
      User::$validation_messages
    );

    if ($validator->fails()) {
      return Larapi::badRequest($validator->messages()->toArray());
    }

    // Create new user
    $user = new User;

    $user->username = $request->username;
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->api_token = str_random(60);

    // Save user
    $user->save();

    // Return created success
    return Larapi::created();
  }

  public function signin(Request $request)
  {
    // Check if user account exists
    $user = User::where('email', $request->email)->first();
    if ($user == NULL) {
      return Larapi::badRequest("User does not exist");
    }

    // Check if passwords match
    if (Hash::check($request->password, $user->password)) {
      // Return api_token in JSON(?)
      $data = [
        'username'  => $user->username,
        'email'     => $user->email,
        'api_token' => $user->api_token
      ];
      return Larapi::ok($data);
    }

    return Larapi::badRequest('Password is incorrect');
  }

  public function signout(Request $request)
  {
    // Logout will be handled by front-end (delete api_token)
    return redirect('/');
  }

  public static function getUser(Request $request)
  {
    $token = $request->header('Authorization');
    if ($token == NULL) {
      return NULL;
    }

    $user = User::where('api_token', $token)->first();
    if ($user == NULL) {
      return NULL;
    }

    return $user;
  }

  public function isAuthenticated(Request $request)
  {
    $token = $request->header('Authorization');
    if ($token == NULL) {
      return Larapi::badRequest("User is not authenticated");
    }

    $user = User::where('api_token', $token)->first();
    if ($user == NULL) {
      return Larapi::badRequest("User is not authenticated");
    }

    return Larapi::ok("User is authenticated");
  }
}
