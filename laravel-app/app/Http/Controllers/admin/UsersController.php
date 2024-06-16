<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\BackendServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        try {
            $responseUser = [];

            if ($request->has('query')) {
                $param = trim($request->get('query'), ' ');

                if (! empty($param)) {
                    $responseDepartment =
                        Http::withHeaders([
                            'Authorization' => 'Bearer '.$request->cookie('auth_token'),
                            'Accept' => 'application/json',
                        ])->get(BackendServer::url().'/api/user/search/'.$request->get('query'));
                }
            } else {
                $responseUser =
                    Http::withHeaders([
                        'Authorization' => 'Bearer '.$request->cookie('auth_token'),
                        'Accept' => 'applications/json',
                    ])->get(BackendServer::url().'/api/users');
            }

            $responseDepartment =
                Http::withHeaders([
                    'Authorization' => 'Bearer '.$request->cookie('auth_token'),
                    'Accept' => 'applications/json',
                ])->get(BackendServer::url().'/api/departments');

            if ($responseDepartment->serverError() || $responseUser->serverError()) {
                return abort(500);
            }

            if ($responseDepartment->successful() && $responseUser->successful()) {

                $paginatedUsers =
                    $this->paginate($responseUser['data'] ?? []);

                return view('admin.users', [
                    'users' => $paginatedUsers,
                    'departments' => $responseDepartment['data'] ?? [],
                ]);
            } elseif ($responseDepartment->unauthorized() || $responseUser->unauthorized()) {
                return redirect()->intended(route('admin.login'));
            }

            return abort($responseUser->status());
        } catch (\Exception $e) {
            if ($e instanceof HttpException) {
                throw new HttpException($responseUser->status());
            }

            return abort(500);
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required'],
            'email' => ['required', 'email'],
            'phone_number' => ['required', 'max:13', 'regex:/^\d+$/'],
            'date_of_birth' => ['required', 'date'],
            'address' => ['required'],
            'nik' => ['required', 'digits:16'],
            'gender' => ['required', 'in:Male,Female'],
            'department_id' => ['required', 'integer'],
            'photo.*' => ['required', 'mimes:png,jpg,jpeg'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors([
                'create-error' => $validator->errors()->first(),
            ])
                ->withInput([
                    'full_name' => $request['full_name'],
                    'email' => $request['email'],
                    'date_of_birth' => $request['date_of_birth'],
                    'phone_number' => $request['phone_number'],
                    'address' => $request['address'],
                    'nik' => $request['nik'],
                    'gender' => $request['gender'],
                    'department_id' => $request['department_id'],
                    'photo' => $request['photo'],
                ]);
        }

        try {
            $photo = $request->file('photo');
            $fileName = time().'_user_'.
                $request['full_name'].'.png';

            $response =
                Http::withHeaders([
                    'Authorization' => 'Bearer '.$request->cookie('auth_token'),
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post(BackendServer::url().'/api/user/create', [
                    'full_name' => $request['full_name'],
                    'email' => $request['email'],
                    'date_of_birth' => $request['date_of_birth'],
                    'phone_number' => $request['phone_number'],
                    'address' => $request['address'],
                    'nik' => $request['nik'],
                    'gender' => $request['gender'],
                    'department_id' => intval($request['department_id']),
                    'photo' => $fileName,
                ]);

            if ($response->successful()) {
                $imgManager = new ImageManager(new Driver());
                $img =
                    $imgManager->read($photo->getRealPath());

                $img->resize(500, 500)->toPng();

                Storage::put(
                    '/public/img/user_profile/'.$fileName,
                    (string) $img->encode()
                );

                return redirect()->intended(route('admin.users'));
            } elseif ($response->badRequest()) {
                return redirect()->back()->withErrors([
                    'create-error' => $response['error'],
                ])
                    ->withInput([
                        'full_name' => $request['full_name'],
                        'email' => $request['email'],
                        'date_of_birth' => $request['date_of_birth'],
                        'phone_number' => $request['phone_number'],
                        'address' => $request['address'],
                        'nik' => $request['nik'],
                        'gender' => $request['gender'],
                        'department_id' => $request['department_id'],
                        'photo' => $request['photo'],
                    ]);
            } elseif ($response->unauthorized()) {
                return redirect()->intended(route('admin.login'));
            }

            return abort($response->status());
        } catch (\Exception $e) {
            if ($e instanceof HttpException) {
                throw new HttpException($response->status());
            }

            return abort(500);
        }

    }

    public function update(Request $request, int $id)
    {
        $id = intval($id);

        $validator = Validator::make($request->all(), [
            'full_name' => ['required'],
            'email' => ['required', 'email'],
            'phone_number' => ['required', 'max:13', 'regex:/^\d+$/'],
            'date_of_birth' => ['required', 'date'],
            'address' => ['required'],
            'nik' => ['required', 'digits:16'],
            'gender' => ['required', 'in:Male,Female'],
            'department_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors([
                'update-error-'.$id => $validator->errors()->first(),
            ])
                ->withInput([
                    'full_name' => $request['full_name'],
                    'email' => $request['email'],
                    'date_of_birth' => $request['date_of_birth'],
                    'phone_number' => $request['phone_number'],
                    'address' => $request['address'],
                    'nik' => $request['nik'],
                    'gender' => $request['gender'],
                    'department_id' => $request['department_id'],
                ]);

            // return dd($validator);
        }

        try {
            $response =
                Http::withHeaders([
                    'Authorization' => 'Bearer '.$request->cookie('auth_token'),
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json',
                ])->put(BackendServer::url().'/api/user/update/'.$id, [
                    'full_name' => $request['full_name'],
                    'email' => $request['email'],
                    'date_of_birth' => $request['date_of_birth'],
                    'phone_number' => $request['phone_number'],
                    'address' => $request['address'],
                    'nik' => $request['nik'],
                    'gender' => $request['gender'],
                    'department_id' => intval($request['department_id']),
                    'new_password' => $request['new_password'],
                ]);

            if ($response->successful()) {
                return redirect()->intended(route('admin.users'));
            } elseif ($response->badRequest()) {
                return redirect()->back()->withErrors([
                    'update-error-'.$id => $response['message'],
                ])
                    ->withInput([
                        'full_name' => $request['full_name'],
                        'email' => $request['email'],
                        'date_of_birth' => $request['date_of_birth'],
                        'phone_number' => $request['phone_number'],
                        'address' => $request['address'],
                        'nik' => $request['nik'],
                        'gender' => $request['gender'],
                        'department_id' => $request['department_id'],
                    ]);
            } elseif ($response->unauthorized()) {
                return redirect()->intended(route('admin.login'));
            }

            return abort($response->status()); // Bad request
        } catch (\Exception $e) {
            if ($e instanceof HttpException) {
                throw new HttpException($response->status());
            }
            dd($response);

            return abort(500);
        }
    }

    public function delete(Request $request, int $id)
    {
        $id = intval($id);

        try {
            $response =
                Http::withHeaders([
                    'Authorization' => 'Bearer '.session('token'),
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json',
                ])->delete(BackendServer::url().'/api/user/delete/'.$id);

            if ($response->successful()) {
                switch ($response['status']) {
                    case 200: // Ok
                        return redirect()->intended(route('admin.users'));

                    case 401: // Unauthorized
                        return redirect()->intended(route('admin.login'));
                }

                return abort($response['status']);
            }

            return abort(400); // Bad request
        } catch (\Exception $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                return abort($e->getStatusCode());
            }

            return abort(500);
        }
    }
}
