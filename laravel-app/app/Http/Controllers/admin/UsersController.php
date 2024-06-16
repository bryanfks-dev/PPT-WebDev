<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Models\BackendServer;
use Intervention\Image\Drivers\Gd\Driver;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Intervention\Image\ImageManager;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        try {
            $userResponse =
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'applications/json'
                ])->get(BackendServer::url() . '/api/users');

            $departmentResponse =
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'applications/json'
                ])->get(BackendServer::url() . '/api/departments');

            if ($userResponse->successful() && $departmentResponse) {
                if ($userResponse['status'] == 200 && $departmentResponse['status'] == 200) {
                    if ($request->has('query')) {
                        $query = $request->get('query');

                        if (!empty($query)) {
                            $results = [];

                            foreach ($userResponse['data'] as $user) {
                                if (in_array(strtolower($query), array_map('strtolower', $user))) {
                                    $results[] = $user;
                                }
                            }

                            $paginatedUsers = $this->paginate($results ?? []);

                            return view("admin.users", [
                                'users' => $paginatedUsers,
                                'departments' => $departmentResponse['data'] ?? []
                            ]);
                        }
                    }

                    $paginatedUsers = $this->paginate($userResponse['data'] ?? []);

                    return view("admin.users", [
                        'users' => $paginatedUsers,
                        'departments' => $departmentResponse['data'] ?? []
                    ]);
                } else if ($userResponse['status'] == 401 || $departmentResponse['status'] == 401) {
                    return redirect()->intended(route('admin.login'));
                }

                if ($userResponse['status'] == 200) {
                    return abort($departmentResponse['status']);
                }

                return abort($userResponse['status']);
            }

            return abort(400); // Bad request
        } catch (\Exception $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                return abort($e->getStatusCode());
            }

            return abort(500);
        }
    }

    public function create(Request $request)
    {
        $fields = \Validator::make($request->all(), [
            'full_name' => ['required'],
            'email' => ['required', 'email'],
            'phone_number' => ['required', 'digits_between:11,13'],
            'date_of_birth' => ['required', 'date'],
            'address' => ['required'],
            'nik' => ['required', 'digits:16'],
            'gender' => ['required', 'in:Male,Female'],
            'department_id' => ['required', 'integer'],
            'photo' => ['required'],
            'photo.*' => ['required', 'mimes:png,jpg,jpeg']
        ]);

        if ($fields->fails()) {
            return redirect()->back()->withErrors([
                'create-error' => $fields->errors()->first()
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
                    'photo' => $request['photo']
                ]);
        }

        try {
            $photo = $request->file('photo');
            $fileName = time() . '_user_' .
                $request['full_name'] . '.png';

            $response =
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . session('token'),
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json'
                ])->post(BackendServer::url() . '/api/user/create', [
                            'full_name' => $request['full_name'],
                            'email' => $request['email'],
                            'date_of_birth' => $request['date_of_birth'],
                            'phone_number' => $request['phone_number'],
                            'address' => $request['address'],
                            'nik' => $request['nik'],
                            'gender' => $request['gender'],
                            'department_id' => intval($request['department_id']),
                            'photo' => $fileName
                        ]);

            if ($response->successful()) {
                switch ($response['status']) {
                    case 200: // Ok
                        // Save user photo to storage
                        $imgManager = new ImageManager(new Driver());
                        $img =
                            $imgManager->read($photo->getRealPath());

                        $img->resize(500, 500)->toPng();

                        \Storage::put(
                            '/public/img/user_profile/' . $fileName,
                            (string) $img->encode()
                        );

                        return redirect()->intended(route('admin.users'));

                    case 400: // Bad request
                        return redirect()->back()->withErrors([
                            'create-error' => $response['message']
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
                                'photo' => $request['photo']
                            ]);

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

            dd($e);

            return abort(500);
        }
    }

    public function update(Request $request, int $id)
    {
        $id = intval($id);

        $fields = \Validator::make($request->all(), [
            'full_name' => ['required'],
            'email' => ['required', 'email'],
            'phone_number' => ['required', 'digits_between:11,13'],
            'date_of_birth' => ['required', 'date'],
            'address' => ['required'],
            'nik' => ['required', 'digits:16'],
            'gender' => ['required', 'in:Male,Female'],
            'department_id' => ['required', 'integer']
        ]);

        if ($fields->fails()) {
            return redirect()->back()->withErrors([
                'update-error-' . $id => $fields->errors()->first(),
            ])
                ->withInput([
                    'full_name' => $request['full_name'],
                    'email' => $request['email'],
                    'date_of_birth' => $request['date_of_birth'],
                    'phone_number' => $request['phone_number'],
                    'address' => $request['address'],
                    'nik' => $request['nik'],
                    'gender' => $request['gender'],
                    'department_id' => $request['department_id']
                ]);
        }

        try {
            $response =
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . session('token'),
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json'
                ])->put(BackendServer::url() . '/api/user/update/' . $id, [
                            'full_name' => $request['full_name'],
                            'email' => $request['email'],
                            'date_of_birth' => $request['date_of_birth'],
                            'phone_number' => $request['phone_number'],
                            'address' => $request['address'],
                            'nik' => $request['nik'],
                            'gender' => $request['gender'],
                            'department_id' => intval($request['department_id']),
                            'new_password' => $request['new_password']
                        ]);

            if ($response->successful()) {
                switch ($response['status']) {
                    case 200: // Ok
                        return redirect()->intended(route('admin.users'));

                    case 400: // Bad request
                        return redirect(route('admin.users'))->withErrors([
                            'update-error-' . $id => $response['message'],
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

    public function delete(Request $request, int $id)
    {
        $id = intval($id);

        try {
            $response =
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . session('token'),
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json'
                ])->delete(BackendServer::url() . '/api/user/delete/' . $id);

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