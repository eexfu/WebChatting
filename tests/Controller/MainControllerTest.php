<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;


class MainControllerTest extends WebTestCase
{
    public function testShowMainPage()
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findById('r1');

        $client->loginUser($testUser[0]);

        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form[action="/about"] button.borderless-button'));
    }

    public function testSendMessage()
    {
        $client = static::createClient();

        $client->request('POST', '/conversation/send-message', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'userId' => 'r1',
            'chatId' => '1',
            'type' => 'text',
            'content' => 'Hello, world!',
            'sentAt' => '2024-05-27T22:00:00+00:00'
        ]));

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());
        $this->assertStringContainsString('Message stored successfully', $client->getResponse()->getContent());
    }

    public function testSearchUser()
    {
        $client = static::createClient();

        $client->request('POST', '/conversation/search-user', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'username' => 'Xun'
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        if (!empty($responseData)) {
            $this->assertArrayHasKey('id', $responseData[0]);
            $this->assertArrayHasKey('username', $responseData[0]);
            $this->assertArrayHasKey('email', $responseData[0]);
            $this->assertContains('Xun Fu', array_column($responseData, 'username'));
        }
    }

    public function testSearchUserById()
    {
        $client = static::createClient();
        $client->request('POST', '/conversation/search-userById', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'id' => 'r1'
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('id', $responseContent[0]);
        $this->assertEquals('r1', $responseContent[0]['id']);
        $this->assertArrayHasKey('username', $responseContent[0]);
    }

    public function testStartChat()
    {
        $client = static::createClient();

        $client->request('POST', '/conversation/start-chat', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'userId1' => 'r1',
            'userId2' => 'r22'
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('chatId', $responseData);
    }

    public function testAboutPageContent()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/about');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'About us');
        $this->assertSelectorTextContains('h4', 'We at StudentBridges stand for unity.');
        $this->assertSelectorExists('img[src="/images/Dijlepark.jpg"]');
    }
}