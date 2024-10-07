<?php

namespace App\Tests\Repository;

use App\Entity\Messages;
use App\Repository\MessagesRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MessagesRepositoryTest extends KernelTestCase
{
    private $entityManager;
    private $messagesRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->entityManager->beginTransaction();
        $this->entityManager->getConnection()->setAutoCommit(false);

        $this->messagesRepository = $this->entityManager->getRepository(Messages::class);
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
        parent::tearDown();
    }

    public function testFindByChatId()
    {
        $results = $this->messagesRepository->findByChatId(41);
        $this->assertCount(1, $results);
        $this->assertEquals('Hi', $results[0]->getContent());
    }
}
