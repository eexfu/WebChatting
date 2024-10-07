<?php

// src/Entity/Enrolled.php
namespace App\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name:"enrolled")]
#[ORM\Entity(repositoryClass:"App\Repository\EnrolledRepository")]
class Enrolled
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'enrolled_id', type: "integer")]
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    #[ORM\Column(name: 'user_id', type: "string")]
    private $user = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Course")
     * @ORM\JoinColumn(nullable=false)
     */
    #[ORM\Column(name: 'course_id',type: "integer")]
    private $course = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(?string $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getCourse(): ?int
    {
        return $this->course;
    }

    public function setCourse(?int $course): self
    {
        $this->course = $course;
        return $this;
    }

    public function enrollCourse(?string $user_id, ?int $course_id, EntityManagerInterface $entityManager)
    {
        $this->setCourse($course_id);
        $this->setUser($user_id);

        $entityManager->persist($this);
        $entityManager->flush();
    }

    public function disenrollCourse(?string $user_id, ?int $course_id, EntityManagerInterface $entityManager)
    {
        $enrolledRepository = $entityManager->getRepository(Enrolled::class);
        $enroll = $enrolledRepository->findOneBy(array('course' => $course_id, 'user' => $user_id));
        $this->setUser($enroll->getUser());
        $this->setCourse($enroll->getCourse());

        $entityManager->remove($enroll);
        $entityManager->flush();
    }
}