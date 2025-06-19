<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class ProfileController extends Controller
{
    public function index(){
        $user = User::where('id',6)->first();
        return view("pages.profile",compact('user'));
    }
}
