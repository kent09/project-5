<?php namespace App\Transformer;

use App\Transformer\Transformer;

/**
 * User Transformer
 */
class UserForSupportTransformer extends Transformer
{
    public function transform($item)
    {
        return [

                    'id'           => $item['id'],

                        'email'        => $item['email'],

                        'first_name'   => $item['first_name'],

                        'last_name'    => $item['last_name'],

                        'fullname'    => $item['full_name'],

            ];
    }
}
