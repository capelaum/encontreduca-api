<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\V1\UpdateUserRequest;
use App\Http\Resources\Admin\ShowUserResource;
use App\Http\Resources\Admin\UserCollection;
use App\Models\ResourceUser;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Returns list of all users.
     *
     * @param Request $request
     * @return UserCollection
     * @throws AuthorizationException
     */
    public function index(Request $request): UserCollection
    {
        $this->authorize('isAdmin', [
            User::class,
            'listar os usuários.'
        ]);

        $users = User::query();

        $users->when($request->search, function ($query, $search) {
            return $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });

        $users = $users->paginate(20);

        return new UserCollection($users);
    }

    /**
     * Show single User data.
     *
     * @param User $user
     * @return ShowUserResource
     * @throws AuthorizationException
     */
    public function show(User $user): ShowUserResource
    {
        $this->authorize('isAdmin', [
            User::class,
            'visualizar este usuário.'
        ]);

        return new ShowUserResource($user);
    }

    /**
     * @param StoreUserRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('isAdmin', [
            User::class,
            'atualizar este usuário.'
        ]);

        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);

        if ($request->avatar) {
            $cloudinaryFolder = config('app.cloudinary_folder');

            $data['avatar_url'] = $request->file('avatar')
                ->storeOnCloudinary("$cloudinaryFolder/avatars")
                ->getSecurePath();
        }

        $user = User::create($data);

        event(new Registered($user));

        return response()->json(new ShowUserResource($user), 201);
    }

    /**
     * Update user and store on database
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('isAdmin', [
            User::class,
            'atualizar este usuário.'
        ]);

        $data = $request->validated();

        if ($request->avatar) {
            $cloudinaryFolder = config('app.cloudinary_folder');

            if (!$user->avatar_url) {
                $data['avatar_url'] = $request->file('avatar')
                    ->storeOnCloudinary("$cloudinaryFolder/avatars")
                    ->getSecurePath();
            }

            if ($user->avatar_url) {
                $avatarUrlArray = explode('/', $user->avatar_url);
                $publicId = explode('.', end($avatarUrlArray))[0];

                $data['avatar_url'] = $request->file('avatar')
                    ->storeOnCloudinaryAs("$cloudinaryFolder/avatars", $publicId)
                    ->getSecurePath();
            }
        }

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        if (!$request->password) {
            unset($data['password']);
        }

        if ($request->email && $user->email !== $request->email) {
            $user->newEmail($request->email);
            $data['email'] = $user->email;
        }

        $user->update($data);

        return response()->json(new ShowUserResource($user));
    }

    /**
     * Delete user avatar from database
     *
     * @param User $user
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function deleteAvatar(User $user): JsonResponse
    {
        $this->authorize('isAdmin', [
            User::class,
            'deletar o avatar deste usuário.'
        ]);

        if ($user->avatar_url) {
            $cloudinaryFolder = config('app.cloudinary_folder');

            $avatarUrlArray = explode('/', $user->avatar_url);
            $publicId = explode('.', end($avatarUrlArray))[0];

            cloudinary()->destroy("$cloudinaryFolder/avatars/$publicId");
        }

        $user->avatar_url = null;
        $user->save();

        return response()->json(null, 204);
    }

    /**
     * @param User $user
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('isAdmin', [
            User::class,
            'deletar o avatar deste usuário.'
        ]);

        $cloudinaryFolder = config('app.cloudinary_folder');

        $avatarUrlArray = explode('/', $user->avatar_url);
        $publicId = explode('.', end($avatarUrlArray))[0];

        cloudinary()->destroy("$cloudinaryFolder/avatars/$publicId");

        User::deleteUser($user);

        return response()->json(null, 204);
    }
}
