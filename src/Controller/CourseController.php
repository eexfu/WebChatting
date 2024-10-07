<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use App\Repository\EnrolledRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends AbstractController
{
    public array $course_titles;

    #[Route("/course", name:"course")]
    public function renderPage(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $this->init($request);
        $session = $request->getSession();
        $session->set('user', $user);

        $groupList = $session->get('groupList', default: []);
        $feedbacks = $session->get('feedbacks', default: []);

        return $this->render('course.html.twig',[
            'user' => $user,
            'groupList' => $groupList,
            'feedbacks' => $feedbacks
        ]);
    }

    private function init(Request $request): void
    {
        $session = $request->getSession();

        // TODO get the groups by userId from database
        $groupList = [
            ['id' => 1, 'name' => 'WebTech'],
            ['id' => 2, 'name' => 'EE3'],
            ['id' => 3, 'name' => 'Transistor']
        ];
        // TODO get the feedbacks by the courseId from database
        $feedbacks = [
            ['id' => 1, 'username' => 'Alice', 'content' => 'This course is interesting. I really like it.', 'commented at' => '2024-5-12'],
            ['id' => 2, 'username' => 'Bob', 'content' => 'This course is interesting. I really like it.', 'commented at' => '2024-5-12'],
            ['id' => 3, 'username' => 'Charlie', 'content' => 'This course is interesting. I really like it.', 'commented at' => '2024-5-12'],
            ['id' => 4, 'username' => 'Josh', 'content' => 'This course is interesting. I really like it.', 'commented at' => '2024-5-12'],
        ];

        $session->set('groupList', $groupList);
        $session->set('feedbacks', $feedbacks);
    }

    #[Route("/course", name:"course")]
    public function getCourseTitles(UserRepository $userRepository, CourseRepository $courseRepository, EnrolledRepository $enrolledRepository)
    {
        $courses_list = [];
        $course_titles = [];
        $course_repository = null;
        $user = $this->getUser();
        $user_id = $user->getUserIdentifier();
        $enrolledRepository->findByUserId($user_id);
        $enrolled_list = $enrolledRepository->findBy(['user' => $user_id]);
        for($i=0; $i<sizeOf($enrolled_list); $i++)
        {
            $courses_list[$i] = $enrolled_list[$i]->getCourse();
            $course_repository[$i] = $courseRepository->findOneBy(['course_id' => $courses_list[$i]]);
            $course_titles[$i] = $course_repository[$i]->getCourseTitle();
        }
        return $this->render('course.html.twig',
            ['course_titles' => $course_titles,
                'user' => $user]);

    }
}