<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CourseRepository;

#[ORM\Table(name: "Courses")]
#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $course_id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $course_title = null;

    #[ORM\Column(type: 'string')]
    private ?string $teacher_id = null;

    #[ORM\Column(type: 'string')]
    private ?string $day = null;

    #[ORM\Column(type: 'string')]
    private ?string $begin = null;

    #[ORM\Column(type: 'string')]
    private ?string $end = null;



    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'courses')]
    #[ORM\JoinTable(name: 'enrolled')]
    private Collection $users;

    public function __construct()
        {
            $this->users = new ArrayCollection();
        }

    public function getCourseId(): ?int
        {
            return $this->course_id;
        }

    public function setCourseId(int $course_id): self
        {
            $this->course_id = $course_id;
            return $this;
        }

    public function getCourseTitle(): ?string
        {
            return $this->course_title;
        }

    public function setCourseTitle(string $course_title): self
        {
            $this->course_title = $course_title;
            return $this;
        }

    public function getUsers(): Collection
        {
            return $this->users;
        }

    public function addUser(User $user): self
        {
            if (!$this->users->contains($user))
                {
                    $this->users->add($user);
                    $user->addCourse($this);
                }
            return $this;
        }

    public function removeUser(User $user): self
        {
            if ($this->users->removeElement($user))
                {
                    $user->removeCourse($this);
                }
            return $this;
        }

    /**
     * @return string|null
     */
    public function getDay(): ?string
    {
        return $this->day;
    }

    /**
     * @return string|null
     */
    public function getBegin(): ?string
    {
        return $this->begin;
    }

    /**
     * @return string|null
     */
    public function getEnd(): ?string
    {
        return $this->end;
    }
}
