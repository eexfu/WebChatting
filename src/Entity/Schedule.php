<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name:"enrolled")]
#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:"enrolled_id")]
    public ?int $id = null;

    #[ORM\Column(name:"user_id", length:10)]
    private ?string $userId = null;

    #[ORM\Column(name:"course_id")]
    private ?int $courseId = null;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'enrolled')]
    #[ORM\JoinTable(name: 'User')]
    private Collection $enrollments;

    public function __construct()
    {
        $this->enrollments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function getCourseId(): ?int
    {
        return $this->courseId;
    }


    public function setCourseId(?int $courseId): void
    {
        $this->courseId = $courseId;
    }

    public function getCourses(): Collection
    {
        return $this->enrollments;
    }

    //For testing purposes
    public function getBegin(?string $userId): Collection
    {
        return $this->enrollments->get('begin');
    }
}
