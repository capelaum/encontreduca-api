<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private array $userKeys = [
        'id',
        'name',
        'avatarUrl',
        'reviewCount',
        'resourcesIds'
    ];

    public function test_list_users()
    {
        $this->authAdmin();

        $this->getJson(route('users.index'))
            ->assertOk()
            ->assertJsonStructure(['*' => $this->userKeys])
            ->json();
    }

    public function test_user_cannot_list_users()
    {
        $this->authUser();

        $this->createUser();

        $this->withExceptionHandling();

        $this->getJson(route('users.index'))
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_show_user()
    {
        $this->authUser();

        $this->getJson(route('users.show', Auth::id()))
            ->assertOk()
            ->assertJsonStructure($this->userKeys)
            ->json();
    }

    public function test_user_cannot_show_other_user_data()
    {
        $this->authUser();

        $user = $this->createUser();

        $this->withExceptionHandling();

        $this->getJson(route('users.show', $user->id))
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_update_user()
    {
        $this->authUser();
        $updatedUser = User::factory(['password' => 'password'])->make();

        $this->patchJson(route('users.update', Auth::id()), [
            'name' => $updatedUser->name,
            'email' => $updatedUser->email,
            'avatarUrl' => $updatedUser->avatar_url,
            'password' => $updatedUser->password,
            'confirmPassword' => $updatedUser->password,
        ])->assertOk()
            ->assertJsonStructure($this->userKeys);

        $this->assertDatabaseHas('users', [
            'name' => $updatedUser->name,
            'email' => Auth::user()->email,
            'avatar_url' => $updatedUser->avatar_url,
            'email_verified_at' => date($updatedUser->email_verified_at),
        ]);

        // assert that new email is stores in pending user emails table
        $this->assertDatabaseHas('pending_user_emails', [
            'user_id' => Auth::id(),
            'user_type' => 'App\Models\User',
            'email' => $updatedUser->email,
        ]);

        // assert that new password matches the auth user password
        $this->assertTrue(Hash::check($updatedUser->password, Auth::user()->getAuthPassword()));
    }

    public function test_update_user_except_email_and_password()
    {
        $this->authUser();
        $updatedUser = User::factory()->make();

        $this->patchJson(route('users.update', Auth::id()), [
            'name' => $updatedUser->name,
            'email' => Auth::user()->email,
            'avatarUrl' => $updatedUser->avatar_url,
        ])->assertOk()
            ->assertJsonStructure($this->userKeys);

        $this->assertDatabaseHas('users', [
            'name' => $updatedUser->name,
            'email' => Auth::user()->email,
            'avatar_url' => $updatedUser->avatar_url,
        ]);
    }

    public function test_user_cannot_update_other_user()
    {
        $this->withExceptionHandling();
        $this->authUser();

        $updatedUser = $this->createUser(['password' => 'password']);

        $this->patchJson(route('users.update', $updatedUser->id), [
            'name' => $updatedUser->name,
            'email' => $updatedUser->email,
            'avatarUrl' => $updatedUser->avatar_url,
            'password' => $updatedUser->password,
            'confirmPassword' => $updatedUser->password
        ])->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_delete_user()
    {
        $this->authUser();

        $this->deleteJson(route('users.destroy', Auth::id()))
            ->assertNoContent();

        $this->assertDatabaseMissing('users', [
            'id' => Auth::id()
        ]);
    }

    public function test_user_cannot_delete_other_user()
    {
        $this->withExceptionHandling();
        $this->authUser();

        $user = $this->createUser();

        $this->deleteJson(route('users.destroy', $user->id))
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_delete_user_avatar()
    {
        $this->authUser();

        $this->deleteJson(route('users.delete.avatar', Auth::id()))
            ->assertNoContent();

        $this->assertDatabaseHas('users', [
            'id' => Auth::id(),
            'avatar_url' => null
        ]);
    }

    public function test_user_cannot_delete_other_user_avatar()
    {
        $this->withExceptionHandling();
        $this->authUser();

        $user = $this->createUser();

        $this->deleteJson(route('users.delete.avatar', $user->id))
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_store_user_resource()
    {
        $this->authUser();

        $resource = $this->createResource();

        $this->postJson(route('users.store.resource', $resource->id))
            ->assertCreated()
            ->assertJsonStructure([
                'user_id',
                'resource_id'
            ]);

        $this->assertDatabaseHas('resource_user', [
            'user_id' => Auth::id(),
            'resource_id' => $resource->id
        ]);
    }

    public function test_user_cannot_save_a_resource_twice()
    {
        $this->authUser();

        $resource = $this->createResource();
        $this->createResourceUser([
            'user_id' => Auth::id(),
            'resource_id' => $resource->id
        ]);

        $this->postJson(route('users.store.resource', $resource->id))
            ->assertStatus(409)
            ->assertJson([
                'message' => 'Você já salvou este recurso.'
            ]);
    }

    public function test_delete_resource_user()
    {
        $this->authUser();

        $resource = $this->createResource();
        $this->createResourceUser([
            'user_id' => Auth::id(),
            'resource_id' => $resource->id
        ]);

        $this->deleteJson(route('users.delete.resource', $resource->id))
            ->assertNoContent();

        $this->assertDatabaseMissing('resource_user', [
            'user_id' => Auth::id(),
            'resource_id' => $resource->id
        ]);
    }

    public function test_user_cannot_delete_saved_resource_of_another_user()
    {
        $this->withExceptionHandling();
        $this->authUser();

        $resource = $this->createResource();
        $this->createResourceUser([
            'user_id' => $this->userIdsWithoutAuthUser->random(),
            'resource_id' => $resource->id
        ]);

        $this->deleteJson(route('users.delete.resource', $resource->id))
            ->assertStatus(400)
            ->assertJson([
                'message' => 'Você não possui esse recurso salvo.'
            ]);
    }
}