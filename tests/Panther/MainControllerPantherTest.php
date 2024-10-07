<?php

namespace App\Tests\Controller;

use Symfony\Component\Panther\PantherTestCase;

class MainControllerPantherTest extends PantherTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createPantherClient(
            [],
            [],
            [
                'capabilities' => [
                    'goog:loggingPrefs' => [
                        'browser' => 'ALL', // calls to console.* methods
                        'performance' => 'ALL', // performance data
                    ],
                ],
            ]);

        $crawler = $this->client->request('GET', 'https://a23www106.studev.groept.be/public/');

        try {
            $this->client->takeScreenshot(__DIR__ . '/screenshots/screenshot.png');
            $crawler->filter('input[type=email]')->sendKeys('xun.fu@student.kuleuven.be');
            $crawler->filter('input[type=password]')->sendKeys(123123);
            $crawler->filter('.submitButton')->click();
        } catch (\Exception $e){
            throw $e;
        }
    }

    protected function tearDown(): void
    {
        if ($this->client) {
            $this->client->quit();
        }
        parent::tearDown();
    }

    public function testPageLoadsCorrectly()
    {
        try {
            $this->client->waitFor('.navbar-text', 10);
            $this->assertSelectorTextContains('a.navbar-text', 'StudentBridges');
        } catch (\Throwable $e) {
            echo "Failed to find the element. Error: " . $e->getMessage();
            file_put_contents('page_content.html', $this->client->getPageSource());
            throw $e;
        }
    }

    public function testSearchUser(){
        $crawler = $this->client->refreshCrawler();

        try{
            $this->client->takeScreenshot(__DIR__ . '/screenshots/start_search_screenshot.png');
            $crawler->filter('#search-box')->sendKeys('xun');
            $crawler->filter('#searchBtn')->click();

            $found = false;
            for ($i = 0; $i < 10; $i++) {
                $crawler = $this->client->refreshCrawler();
                $this->client->waitFor('#chatList .chat-button .user-name');
                $this->client->takeScreenshot(__DIR__ . '/screenshots/search_screenshot.png');
                $chatButtons = $crawler->filter('#chatList .chat-button .user-name');
                foreach ($chatButtons as $button) {
                    if (strpos(strtolower($button->getText()), 'xun') !== false) {
                        $found = true;
                        break 2;
                    }
                }
                sleep(1);
            }

            $this->assertTrue($found, 'At least one user with the name "Xun" should be present in the search results.');

            $this->client->executeScript("document.querySelector('.chat-button').click();");
        }
        catch (\Exception $e) {
            $this->client->takeScreenshot(__DIR__ . '/screenshots/failure_search_screenshot.png');
            throw $e;
        }
    }

    public function testSendMessage(){
        try {
            $crawler = $this->client->refreshCrawler();
            $this->client->executeScript("document.querySelector('.chat-button').click();");
            $this->client->waitForVisibility('#message');
            $crawler->filter('#message')->sendKeys('Hi');
            $crawler->filter('#sendBtn')->click();

            $this->client->waitFor('.cright.cmsg');
            $lastElement = $crawler->filter('.cright.cmsg .content')->last();
            $this->assertStringContainsString('Hi', $lastElement->text(), 'The last .cright.cmsg .content should contain "Hi"');

            sleep(2);
            $consoleLogs = $this->client->getWebDriver()->manage()->getLog('browser');
            $sendMessage = false;
            $storeMessage = false;

            foreach ($consoleLogs as $log) {
                var_dump($log['message']);
                if (strpos($log['message'], 'send message successfully') !== false) {
                    $sendMessage = true;
                }
                if (strpos($log['message'], 'store message successfully') !== false) {
                    $storeMessage = true;
                }
            }

            $this->assertTrue($sendMessage, 'Console log should contain "send message successfully"');
            $this->assertTrue($storeMessage, 'Console log should contain "store message successfully"');
        } catch (\Exception $e) {
            $this->client->takeScreenshot(__DIR__ . '/screenshots/failure_screenshot.png');
            throw $e;
        }
    }

    public function testReturnButtonFunctionality()
    {
        $this->testSearchUser();
        $this->client->waitForVisibility('#returnBtn');
        $this->client->getCrawler()->filter('#returnBtn')->click();
        $this->assertEmpty($this->client->getCrawler()->filter('#search-box')->text());
        var_dump($this->client->getCrawler()->filter('#chatList .chat-button .user-name')->text());
        $this->assertTrue($this->client->getCrawler()->filter('#chatList .chat-button .user-name')->text() === 'Fu Xun');
    }

    public function testNotificationFunctionality()
    {
        sleep(1);
        $script = <<<JS
            (async function() {
                try {
                    const response = await fetch('https://a23www106.studev.groept.be/public/send-notification', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            "type": "message",
                            "sender": {
                                "id": "r0928034",
                                "username": "Fu Xun",
                                "email": "fu.xun@student.kuleuven.be",
                                "icon": "SHFADHFOHDOFBJUOBWEBFJKDB"
                            },
                            "receiver": {
                                "id": 51,
                                "userId": "r0928025",
                                "username": "Xun Fu",
                                "email": "xun.fu@student.kuleuven.be",
                                "icon": "DFLHOASNVGBOQEBQOBQIFUG"
                            },
                            "content": "Hello",
                            "sentAt": "2024-5-29"
                        })
                    });
                    if (!response.ok) {
                        throw new Error('Network response was not ok.');
                    }
                    const data = await response.json();
                    window.fetchResponseData = data;
                } catch (error) {
                    console.error('Fetch error:', error);
                    window.fetchResponseData = null;
                }
            })();
        JS;

        $this->client->executeScript($script);

        $maxWaitTime = 10;
        for ($i = 0; $i < $maxWaitTime; $i++) {
            $ajaxComplete = $this->client->executeScript('return window.ajaxComplete;');
            if ($ajaxComplete) {
                break;
            }
            sleep(1);
        }

        $responseData = $this->client->executeScript('return window.fetchResponseData;');
        var_dump($responseData);
        $this->client->takeScreenshot(__DIR__ . '/screenshots/notify.png');

        $this->client->waitForVisibility('.notification-dot',10);

        $this->client->takeScreenshot(__DIR__ . '/screenshots/notify.png');
        $isVisible = $this->client->executeScript("return window.getComputedStyle(document.querySelector('.notification-dot')).display !== 'none';");
        $this->assertTrue($isVisible, "Notification dot should be visible");

        $this->client->executeScript("document.querySelector('.chat-button').click();");
        $this->client->waitFor('.cright.cmsg');
        $lastElement = $this->client->refreshCrawler()->filter('.cleft.cmsg .content')->last();
        $this->assertStringContainsString('Hello', $lastElement->text(), 'The last .cright.cmsg .content should contain "Hi"');

    }

    public function testMaxChars()
    {
        $crawler = $this->client->refreshCrawler();

        $this->client->executeScript("document.querySelector('.chat-button').click();");
        $this->client->waitForVisibility('#message');
        $message = $crawler->filter('#message');
        $longText = str_repeat('a', 201);

        $message->sendKeys($longText);

        try {
            $alert = $this->client->switchTo()->alert();
            $alertText = $alert->getText();
            $this->assertStringContainsString('Error: Message content cannot be more than 200 chars.', $alertText);
            $alert->accept();
        } catch (\Exception $e) {
            throw $e;
        }

        $length = $this->client->executeScript('return document.querySelector("#message").innerText.length');
        $this->assertEquals(200, $length);
    }

    public function testStoreDataFunction()
    {
        $client = $this->client;

        $client->waitFor('footer');

        $chatList = $client->executeScript('return JSON.parse(sessionStorage.getItem("chatList"));');
        $user = $client->executeScript('return JSON.parse(sessionStorage.getItem("user"));');
        $serverIp = $client->executeScript('return JSON.parse(sessionStorage.getItem("serverIp"));');
        $webSocketIp = $client->executeScript('return JSON.parse(sessionStorage.getItem("webSocketIp"));');
        $messages = $client->executeScript('return JSON.parse(sessionStorage.getItem("messages"));');
        $chat = $client->executeScript('return JSON.parse(sessionStorage.getItem("chat"));');

        $chatList_expect = [
            [
                "email" => "fu.xun@student.kuleuven.be",
                "icon"=> "",
                "id"=> 51,
                "userId" => "r0928034",
                "username"=> "Fu Xun"
            ],
            [
                "email" => "koenraad.goddefroy@student.kuleuven.be",
                "icon"=> "",
                "id"=> 52,
                "userId" => "r0820755",
                "username"=> "Koenraad Goddefroy"
            ],
        ];
        $user_expect = [
            "email" => "xun.fu@student.kuleuven.be",
            "icon"=> "",
            "id"=> "r0928025",
            "username"=> "Xun Fu"
        ];
        $serverIp_expect = [
            "url"=> "https://a23www106.studev.groept.be/public"
        ];
        $webSocketIp_expect = [
            "url"=> "wss://a23www106.studev.groept.be:2346"
        ];
        $this->assertNotEmpty($chatList);
        $this->assertEquals($chatList_expect, $chatList);
        $this->assertNotEmpty($user);
        $this->assertEquals($user_expect, $user);
        $this->assertNotEmpty($serverIp);
        $this->assertEquals($serverIp_expect, $serverIp);
        $this->assertNotEmpty($webSocketIp);
        $this->assertEquals($webSocketIp_expect, $webSocketIp);
        $this->assertNotEmpty($messages);
        $this->assertEquals([], $chat);

        sleep(2);
        $consoleLogs = $client->getWebDriver()->manage()->getLog('browser');
        $startPusherFound = false;
        $endPusherFound = false;

        foreach ($consoleLogs as $log) {
            var_dump($log['message']);
            if (strpos($log['message'], 'start pusher') !== false) {
                $startPusherFound = true;
            }
            if (strpos($log['message'], 'end pusher') !== false) {
                $endPusherFound = true;
            }
        }

        $this->assertTrue($startPusherFound, 'Console log should contain "start pusher"');
        $this->assertTrue($endPusherFound, 'Console log should contain "end pusher"');
    }
}
