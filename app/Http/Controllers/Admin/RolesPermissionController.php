<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RolesPermissionController extends Controller
{
	public function index()
	{
		$data['roles'] = Role::query()->orderBy('id', 'desc')->get();
		return view('admin.role_permission.roleList', $data);
	}

	public function roleCreate(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'name' => 'required',
			'permissions' => 'required|array',
			'permissions.*' => 'integer|not_in:0'
		]);
		if ($validator->fails()) {
			return response()->json(['errors' => $validator->messages()], 422);
		}

		$role = new Role();
		$role->user_id = auth()->id();
		$role->name = $request->name;
		$role->status = $request->status;
		$role->permission = $request->permissions;

		$role->save();
		session()->flash('success', 'Saved Successfully');
		return response()->json(['result' => $role]);
	}

	public function roleUpdate(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'id' => 'required',
			'name' => 'required',
			'permissions' => 'required|array',
			'permissions.*' => 'integer|not_in:0'
		]);
		if ($validator->fails()) {
			return response()->json(['errors' => $validator->messages()], 422);
		}

		$role = Role::findOrFail($request->id);
		$role->name = $request->name;
		$role->status = $request->status;
		$role->permission = $request->permissions;

		$role->save();

        $usersWithRole = Admin::where('role_id', $role->id)->get();
        foreach ($usersWithRole as $user) {
            \Cache::forget("role_permissions_{$user->role_id}");
        }

		session()->flash('success', 'Update Successfully');
		return response()->json(['result' => $role]);
	}

	public function roleDelete($id)
	{
		$role = Role::with(['roleUsers'])->find($id);
		if (count($role->roleUsers) > 0) {
			return back()->with('alert', 'This role has many users');
		}
		$role->delete();
		return back()->with('success', 'Delete successfully');
	}

	public function staffList()
	{
		$data['roleUsers'] = Admin::where('is_owner', 0)->orderBy('name', 'asc')->get();
		$data['roles'] = Role::where('status', 1)->orderBy('name', 'asc')->get();
		return view('admin.role_permission.userList', $data);
	}

	public function staffCreate(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email'],
			'username' => ['required', 'string', 'max:50', 'unique:admins,username'],
			'password' => ['required', 'string', 'min:6'],
			'role' => ['required'],
		]);
		if ($validator->fails()) {
			return response()->json(['errors' => $validator->messages()], 422);
		}

		$user = new Admin();
		$user->name = $request->name;
		$user->email = $request->email;
		$user->username = $request->username;
		$user->password = Hash::make($request->password);
		$user->role_id = $request->role;
		$user->status = $request->status;

		$user->save();
		session()->flash('success', 'Saved Successfully');
		return response()->json(['result' => $user]);
	}

	public function statusChange($id)
	{
		$user = Admin::findOrFail($id);
		if ($user) {
			if ($user->status == 1) {
				$user->status = 0;
			} else {
				$user->status = 1;
			}
			$user->save();
			return back()->with('success', 'Updated Successfully');
		}
	}

	public function userLogin($id)
	{
		Auth::guard('admin')->loginUsingId($id);
		return redirect()->route('admin.dashboard');
	}
}
