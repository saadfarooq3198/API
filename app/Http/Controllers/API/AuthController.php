<?php

namespace App\Http\Controllers\API;
use App\Models\User;
use App\Models\Product;
use App\Mail\WelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController as BaseController;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $auth = Auth::user(); 
            $success['token'] =  $auth->createToken('LaravelSanctumAuth')->plainTextToken; 
            $success['name'] =  $auth->name;
            return $this->handleResponse($success, 'User logged-in!');
        } 
        else{ 
            return $this->handleError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);
   
        if($validator->fails()){
            return $this->handleError($validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('LaravelSanctumAuth')->plainTextToken;
        $success['name'] =  $user->name;
        Mail::to($request->email)->send(new WelcomeMail($user));
        return $this->handleResponse($success, 'User successfully registered!');
    }
    public function product(Request $request){
        $orderby = $request->orderby;
        $searchby = $request->search;
        $products=ProductResource::collection(Product::where('name','LIKE','%'.$searchby.'%')->orderBy('price',$orderby)->get());
        $response = [
            'success' => true,
            'message' => 'This is Products List',
            'data'    => $products,
        ];
        return response()->json($response, 200);
    }
}