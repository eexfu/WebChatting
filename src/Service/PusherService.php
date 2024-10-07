<?php
namespace App\Service;

use GuzzleHttp\Exception\GuzzleException;
use Pusher\ApiErrorException;
use Pusher\Pusher;
use Pusher\PusherException;

class PusherService
{
    private Pusher $pusher;

    /**
     * @throws PusherException
     */
    public function __construct(string $appId, string $key, string $secret, string $cluster)
    {
        $this->pusher = new Pusher($key, $secret, $appId, [
        'cluster' => $cluster,
        'useTLS' => true
        ]);
    }

    /**
     * @throws PusherException
     * @throws ApiErrorException
     * @throws GuzzleException
     */
    public function trigger(string $channel, string $event, array $data): void
    {
        $this->pusher->trigger($channel, $event, $data);
    }
}
