<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends AppBaseController
{
    private mixed $roles;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->roles = config('role');
    }
  
    public function show(User $user)
    {
        $employeeArray = User::where("user_type", "!=", 1)->with('leader')->get();
        $showExecutive = User::where('user_type', 5)->get();
        $roles = $this->roles;
        return view('users/show_employee', compact('employeeArray', 'roles', 'showExecutive'));
    }

    public function create(Request $request)
    {
        $res = new User();
        $res->name = $request->input('name');
        $res->email = $request->input('email');
        $res->password = Hash::make($request->input('password'));
        $res->user_type = $request->input('user_type');

        if ($request->input('user_type') == 6 && $request->input('team_leader_id') !== null) {
            $res->team_leader_id = $request->input('team_leader_id');
        }

        $res->save();

        return response()->json([
            'success' => "Created Successfully"
        ]);
    }

    public function update(Request $request, User $user)
    {
        $res = User::find($request->id);
        $res->name = $request->input('name');
        $res->email = $request->input('email');
        $res->user_type = $request->input('user_type');

        // dd($res);

        if ($request->input('user_type') == 6 && $request->input('team_leader_id') !== null) {
            $res->team_leader_id = $request->input('team_leader_id');
        }

        $res->save();

        return response()->json([
            'success' => "Updated Successfully"
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        $res = User::find($id);
        $res->password = Hash::make($request->input('new_password'));
        $res->save();

        return response()->json([
            'success' => "Reset Successfully"
        ]);
    }

    public function destroy(Request $request)
    {
        $currentuserid = Auth::user()->id;
        $idAdmin = RoleManager::isAdmin(Auth::user()->user_type);

        $res = User::find($request->id);
        if ($res->status == 1) {
            $res->status = 0;
        } else {
            if (!$idAdmin) {
                return response()->json([
                    'error' => "Ask admin to change."
                ]);
            }
            $res->status = 1;
        }
        $res->save();
        return response()->json([
            'success' => "Delete Successfully"
        ]);
    }
}
