<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\RoleRequest;
use App\Services\BUserService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

    public function __construct()
    {
        $this->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::ADD_AND_EDIT_ROLES]), ['except' => ['index', 'edit']]);

    }

    public function index(BUserService $BUserService)
    {

        $roles = $BUserService->getPaginatedAllAvailableRoles();

        return view('backoffice.b-users.roles.index', compact('roles'));
    }

    public function create(BUserService $BUserService)
    {
        return view('backoffice.b-users.roles.create');
    }


    public function store(RoleRequest $request)
    {
        $role = Role::create(['name' => $request->name]);
        $role->givePermissionTo($request->permissions);

        session()->flash('success', t('role_successfully_added'));
        return redirect()->route('roles.index');
    }

    public function edit($id, BUserService $BUserService)
    {
        $role = Role::findOrFail($id);
        return view('backoffice.b-users.roles.edit', compact('role'));
    }

    public function update(RoleRequest $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->name = $request->name;
        $role->save();
        $role->syncPermissions($request->permissions);

        session()->flash('success', t('role_successfully_updated'));
        return redirect()->route('roles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
