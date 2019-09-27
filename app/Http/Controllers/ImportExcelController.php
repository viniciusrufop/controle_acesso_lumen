<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Models\ImportExcel;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\DataUser;

class ImportExcelController extends Controller
{
    private $userModel;
    private $dataUserModel;

    public function __construct(User $user, DataUser $dataUser)
    {
        $this->userModel = $user;
        $this->dataUserModel = $dataUser;
    }


    public function importExcelUser(Request $request)
    {
        try {
            $dadosExcel = (new ImportExcel())->toArray($request->file('arquivo'));
            $header = $dadosExcel[0][0];

            if(!$this->headerValidation($header)) { throw new \Exception("Dados de entrada incorretos."); }

            $usuarios = [];
            foreach ($dadosExcel[0] as $key => $value) {
                $nome = $value[0]; $email = $value[1]; $login = $value[2]; $senha = $value[3];
                if ($key > 0 && !is_null($nome) && !is_null($email) && !is_null($login) && !is_null($senha)) {
                    $nomeSobrenome = explode(" ", $nome, 2);
                    $tmpArray = [
                        'nome'          => $nomeSobrenome[0],
                        'sobrenome'     => (isset($nomeSobrenome[1])) ? $nomeSobrenome[1] : '',
                        'email'         => $value[1],
                        'login'         => $value[2],
                        'senha'         => $value[3],
                        'admin'         => $this->getAdmin($value[4]),
                        'telefone'      => $value[5],
                        'logradouro'    => $value[6],
                        'complemento'   => $value[7],
                        'bairro'        => $value[8],
                        'cidade'        => $value[9],
                        'estado'        => $value[10],
                        'cep'           => $value[11],
                        'ativo'         => 1,
                    ];
                    if ($this->trataUser($tmpArray)) { array_push($usuarios, $tmpArray); }
                }
            }

            if (count($usuarios) <= 0) { throw new \Exception("Tabela sem dados para cadastrar."); }

            $result = [];
            foreach ($usuarios as $usuario) {
                array_push($result, $this->insertNewUser($usuario));
            }            

            return response()->json(['Result' => $result, 'success' => true],Response::HTTP_OK);
        } catch(\Exception $e){
            return response()->json(['message' => $e->getMessage(), 'success' => false],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }

    private function insertNewUser($dataForm)
    {
        try{
            $dataForm['password'] = Hash::make($dataForm['login'] . $dataForm['senha']);
            $user = $this->userModel->create($dataForm);
            $user->dataUser()->create($dataForm);
            if ($dataForm['admin']) {
                $dataForm = [
                    'user_id'   => $user->id,
                    'token'     => Hash::make($dataForm['email'])
                ];
                $user->admin()->create($dataForm);
            }
            return ['success' => true];
        } catch(QueryException $e){
            return [
                'nome'      => $dataForm['nome'],
                'email'     => $dataForm['email'],
                'message'   => $e, 
                'success'   => false
            ];
        }
    }

    private function  headerValidation($header)
    {
        if ($header[0] == 'Nome' && $header[1] == 'Email' && $header[2] == 'Login' && $header[3] == 'Senha' && $header[4] == 'Administrador') {
            return true;
        }
        return false;
    }

    private function trataUser($usuario)
    {
        if (is_numeric($usuario['login']) && (strlen($usuario['login']) <= 4) &&
            is_numeric($usuario['senha']) && (strlen($usuario['senha']) <= 4)) { 
                return true; 
            }
        return false;
    }

    private function getAdmin($value)
    {
        switch ($value) {
            case 'Sim': case 'sim': case 'SIM': case '1':
                return 1;
            default:
                return 0;
        }
    }
}
