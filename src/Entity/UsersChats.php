<?php

namespace App\Entity;

use App\Repository\UsersChatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsersChatsRepository::class)]
#[ORM\Table(name: 'users_chats')]
class UsersChats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $userId = null;

    #[ORM\Column]
    private ?int $chatId = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getChatId(): ?int
    {
        return $this->chatId;
    }

    public function setChatId(int $chatId): static
    {
        $this->chatId = $chatId;

        return $this;
    }
}
