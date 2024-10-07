<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    private ?EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        error_reporting(0);

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testUser(): void
    {
        $schedule = $this->entityManager->getRepository(User::class);
        $user = $schedule->findByName('charlene');
        $this->assertSame($user->getEmail(), "charlene.herrmann@student.kuleuven.be");
    }

    public function testFindById()
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $results = $userRepository->findById("r1");
        $this->assertCount(1, $results);
        $this->assertEquals("r1", $results[0]->getId());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}