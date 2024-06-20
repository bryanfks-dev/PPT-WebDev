<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\BackendServer;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PeoplesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $response = null;

            $httpHeaders = [
                'Authorization' => 'Bearer ' . $request->cookie('auth_token'),
                'Accept' => 'application/json',
            ];

            $param = trim($request->get('query', ''), ' ');

            if (!empty($param)) {
                $response =
                    \Http::withHeaders($httpHeaders)
                        ->get(BackendServer::url() . '/api/users/me/department/users/search/' . $param);
            } else {
                $response =
                    \Http::withHeaders($httpHeaders)
                        ->get(BackendServer::url() . '/api/users/me/department/users');
            }

            if ($response->successful()) {
                $paginatedEmployees =
                    $this->paginate($response['data']['employees'] ?? [], 9);

                return view('user.peoples', [
                    'department_name' => $response['data']['name'],
                    'manager' => $response['data']['manager'],
                    'is_manager' => $response['data']['is_manager'],
                    'employees' => $paginatedEmployees,
                ]);
            } else if ($response->unauthorized()) {
                return redirect()->intended(route('user.login'));
            }

            return abort($response->status());
        } catch (\Exception $e) {
            if ($e instanceof HttpException) {
                return abort($response->status());
            }

            return abort(500);
        }
    }

    public function update(Request $request, int $id)
    {
        $id = intval($id);

        if ($id <= 0) {
            return abort(404);
        }

        $validator = \Validator::make($request->all(), [
            'initial_salary' => ['required', 'numeric', 'min:1'],
            'current_salary' => ['required', 'numeric', 'min:1']
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors([
                'update-error-' . $id => $validator->errors()->first()
            ]);
        }

        try {
            // Convert salary inputs to float
            $initial_salary =
                floatval(str_replace('.', '', $request->input('initial_salary')));
            $current_salary =
                floatval(str_replace('.', '', $request->input('current_salary')));

            $response =
                \Http::withHeaders([
                    'Authorization' => 'Bearer ' . $request->cookie('auth_token'),
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json',
                ])->put(BackendServer::url() . '/api/users/me/department/users/update/' . $id, [
                            'initial_salary' => $initial_salary,
                            'current_salary' => $current_salary,
                        ]);

            if ($response->successful()) {
                return redirect()->back()
                    ->with('update-success-' . $id, 'Salary updated successfully');
            } else if ($response->badRequest()) {
                return redirect()->intended(route('user.employees'))->withErrors([
                    'update-error-' . $id => $response['error']
                ])->withInput([
                            'initial_salary' => $request['initial_salary'],
                            'current_salary' => $request['current_salary'],
                        ]);
            } else if ($response->unauthorized()) {
                return redirect()->intended(route('user.login'));
            }

            return abort($response->status());
        } catch (\Exception $e) {
            if ($e instanceof HttpException) {
                return abort($response->status());
            }

            return abort(500);
        }
    }
}
