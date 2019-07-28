<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\DataUser;
use App\Models\Tag;
use App\Models\Admin;

class DataUserController extends Controller
{

    private $userModel;
    private $dataUserModel;
    private $tagModel;
    private $adminModel;

    public function __construct(User $user,DataUser $dataUser, Tag $tag, Admin $admin)
    {
        $this->userModel = $user;
        $this->dataUserModel = $dataUser;
        $this->tagModel = $tag;
        $this->adminModel = $admin;
    }

    public function upgradeAdmin(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->all(),['email' => 'required',]
            );

            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                $email_value = $req->email;
                $user = $this->userModel->where('email',$email_value)->get()->first();

                if(!empty($user)){
                    $admin = $user->admin()->get()->first();
                    if(empty($admin)){
                        $dataForm = [
                            'user_id'   => $user->id,
                            'token'     => Hash::make($email_value)
                        ];
                        $admin = $user->admin()->create($dataForm);
                        return response()->json(['error' => false,'message' => 'admin_cadastrado_com_sucesso'],Response::HTTP_OK);
                    } else {
                        return response()->json(['error' => false,'message' => 'usuario_ja_e_admin'],Response::HTTP_OK);
                    }
                } else {
                    return response()->json(['error' => 'usuario_nao_encontrado'],Response::HTTP_NOT_FOUND);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function downgradeAdmin(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->all(),['email' => 'required',]
            );

            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                $email_value = $req->email;
                $user = $this->userModel->where('email',$email_value)->get()->first();

                if($user){
                    $admin = $this->adminModel->where('user_id',$user->id)->get()->first();
                    if($admin){
                        $admin->delete();
                        return response()->json(['error' => false,'message' => 'admin_removido'],Response::HTTP_OK);
                    } else {
                        return response()->json(['error' => 'admin_nao_encontrado'],Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json(['error' => 'usuario_nao_encontrado'],Response::HTTP_NOT_FOUND);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
}