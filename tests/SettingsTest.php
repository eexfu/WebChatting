<?php

namespace App\Tests;

use App\Entity\Course;
use App\Entity\Enrolled;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SettingsTest extends KernelTestCase
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


    public function testEnroll(): void
    {
        $enroll = new Enrolled();
        $enroll->setUser('r1');
        $enroll->setCourse(3);

        $this->entityManager->persist($enroll);
        $this->entityManager->flush();
    }

    public function testDisenroll(): void
    {
        $enrolledRepository = $this->entityManager->getRepository(Enrolled::class);
        $enroll = $enrolledRepository->findOneBy(['user' => 'r1', 'course' => 3]);
        $enroll->disenrollCourse('r1', 3, $this->entityManager);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}