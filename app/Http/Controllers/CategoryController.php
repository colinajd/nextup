<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Category;
use Larapi;
use Validator;
use App\Http\Utils\Helpers;

class CategoryController extends Controller
{
  public function all(Request $request)
  {
    $categories = $this->show($request, 'all');
    $res = $categories->getData()->response;

    // Return $dates array to view
    return view('categories', [
      'single'     => false,
      'categories' => $res
    ]);
  }

  public function single(Request $request, $id)
  {
    $category = $this->show($request, $id);
    $res = $category->getData()->response;

    // Return $dates array to view
    return view('categories', [
      'single'   => true,
      'category' => $res
    ]);
  }

  public function store(Request $request)
  {
    $user = AuthController::getUser($request);
    if ($user == NULL) {
      return Larapi::unauthorized();
    }

    $validator = Validator::make(
      $request->all(),
      Category::$validation_rules,
      Category::$validation_messages
    );

    if ($validator->fails()) {
      return Larapi::badRequest($validator->messages()->toArray());
    }

    // If user isn't an admin
    if ($user->username !== "zlshames" && $user->username !== "cynical89") {
      return Larapi::forbidden();
    }

    // Create Category
    $category = new Category;
    $category->name = $request->name;
    $category->save();

    return Larapi::created();
  }

  public function show(Request $request, $id)
  {
    if ($id == "all") {
      $categories = Category::all();
      $output = array();

      foreach ($categories as $i) {
        array_push($output, $i->name);
      }

      return Larapi::ok($output);
    }

    // Validate ID
    if (!is_numeric($id)) {
      return Larapi::badRequest("The ID must be numeric.");
    }

    $category = Category::find($id);
    if ($category == NULL) {
      return Larapi::notFound("Failed to find category.");
    }

    return Larapi::ok($category);
  }

  // USING POSTMAN, DATA MUST BE SENT AS x-www-form-urlencoded
  public function update(Request $request, $id)
  {
    $user = AuthController::getUser($request);
    if ($user == NULL) {
      return Larapi::unauthorized();
    }

    // Validate ID
    if (!is_numeric($id)) {
      return Larapi::badRequest("The ID must be numeric.");
    }

    // Validate update data
    $validator = Validator::make(
      $request->all(),
      Category::$validation_rules,
      Category::$validation_messages
    );

    if ($validator->fails()) {
      return Larapi::badRequest($validator->messages()->toArray());
    }

    // If user isn't an admin
    if ($user->username !== "zlshames" && $user->username !== "cynical89") {
      return Larapi::forbidden();
    }

    $category = Category::find($id);
    if ($category == NULL) {
      return Larapi::notFound("Failed to find category.");
    }

    $category->name = $request->name;
    $category->save();

    return Larapi::accepted();
  }

  public function destroy(Request $request, $id)
  {
    $user = AuthController::getUser($request);
    if ($user == NULL) {
      return Larapi::unauthorized();
    }

    // Validate ID
    if (!is_numeric($id)) {
      return Larapi::badRequest("The ID must be numeric.");
    }

    // If user isn't an admin
    if ($user->username !== "zlshames" && $user->username !== "cynical89") {
      return Larapi::forbidden();
    }

    $category = Category::find($id);
    if ($category == NULL) {
      return Larapi::notFound("Failed to find category.");
    }

    $category->delete();

    return Larapi::ok();
  }
}
