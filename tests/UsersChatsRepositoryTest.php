<?php

namespace App\Tests\Repository;

use App\Entity\UsersChats;
use App\Repository\UsersChatsRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UsersChatsRepositoryTest extends KernelTestCase
{
    private $entityManager;
    private $usersChatsRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->entityManager->beginTransaction();
        $this->entityManager->getConnection()->setAutoCommit(false);

        $this->usersChatsRepository = $this->entityManager->getRepository(UsersChats::class);
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
        parent::tearDown();
    }

    public function testFindChatsByUserId()
    {
        $userId = "r1";
        $results = $this->usersChatsRepository->findChatsByUserId($userId);
        $this->assertEquals(1, $results[0]['chatId']);
        $this->assertEquals("r2", $results[0]['userId']);
    }

    public function testFindChatByUserIds()
    {
        $result = $this->usersChatsRepository->findChatByUserIds("r1", "r2");
        $this->assertNotNull($result);
        $this->assertEquals(1, $result['chatId']);
    }
}
