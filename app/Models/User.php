<?php namespace App\Models;

use App\Models\Traits\Staged;
use KentAuth\Models\User as KentUser;

class User extends KentUser {
    use Staged;
}
