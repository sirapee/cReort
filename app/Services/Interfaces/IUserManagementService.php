<?php

namespace App\Services\Interfaces;

use App\Contracts\Responses\UserManagementResponse;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UserAuthorizationRequest;
use App\Http\Requests\UserCreationRequest;
use Illuminate\Http\Request;

interface IUserManagementService
{
    public function allUser(Request $request): UserManagementResponse;

    public function createNewUser(UserCreationRequest $request): UserManagementResponse;

    public function userDetails($id): UserManagementResponse;

    public function getDetails($id): UserManagementResponse;

    public function updateUser(UpdateUserRequest $request, $id = null): UserManagementResponse;

    public function getRoles(): UserManagementResponse;

    public function authorizeUser(UserAuthorizationRequest $request): UserManagementResponse;

    public function pendingAuthorization(): UserManagementResponse;

    public function permissionsDetails($slug): UserManagementResponse;
}
