<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Course;
use App\Entity\Enrolled; // Ensure this is imported

class SettingsController extends AbstractController
{
    #[Route("/settings", name: "settings")]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            // User is logged in, redirect to the desired route
            return $this->redirectToRoute('app_login');
        }

        $userDate = [
            'firstname' => $user->getFirstName(),
            'lastname' => $user->getLastName(),
            'email' => $user->getEmail(),
            'id' => $user->getID(),
            'birthdate' => $user->getBirthdate(),
            'icon' => base64_encode($user->getIcon())
        ];



        return $this->render('settings.html.twig', [
            'user' => $userDate,
            'serverIp' => 'https://a23www106.studev.groept.be/public',
//            'serverIp' => 'http://localhost',
            'userId' => $user->getId()
        ]);
    }

    #[Route("/settings/upload-icon")]
    public function upload(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $userId = $request->request->get('userId');
        $imageFile = $request->files->get('imageFile');

        if (!$userId || !$imageFile) {
            return new Response('Invalid input', Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }

        $user->setIcon(file_get_contents($imageFile->getPathname()));
        $em->persist($user);
        $em->flush();

        return new Response('Image uploaded successfully');
    }

    #[Route("/settings/enroll", name: "enroll_course", methods: ["POST"])]
    public function enrollCourse(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            // User is logged in, redirect to the desired route
            return $this->redirectToRoute('app_login');
        }

        $courseTitle = $request->request->get('course_title');
        $userId = $user->getUserIdentifier();

        $this->enrollInCourse($courseTitle, $userId, $entityManager);

        return $this->redirectToRoute('settings');
    }

    private function enrollInCourse($courseTitle, $userId, EntityManagerInterface $entityManager)
    {
        $courseRepository = $entityManager->getRepository(Course::class);
        $course = $courseRepository->findOneBy(['course_title' => $courseTitle]);
        $courseId = $course->getCourseId();

        if (!$course) {
            throw new \Exception("Course not found: $courseTitle");
        }

        $enrolled = new Enrolled();
        $enrolled->enrollCourse($userId, $courseId, $entityManager);
    }

    #[Route("/settings/disenroll", name: "disenroll_course", methods: ["POST"])]
    public function disenrollCourse(Request $request, UserRepository $userRepository, CourseRepository $courseRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $courseTitle = $request->request->get('course_title');

        $course = $courseRepository->findOneBy(['course_title' => $courseTitle]);

        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        $course_id = $course->getCourseId();

        $enrolled = new Enrolled();
        $enrolled->disenrollCourse($user->getUserIdentifier(), $course_id, $entityManager);

        return $this->redirectToRoute('settings');
    }
}
