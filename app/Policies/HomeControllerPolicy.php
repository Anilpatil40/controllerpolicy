<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HomeControllerPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index($user){
        return true;
    }

    public function function1($user, $id){
        return $id == 3 || $id == 1;
    }

    public function function2($user){
        return true;
    }
}
