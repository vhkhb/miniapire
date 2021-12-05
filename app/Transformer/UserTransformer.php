<?php

namespace App\Transformer;

use App\Models\User;
use League\Fractal\TransformerAbstract;


class UserTransformer extends TransformerAbstract
{
    /**
     * @param User $user
     * @return array
     *
     * This method is use for the transform object to Array for user data
     */
    public function transform(User $user)
    {
        return [
            'Id'    => (int) $user->id,
            'Name'  => (string) $user->name,
            'Email' => (string) $user->email,
            'CreatedAt' => $user->created_at->format('d-m-Y'),
        ];
    }
}
