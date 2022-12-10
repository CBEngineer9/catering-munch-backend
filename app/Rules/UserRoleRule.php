<?php

namespace App\Rules;

use App\Models\Users;
use Illuminate\Contracts\Validation\Rule;

class UserRoleRule implements Rule
{
    /** @var Array $roles roles allowed to pass */
    protected $roles;

    /**
     * Create a new rule instance.
     * 
     * @param $roles roles allowed to pass
     * @return void
     */
    public function __construct(...$roles)
    {
        $this->roles = $roles;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = Users::findOrFail($value);
        $pass = false;
        foreach ($this->roles as $role) {
            if ($user->users_role === $role) {
                $pass = true;
            }
        }
        return $pass;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The user in :attribute have to be ' . implode(",",$this->roles);
    }
}
