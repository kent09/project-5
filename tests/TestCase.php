<?php

use App\User;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://fusedtools.local';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        global $app;
        
        if (is_null($app)) {
            $app = require __DIR__.'/../bootstrap/app.php';

            $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        }
        return $app;
    }

    /**
     * Create a test signed in user
     *
     * @param null $user
     * @return mixed
     */
    public function signIn($user = null)
    {
        $user = $user ?: factory(User::class)->create();
        $this->actingAs($user);
        return $this;
    }

    /**
     * Add a session
     *
     * @param null $data
     * @return $this
     */
    public function addSession($data = null)
    {
        $this->withSession($data);
        return $this;
    }
}
