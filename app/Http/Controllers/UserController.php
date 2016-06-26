<?php

namespace App\Http\Controllers;

use Auth;
use Response;
use View;
use Illuminate\Http\Request;

use App\Http\Requests;

class UserController extends Controller
{
    public function profile()
    {
        $data = [
            'user'=>Auth::user()
        ];
        return View::make('users.profile', $data);
    }

    public function avatar()
    {
        return Response::json([
            'status' => 'success',
            'imgsrc' => env('AVATAR_URL') . '/' . Auth::user()->avatar
        ], 200);
    }
}
