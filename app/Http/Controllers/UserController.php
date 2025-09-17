<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Fetch the user id and name, now this function is only using for the User Dropdows
        $users = User::select('id', 'name')->get();
        return response()->json($users);
    }
}
