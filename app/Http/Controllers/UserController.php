<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response; /* https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/HttpFoundation/Response.php */

class UserController extends Controller
{
    public function login()
    {
        $objReturn = [
            [ 'id' => 1 , 'nome' => 'java'],
            [ 'id' => 2 , 'nome' => 'javascript'],
        ];
        return response()->json($objReturn, Response::HTTP_OK);
    }
}
