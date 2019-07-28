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
use App\Models\History;

class ExampleController extends Controller
{

    private $userModel;
    private $dataUserModel;
    private $tagModel;
    private $historyModel;

    public function __construct(User $user,DataUser $dataUser, Tag $tag, History $history)
    {
        $this->userModel = $user;
        $this->dataUserModel = $dataUser;
        $this->tagModel = $tag;
        $this->historyModel = $history;
    }

    public function testeGet()
    {
        return response()->json('ok', Response::HTTP_OK);
    }

    public function testePost(Request $req)
    {
        // dd($req);
        return response()->json($req->all(),Response::HTTP_OK);
    }

    public function getDataUser()
    {
        $user = $this->userModel->find(1);
        $dataUser = $user->dataUser()->get()->first();
        echo "<pre>";
        echo $user . "<br>";
        echo $dataUser;
    }

    public function getUserByData()
    {
        $dataUser = $this->dataUserModel->find(1);
        $user = $dataUser->user()->get()->first();
        echo "<pre>";
        echo $dataUser . "<br>";
        echo $user;
    }

    public function insertUser()
    {
        $dataForm = [
            'email' => 'vinicius@rocha.com.br',
            'nome'  => 'Teste create',
            'login' => '5431',
            'senha' => '8795',
            'ativo' => 0
        ];

        $dataForm['password'] = Hash::make($dataForm['senha']);

        $user = $this->userModel->create($dataForm);
        $dataUser = $user->dataUser()->create($dataForm);

        echo "<pre>";
        echo $user . "<br>";
        echo $dataUser;

    }

    public function getTagByDataUser()
    {
        $dataUser = $this->dataUserModel->find(1);
        $tags = $dataUser->tags()->get();

        echo "<pre>";
        echo $dataUser->nome . "<br>";
        foreach($tags as $tag){
            echo $tag->tag_value . ', ';
        }
    }

    public function getDataUserByTag()
    {
        $tag = $this->tagModel->where('tag_value','abcd1234')->get()->first();
        $dataUser = $tag->dataUser()->get()->first();

        echo "<pre>";
        echo $dataUser->nome . "<br>";
    }

    public function insertTag()
    {
        $dataForm = [
            'tag_value' => 'vini1234',
            'ativo'     => 0
        ];

        $tag = $this->tagModel->create($dataForm);
        echo "<pre>";
        echo $tag;
    }

    public function getTagsWhithoutUse()
    {
        // $tag = $this->tagModel->where('data_user_id',null)->get();
        $tag = $this->tagModel->whereNull('data_user_id')->get();
        echo $tag;
    }

    public function getTagTrue()
    {
        $tag = $this->tagModel
                        ->where('tag_value','vini1234')
                        ->where('ativo',1)
                        ->whereNotNull('data_user_id')
                        ->get();

        echo $tag . "<br>";
        if(empty($tag[0])) echo "Não Autenticado";
        else echo "Autenticado";
    }

    public function changeTagTrue()
    {
        $tag = $this->tagModel
                    ->where('tag_value','vini1234')
                    ->update(['ativo' => 1]);

        echo $tag . "<br>";
    }

    public function getTokenUser()
    {
        $user = $this->userModel->find(1);
        $token = $user->admin()->get()->first();
        dd($token->token);
    }

    public function generateToken()
    {
        $email = $this->userModel->find(1)->email;
        $new_token = Hash::make($email);
        dd($new_token);
    }

    public function upgradeAdmin()
    {
        $dataForm = [
            'email' => '1'
        ];

        $user = $this->userModel->where('email',$dataForm['email'])->get()->first();
        $dataForm['token'] = Hash::make($dataForm['email']);
        $admin = $user->admin()->create($dataForm);
        dd($admin);

    }

    public function downgradeAdmin()
    {
        $dataForm = [
            'email' => 'email0@gmail.com'
        ];

        $user = $this->userModel->where('email',$dataForm['email'])->get()->first();
        $admin = $user->admin()->delete();
        dd($admin);
    }

    private function myAuthToken(array $token)
    {
        $validator = Validator::make(
            $token,['token'     => 'required | max:60 | min:60']
        );
        return ($validator->fails()) ? true : false;
    }

    public function testeAuthToken()
    {
        $dataForm = ['token' => '$2y$10$97pTtcwYtUvnBo/nRYPEuOBNOFiEjj30OCuw/71a4j.ENXzk6I8Vu'];

        if($this->myAuthToken($dataForm)){
            echo "nao autenticado";
        } else {
            echo "autenticado";
        }
    }

    public function insertHistory()
    {
        $dataForm = [
            'data_user_id' => '1',
            'tag_value' => 'invi1234',
            'data'  => date('Y-m-d'),
            'hora'  => date('H:i:s')
        ];

        $history = $this->historyModel->create($dataForm);

        echo "<pre>";
        echo $history . "<br>";
    }

}