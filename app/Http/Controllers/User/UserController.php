<?php

namespace App\Http\Controllers\User;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        $json = [
            'data'=>$users
            ];
        return response()->json($json,200);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules =[
                'name'=>'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6|confirmed'
            ];

        $this->validate($request,$rules);

        $fields = $request -> all();
        $fields['password'] = bcrypt($request->password);
        $fields['verified'] = User::USUARIO_NO_VERIFICADO;
        $fields['verification_token'] = User::generarVerificationToken();
        $fields['admin'] = User::USUARIO_REGULAR;

        $user = User::create($fields);

        return response()->json(['data'=>$user],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $json = [
            'data'=>$user
            ];
        return response()->json($json,200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $rules =[
                'email' => 'email|unique:users,email,' . $user->id,
                'password' => 'min:6|confirmed',
                'admin' => 'in:' . User::USUARIO_ADMINISTRADOR . ',' . User::USUARIO_REGULAR,
            ];

        $this->validate($request,$rules);

        if($request->has('name')){
            $user->name = $request->name;
        }

        if($request->has('email') && $user->email != $request->email){
            $user ->verified = User::USUARIO_NO_VERIFICADO;
            $user -> verification_token = User::generarVerificationToken();
            $user -> email = $request->email;
        }

        if($request -> has('pasword')){
            $user -> password = bcrypt($request->password);
        }

        if($request->has('admin')){
            if(!$user->esVerificado()){
                $json =[
                    'error'=> 'Only verified Users can become admin'
                    ];
                return response()->json($json,409);
            }

            $user->admin = $request->admin;
        }

        if(!$user -> isDirty()){
            $json = [
                'error' => 'At least one value has to be modified for updating the user'
                ];
            return response()->json($json,422);
        }

        $user->save();
        return response()->json(['data'=> $user],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['data'=>$user],200);
    }
}
