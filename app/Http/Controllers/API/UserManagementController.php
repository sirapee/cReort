<?php

namespace App\Http\Controllers\API;

use App\Exports\UserDetailsExport;
use app\Helpers\HelperFunctions;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UserAuthorizationRequest;
use App\Http\Requests\UserCreationRequest;
use App\Services\Interfaces\IUserManagementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Adldap\Laravel\Facades\Adldap;
use Illuminate\Support\Facades\Log;



class UserManagementController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public IUserManagementService $userService;
    public $staffId;
    public function __construct(IUserManagementService $service)
    {
        $this->userService = $service;
        $this->middleware('auth:api');
    }

    /**
     * Show a list of all the users.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->userService->allUser($request));
    }

    public function roles(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->userService->getRoles());
    }


    /**
     * User create form processing.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserCreationRequest $request): \Illuminate\Http\JsonResponse
    {
        $response =$this->userService->createNewUser($request);
        if($response->isSuccessful){
            return response()->json($response, 201);
        }
        return response()->json($response, 400);
    }


    public function details($id): \Illuminate\Http\JsonResponse
    {
        $response = $this->userService->getDetails($id);
        if($response->isSuccessful){
            return response()->json($response);
        }
        return response()->json($response, 400);
    }

    public function pendingDetails($id, $functionCode): \Illuminate\Http\JsonResponse
    {
        $response = $this->userService->authorizationUserDetails($id, $functionCode);
        if($response->isSuccessful){
            return response()->json($response);
        }
        return response()->json($response, 400);
    }

    /**
     * User update.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id): \Illuminate\Http\JsonResponse
    {
        $response = $this->userService->userDetails($id);
        if($response->isSuccessful){
            return response()->json($response);
        }
        return response()->json($response, 400);
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        $response = $this->userService->userDetails($id);
        if($response->isSuccessful){
            return response()->json($response);
        }
        return response()->json($response, 400);
    }

    public function adDetails($id): \Illuminate\Http\JsonResponse
    {
        $response = $this->userService->getDetails($id);
        if($response->isSuccessful){
            return response()->json($response);
        }
        return response()->json($response, 400);
    }

    public function permissionsDetails($slug): \Illuminate\Http\JsonResponse
    {
        $response = $this->userService->permissionsDetails($slug);
        if($response->isSuccessful){
            return response()->json($response);
        }
        return response()->json($response, 400);
    }



    /**
     * User update form processing page.
     *
     * @param Request $request
     * @param  int      $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, $id = null): \Illuminate\Http\JsonResponse
    {

        $response = $this->userService->updateUser($request, $id);
        if($response->isSuccessful){
            return response()->json($response);
        }
        return response()->json($response, 400);

    }



    public function destroy($userId): \Illuminate\Http\JsonResponse
    {
        $response = $this->userService->deleteUser($userId);
        if($response->isSuccessful){
            return response()->json($response);
        }
        return response()->json($response, 400);
    }

    public function restore($userId): \Illuminate\Http\JsonResponse
    {
        $response = $this->userService->restoreUser($userId);
        if($response->isSuccessful){
            return response()->json($response);
        }
        return response()->json($response, 400);
    }


    public function verify(UserAuthorizationRequest $request): \Illuminate\Http\JsonResponse
    {

        $response = $this->userService->authorizeUser($request);
        if($response->isSuccessful){
            return response()->json($response);
        }
        return response()->json($response, 400);

    }

    public function pendingAuthorization(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->userService->pendingAuthorization());
    }

    public function usersDetailsDownload(): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return (new UserDetailsExport)->download('Users.xlsx');
    }

    /**
     * Search user from database base on some specific constraints
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $constraints = [
            'username' => $request['username'],
            'first_name' => $request['firstname'],
            'last_name' => $request['lastname'],
            'emp_id' => $request['emp_id']
        ];

        $users = $this->doSearchingQuery($constraints);
        $response = [
            'ResponseCode' => 0,
            'Message' =>  'Processing Completed',
            'Details' => $users
        ];
        return response()->json($response, 200);
    }

    private function doSearchingQuery($constraints)
    {
        $query = User::query();
        $fields = array_keys($constraints);
        $index = 0;
        foreach ($constraints as $constraint) {
            if ($constraint != null) {
                $query = $query->where($fields[$index], 'like', '%' . $constraint . '%');
            }

            $index++;
        }
        return $query->paginate(5);
    }

    protected function sendMail($user, $code)
    {
        $activate = new  Activate($user, $code);
        Mail::to($user->email)->send($activate);


        // Mail::to($event->user->email)->send(new NewUserWelcome($event->user));
    }



}
