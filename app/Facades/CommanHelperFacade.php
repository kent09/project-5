<?php

namespace App\Facades;

/**
 * Form facade.
 */
class CommanHelperFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'App\Lib\CommanHelper';
    }
}
