<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use App\Repository\EnrolledRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScheduleController extends AbstractController
{
    private EntityManager $entityManager;
    public Array $courses;

    #[Route('/schedule', name: 'schedule')]
    public function schedule(Request $request, UserRepository $userRepository, EnrolledRepository $enrolledRepository, CourseRepository $courseRepository): Response
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $courses = null;
        $enroll = $enrolledRepository->findByUserId($userId);
        $courses_list = null;
        $course_object = null;
        $courses_monday = [];
        $courses_tuesday = [];
        $courses_wednsesday = [];
        $courses_thursday = [];
        $courses_friday = [];

        for($i = 0; $i < sizeof($enroll); ++$i)
        {
            $courses_list[$i] = $enroll[$i]->getCourse();
            $courses[$i] = $courseRepository->findOneBy(['course_id' => $courses_list[$i]]);


            $course_object[$i] = [
                'title' => $courses[$i]->getCourseTitle(),
                'day' => $courses[$i]->getDay(),
                'begin' => $courses[$i]->getBegin(),
                'end' => $courses[$i]->getEnd()
            ];

            if($courses[$i]->getDay() == 'Monday')
            {
                array_push($courses_monday, $course_object[$i]);
            }

            if($courses[$i]->getDay() == 'Tuesday')
            {
                array_push($courses_tuesday, $course_object[$i]);
            }

            if($courses[$i]->getDay() == 'Wednesday')
            {
                array_push($courses_wednsesday, $course_object[$i]);
            }

            if($courses[$i]->getDay() == 'Thursday')
            {
                array_push($courses_thursday, $course_object[$i]);
            }

            if($courses[$i]->getDay() == 'Friday')
            {
                array_push($courses_friday, $course_object[$i]);
            }
        }

        return $this->render('schedule.html.twig', [
            'pageTitle' => 'Schedule at StudentBridges',
            'userId' => $userId,
            'length' => sizeof($enroll),
            'all_courses' => $course_object,
            'courses_monday' => $courses_monday,
            'courses_tuesday' => $courses_tuesday,
            'courses_wednesday' => $courses_wednsesday,
            'courses_thursday' => $courses_thursday,
            'courses_friday' => $courses_friday,
        ]);
    }
}