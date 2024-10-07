<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository(User::class);

        // Find the existing user
        $existingrUser = $userRepository->findOneBy(['id' => 'rtest']);

        // Remove the user if found
        if ($existingrUser) {
            $em->remove($existingrUser);
            $em->flush();
        }

        // Find the existing user
        $existinguUser = $userRepository->findOneBy(['id' => 'utest']);

        // Remove the user if found
        if ($existinguUser) {
            $em->remove($existinguUser);
            $em->flush();
        }

        // Create a User fixture
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');

        $rUser = (new User())->setEmail('email@student.kuleuven.be');
        $rUser->setId('rtest');
        $rUser->setFirstName('Test');
        $rUser->setLastName('Student');
        $rUser->setPassword($passwordHasher->hashPassword($rUser, 'password'));

        $uUser = (new User())->setEmail('email@kuleuven.be');
        $uUser->setId('utest');
        $uUser->setFirstName('Test');
        $uUser->setLastName('Teacher');
        $uUser->setPassword($passwordHasher->hashPassword($uUser, 'password'));

        $em->persist($rUser);
        $em->flush();
    }

    public function testLogin(): void
    {
        // Denied - Can't login with invalid email address.
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign In', [
            '_username' => 'doesNotExist@example.com',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        // Ensure we do not reveal if the user exists or not.
        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');

        // Denied - Can't login with invalid password.
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign In', [
            '_username' => 'email@student.kuleuven.be',
            '_password' => 'bad-password',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        // Ensure we do not reveal the user exists but the password is wrong.
        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');

        // Success - Login with valid credentials is allowed.
        $this->client->submitForm('Sign In', [
            '_username' => 'email@student.kuleuven.be',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();

        self::assertSelectorNotExists('.alert-danger');
        self::assertResponseIsSuccessful();
    }
}
