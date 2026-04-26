<?php

namespace App\Repository;

/**
 * Alias of UserRepository for backward compatibility.
 * All controllers using UsersRepository will work through this class.
 */
class UsersRepository extends UserRepository
{
    // Inherits all methods from UserRepository
}
