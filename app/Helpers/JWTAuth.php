<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use DomainException;
use UnexpectedValueException;

use function PHPUnit\Framework\isEmpty;

class JWTAuth{
    public $key;

    public function __construct()
    {
        $this->key = 'clave';
    }



    public function signup(string $email, string $password, $get_token = null){
        // Buscar si existe el usuario con las credenciales
        $user = User::where([
            'email' => $email
        ])->first();

        // Comprobar si son correctas
        $signup = false;
        $pwd = password_verify($password, $user->password);

        if(is_object($user) && $pwd){
            $signup = true;
        }

        // Generar el Token con los datos del usuario
        if($signup){
            $token = array(
                "sub" => $user->id,
                "name" => $user->name,
                "surname" => $user->surname,
                "emai" => $user->email,
                "iat" => time(),
                "exp" => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');

            if(!is_null($get_token)){
                $decoded = JWT::decode($jwt, $this->key, [ 'HS256' ]);

                $data = array(
                    "status" => "succes",
                    "code" => 200,
                    "user" => $decoded
                );
            }else{
                $data = array(
                    "status" => "succes",
                    "code" => 200,
                    "user_token" => $jwt
                );
            }

        }else{
            $data = array(
                "status" => "error",
                "code" => 404,
                "message" => "Usuario no encontrado"
            );
        }

        // Devolver los datos decodificados o el Token en funcion de un parametro
        return json_encode($data);
    }



    public function check_token($jwt, $identity = false){
        $auth = false;

        try{
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, [ 'HS256' ]);
        }catch(UnexpectedValueException $e){
            $auth = false;
        }catch(DomainException $e){
            $auth = false;
        }

        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        if($identity){
            return $decoded;
        }

        return $auth;
    }
}