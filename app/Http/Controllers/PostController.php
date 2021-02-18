<?php

namespace App\Http\Controllers;

use App\Helpers\JWTAuth;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    public function index(){
        $posts = Post::all()->load(['category', 'user']);

        if(empty($posts)){
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No hay posts guardados'
            );
        }else{
            $data = array(
                'status' => 'succcess',
                'code' => 200,
                'posts' => $posts
            );
        }

        return response()->json($data, $data['code']);
    }



    public function show($id){
        $post = Post::find($id);

        if(empty($post)){
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No existe el post'
            );
        }else{
            $data = array(
                'status' => 'succcess',
                'code' => 200,
                'post' => $post->load(['category', 'user'])
            );
        }

        return response()->json($data, $data['code']);
    }



    public function store(Request $request){
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if(empty($params_array) || empty($json)){
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No hay datos'
            );
        }else{
            $jwtAuth = new JWTAuth;
            $token = $request->header('Authorization', null);
            $user = $jwtAuth->check_token($token, true);

            $validate = Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required|numeric'
            ]);

            if($validate->fails()){
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'errors' => $validate->errors()
                );
            }else{
                $post = new Post;
                $post->title = $params_array['title'];
                $post->content = $params_array['content'];
                $post->image = $params_array['image'];
                $post->user_id = $user->sub;
                $post->category_id = $params_array['category_id'];
                $post->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Se ha creado el Post correctamente',
                    'post' => $post
                );
            }
        }

        return response()->json($data, $data['code']);
    }



    public function update($id, Request $request){
        $user = $this->getIdentity($request->header('Authorization', null));
        $post = Post::find($id);

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if(empty($json) || empty($params_array)){
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No hay datos'
            );
        }else{
            if($user->sub == $post->user_id){
                $validate = Validator::make($params_array, [
                    'title' => 'required',
                    'content' => 'required',
                    'category_id' => 'required|numeric'
                ]);

                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['user']);

                if($validate->fails()){
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'errors' => $validate->errors()
                    );
                }else{
                    Post::where('id', $id)->update($params_array);

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Se ha creado el Post correctamente',
                        'post' => Post::find($id)
                    );
                }
            }else{
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'errors' => 'No puedes editar un post que no es tuyo'
                );
            }
        }
        return response()->json($data, $data['code']);
    }



    public function destroy($id, Request $request){
        $user = $this->getIdentity($request->header('Authorization', null));
        $post = Post::find($id);

        if(empty($post)){
            $data = array(
                'status' => 'error',
                'code' => 404,
                'errors' => 'No existe el post'
            );
        }else{
            if($user->sub == $post->user_id){
                $post->delete();
    
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El post se ha eliminado correctamente',
                    'post' => $post
                );
            }else{
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'errors' => 'No puedes eliminar un post que no es tuyo'
                );
            }
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
            Storage::disk('images')->put($image_name, File::get($image));

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
        $isset = Storage::disk('images')->exists($filename);

        if($isset){
            $file = Storage::disk('images')->get($filename);

            $data = array(
                'image' => base64_encode($file),
                'code' => 200
            );
        }else{
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'No se ha encontrado la imagen'
            );
        }

        return response()->json($data, $data['code']);
    }



    public function getPostsByCategory($id){
        $posts = Post::where('category_id', $id)->get();

        if(empty($posts)){
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No hay posts guardados'
            );
        }else{
            $data = array(
                'status' => 'succcess',
                'code' => 200,
                'posts' => $posts->load(['user', 'category'])
            );
        }

        return response()->json($data, $data['code']);
    }



    public function getPostsByUser($id){
        $posts = Post::where('user_id', $id)->get();

        if(empty($posts)){
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No hay posts guardados'
            );
        }else{
            $data = array(
                'status' => 'succcess',
                'code' => 200,
                'posts' => $posts->load(['user', 'category'])
            );
        }

        return response()->json($data, $data['code']);
    }



    public function getIdentity($request){
        $jwtAuth = new JWTAuth;
        $token = $request->header('Authorization', null);

        return $jwtAuth->check_token($token, true);

    }
}
