<?php

namespace App\Http\Controllers;

use Carbon;
use App\Helpers\JWTAuth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;

class UserController extends Controller
{
    public function register(Request $request){
        // Recoger los datos
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if(!empty($params) && !empty($params_array)){
            // Limpiar los datos
            $params_array = array_map('trim', $params_array);

            // Validar datos
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ]);

            if($validate->fails()){
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El usuario NO se ha creado',
                    'errors' => $validate->errors()
                );
            }else{
                // Cifrar la contraseña
                $password = password_hash($params->password, PASSWORD_BCRYPT, [ 'cost' => 4 ]);

                // Crear el usuario
                $user = new User;

                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $password;
                $user->role = $params_array['role'] == 'admin' ? $params_array['role'] : 'user';

                $user->save();

                $data = array(
                    'status' => 'succes',
                    'code' => 201,
                    'message' => 'El usuario se ha creado correctamente',
                    'user' => $user
                );
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Los datos enviados no son correctos'
            );
        }

        return response()->json($data, $data['code']);
    }



    public function login(Request $request){
        $jwt = new JWTAuth();

        // Recibir los datos
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        $email = $params->email;
        $password = $params->password;

        // Validar los datos
        $validate = Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validate->fails()){
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Hay campos incorrectos',
                'errors' => $validate->errors()
            );
        }else{
            if(!empty($params->gettoken)){
                $data = json_decode($jwt->signup($email, $password, true), true);

            }else{
                $data = json_decode($jwt->signup($email, $password), true);
            }
        }

        // Devolver los datos o token
        return response()->json($data, $data['code']);
    }



    public function update(Request $request){
        $token = $request->header('Authorization');

        $jwt = new JWTAuth;
        $check_token = $jwt->check_token($token);

        // Recoger los datos
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if($check_token && !empty($params_array)){
            // Sacar el usuario identificado
            $user_token = $jwt->check_token($token, true);


            // Validar los datos
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|regex:/^[\pL\s\-]+$/u',
                'email' => 'required|email|unique:users,email,'.$user_token->sub
            ]);

            // Limpiar datos
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);
            unset($params_array['updated_at']);

            if($validate->fails()){
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'errors' => $validate->errors()
                );
            }else{
                // Actualizar el usuario
                User::where('id', $user_token->sub)->update($params_array);


                $user = User::findOrFail($user_token->sub);

                $data = array(
                    'status' => 'succes',
                    'code' => 200,
                    'message' => 'El usuario se ha actualizado correctamente',
                    'user' => $user,
                    'changes' => $params_array
                );
            }

        }else{
            $data = array(
                'status' => 'Error',
                'code' => 401,
                'message' => 'El usuario no esta identificado correctamente'
            );
        }

        return response()->json($data, $data['code']);
    }



    public function upload(Request $request){
        // Recoger los datos de la peticion
        $image = $request->file('file0');

        // Validar imagen
        $validate = Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar la imagen
        if(!$image || $validate->fails()){
            $data = array(
                'status' => 'Error',
                'code' => 400,
                'message' => 'No se ha podido subir la imagen',
                'errors' => $validate->errors()
            );

        }else{
            $image_name = time().$image->getClientOriginalName();
            Storage::disk('users')->put($image_name, File::get($image));

            $data = array(
                'status' => 'Succes',
                'code' => 200,
                'message' => 'La imagen se ha subido correctamente',
                'image' => $image_name
            );
        }

        return response()->json($data, $data['code']);
    }



    public function getImage($filename){
        $isset = Storage::disk('users')->exists($filename);
        if($isset){
            $file = Storage::disk('users')->get($filename);
            return new \Symfony\Component\HttpFoundation\Response($file, 200);
        }else{
            return response()->json(array(
                'status' => 'error',
                'code' => 200,
                'message' => 'La imagen no existe'
            ), 404);
        }
    }



    public function detail($id){
        $user = User::find($id);

        if(is_object($user)){
            $data = array(
                'status' => 'Success',
                'code' => 200,
                'user' => $user
            );

        }else{
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'No se ha encontrado al usuario'
            );
        }

        return response()->json($data, $data['code']);
    }
}
