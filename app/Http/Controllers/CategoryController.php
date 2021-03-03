<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(){
        $categories = Category::all();

        if(empty($categories)){
            $data = array(
                'message' => 'No hay registros'
            );
        }else{
            $data = array(
                'categories' => $categories
            );
        }

        return response()->json($data);
    }



    public function show($id){
        $category = Category::find($id);

        if(empty($category)){
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'La categoria no existe'
            );
        }else{
            $data = array(
                'status' => 'success',
                'code' => 200,
                'category' => $category
            );
        }

        return response()->json($data, $data['code']);
    }



    public function store(Request $request){
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $validate = Validator::make($params_array, [
            'name' => 'required|unique:categories'
        ]);

        if($validate->fails()){
            $data = array(
                'status' => 'error',
                'code' => 400,
                'errors' => $validate->errors()
            );
        }else{
            $category = new Category;
            $category->name = $params_array['name'];
            $category->save();

            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'La categoria se ha creado con exito',
                'category' => $category
            );
        }

        return response()->json($data, $data['code']);
    }



    public function update($id, Request $request){
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if(!empty($json)){
            unset($params_array['created_at']);
            unset($params_array['id']);

            $validate = Validator::make($params_array, [
                'name' => 'required'
            ]);

            if($validate->fails()){
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'errors' => $validate->errors()
                );
            }else{
                Category::where('id', $id)->update($params_array);

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La categoria se ha creado con exito',
                    'category' => $params_array
                );
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No hay datos'
            );
        }

        return response()->json($data, $data['code']);
    }
}
