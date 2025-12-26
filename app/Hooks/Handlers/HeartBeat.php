<?php

namespace TaskLedger\App\Hooks\Handlers;

use TaskLedger\App\App;

class HeartBeat
{
    public function handle($response, $data)
    {
        $key = App::config()->get('app.slug');

        $response[$key] = wp_create_nonce('wp_rest');
        
        return $response;
    }
}
