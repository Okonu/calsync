<?php

namespace App\Services;

use Laravel\Socialite\SocialiteManager;
use Laravel\Socialite\Two\GoogleProvider;

class SocialiteConnectManager extends SocialiteManager
{
    /**
     * instance of the Google driver for connecting accounts.
     */
    protected function createGoogleDriver()
    {
        $config = $this->config->get('services.google');

        $config['redirect'] = config('services.google.connect_redirect', $config['redirect']);

        return $this->buildProvider(
            GoogleProvider::class, $config
        );
    }
}
