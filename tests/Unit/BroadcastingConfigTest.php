<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BroadcastingConfigTest extends TestCase
{
    public function test_pusher_options_use_safe_fallbacks_when_env_values_are_empty(): void
    {
        putenv('BROADCAST_CONNECTION=pusher');
        putenv('PUSHER_APP_KEY=');
        putenv('PUSHER_APP_SECRET=');
        putenv('PUSHER_APP_ID=');
        putenv('PUSHER_APP_CLUSTER=');
        putenv('PUSHER_HOST=');
        putenv('PUSHER_PORT=');
        putenv('PUSHER_SCHEME=');

        $config = require dirname(__DIR__, 2) . '/config/broadcasting.php';

        $this->assertSame('mt1', $config['connections']['pusher']['options']['cluster']);
        $this->assertSame('api-mt1.pusher.com', $config['connections']['pusher']['options']['host']);
        $this->assertSame(443, $config['connections']['pusher']['options']['port']);
        $this->assertSame('https', $config['connections']['pusher']['options']['scheme']);
    }
}
