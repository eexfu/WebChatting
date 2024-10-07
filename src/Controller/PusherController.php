<?php

namespace App\Controller;

use App\Repository\UserRepository;
use GuzzleHttp\Exception\GuzzleException;
use Pusher\ApiErrorException;
use Pusher\PusherException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\PusherService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PusherController extends AbstractController
{
    private PusherService $pusher;

    public function __construct(PusherService $pusher)
    {
        $this->pusher = $pusher;
    }

    /**
     * @throws PusherException
     * @throws ApiErrorException
     * @throws GuzzleException
     */
    public function sendNotification(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(),true);
        $channel = "my-channel-" . $data['receiver']['id'];
        $event = $data['type'];
        $message = (array)$data;
        $this->pusher->trigger($channel, $event, $message);

        return new JsonResponse([
            'status' => 'Notification sent success',
            'channel' => $channel,
            'event' => $event,
            "message" => $message,
            'data' => $data
        ]);
    }

    /**
     * @throws PusherException
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    #[Route("/create-chat", "create-chat")]
    public function testConnection(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $channel = "my-channel-" . $data['userId2'];
        $event = "createChat";
        $message = (array)$data;
        $this->pusher->trigger($channel, $event, $message);

        return new JsonResponse([
            'status' => 'Create chat request sent success',
            'channel' => $channel,
            'event' => $event,
            "message" => $message,
            'data' => $data
        ]);
    }
}
