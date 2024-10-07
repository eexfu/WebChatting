<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Entity\Chat;
use App\Entity\UsersChats;
use App\Repository\MessagesRepository;
use App\Repository\UserRepository;
use App\Repository\UsersChatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route("/", name:"main")]
    public function showMainPage(Request $request, UserRepository $userRepository, UsersChatsRepository $usersChatsRepository, MessagesRepository $messagesRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $userData = [
            'id' => $user->getId(),
            'username' => $user->getName(),
            'email' => $user->getEmail(),
            'icon' => $user->getIcon()? base64_encode($user->getIcon()) : ''
        ];

        $this->init($request);

        $session = $request->getSession();

        $chats = $usersChatsRepository->findChatsByUserId($user->getUserIdentifier());
        $chatList = [];
        if (!empty($chats)) {
            foreach ($chats as $chat) {
                $otherUser = $userRepository->findById($chat['userId']);
                if(!empty($otherUser)){
                    $chatList[] = [
                        'id' => $chat['chatId'],
                        'userId' => $chat['userId'],
                        'username' => ($otherUser[0])->getName(),
                        'email' => ($otherUser[0])->getEmail(),
                        'icon' => ($otherUser[0])->getIcon()? base64_encode($otherUser[0]->getIcon()) : ''
                    ];
                }
            }
        }

        $messages = [];
        foreach($chats as $chat){
            $messages_temp = $messagesRepository->findByChatId($chat['chatId']);
            foreach($messages_temp as $message){
                $messages[] = [
                    'userId' => $message->getUserId(),
                    'chatId' => $message->getChatId(),
                    'groupId' => $message->getGroupId(),
                    'type' => $message->getType(),
                    'content' => $message->getContent(),
                    'sentAt' => $message->getSentAt(),
                    'deliveredAt' => $message->getDeliveredAt(),
                    'seenAt' => $message->getSeenAt()
                ];
            }
        }

        $serverIp = $session->get('serverIp', default: []);
        $webSocketIp = $session->get('webSocketIp', default: []);

        return $this->render('main.html.twig', [
            'chatList' => $chatList,
            'user' => $userData,
            'serverIp' => $serverIp,
            'webSocketIp' => $webSocketIp,
            'messages' => $messages
        ]);
    }

    private function init(Request $request): void
    {
        $session = $request->getSession();
        $serverIp = [
            'url' => 'https://a23www106.studev.groept.be/public'
//            'url' => 'http://localhost'
        ];
        $webSocketIp = [
            'url' => 'wss://a23www106.studev.groept.be:2346'
//            'url' => 'ws://localhost:2346'
        ];

        $session->set('serverIp', $serverIp);
        $session->set('webSocketIp', $webSocketIp);
    }

    #[Route("/conversation/send-message")]
    public function sendMessage(Request $request, EntityManagerInterface $entityManager, MessagesRepository $messagesRepository): Response
    {
        $data = json_decode($request->getContent(),true);
        if(!$data){
            return new JsonResponse(['error' => 'Invalid input'], Response::HTTP_BAD_REQUEST);
        }

        $message = new Messages();
        $message->setUserId($data['userId']);
        $message->setChatId($data['chatId']);
        $message->setGroupId($data['groupId'] ?? 0);
        $message->setType($data['type']);
        $message->setContent($data['content']);
        $message->setSentAt(new \DateTime($data['sentAt']));
        $message->setDeliveredAt(isset($data['deliveredAt']) ? new \DateTime($data['deliveredAt']) : null);
        $message->setSeenAt(isset($data['seenAt']) ? new \DateTime($data['seenAt']) : null);

        try {
            $entityManager->persist($message);
            $entityManager->flush();
            return new JsonResponse(['message' => 'Message stored successfully'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to store message: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route("/conversation/search-user", name: "search-user")]
    public function searchUser(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['username'])) {
            return new JsonResponse(['error' => 'Invalid input'], Response::HTTP_BAD_REQUEST);
        }

        $student_name = $data['username'];
        $users = $userRepository->findByName($student_name);
        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => $user->getId(),
                'username' => $user->getName(),
                'email' => $user->getEmail(),
                'icon' => $user->getIcon() ? base64_encode($user->getIcon()) : ''
            ];
        }

        return new JsonResponse($result);
    }

    #[Route("/conversation/search-userById", name: "search-userById")]
    public function searchUserById(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['id'])) {
            return new JsonResponse(['error' => 'Invalid input'], Response::HTTP_BAD_REQUEST);
        }

        $id = $data['id'];
        $users = $userRepository->findById($id);
        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => $user->getId(),
                'username' => $user->getName(),
                'email' => $user->getEmail(),
                'icon' => $user->getIcon() ? base64_encode($user->getIcon()) : ''
            ];
        }

        return new JsonResponse($result);
    }

    #[Route('/conversation/start-chat')]
    public function startChat(Request $request, EntityManagerInterface $entityManager, UsersChatsRepository $usersChatsRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['userId1'], $data['userId2'])) {
            return new JsonResponse(['error' => 'Invalid input', 'userID1' => $data['userId1'], 'userId2' => $data['userId2']], Response::HTTP_BAD_REQUEST);
        }

        $userId1 = $data['userId1'];
        $userId2 = $data['userId2'];

        $chatId = $usersChatsRepository->findChatByUserIds($userId1, $userId2);

        if (!$chatId) {
            $chat = new Chat();
            $entityManager->persist($chat);
            $entityManager->flush();

            $chatId = $chat->getId();

            $userChat1 = new UsersChats();
            $userChat1->setUserId($userId1);
            $userChat1->setChatId($chatId);

            $userChat2 = new UsersChats();
            $userChat2->setUserId($userId2);
            $userChat2->setChatId($chatId);

            $entityManager->persist($userChat1);
            $entityManager->persist($userChat2);
            $entityManager->flush();
        }

        $result = [
            'chatId' => $chatId
        ];

        return new JsonResponse($result);
    }

    #[Route("/about", name:"about")]
    public function showAboutPage(): Response
    {
        return $this->render('about.html.twig');
    }
}