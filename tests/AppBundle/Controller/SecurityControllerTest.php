<?php

namespace Tests\AppBundle\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{

    protected function setUp()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        if (!isset($metadatas)) {
            $metadatas = $em->getMetadataFactory()->getAllMetadata();
        }
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropDatabase();
        if (!empty($metadatas)) {
            $schemaTool->createSchema($metadatas);
        }
        $this->postFixtureSetup();

        $fixtures = array(
            'AppBundle\DataFixtures\ORM\AppFixture',
        );
        $this->loadFixtures($fixtures);
    }

    public function testLogin()
    {
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/login');

        // False User
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'FalseUsername';
        $form['_password'] = 'FalsePassword';
        $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Nom d\'utilisateur', $crawler->filter('label')->text());

        // Real User
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'Admin';
        $form['_password'] = 'Admin';
        $client->submit($form);

        $crawler = $client->getCrawler();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Connecté en tant que : Admin', $crawler->filter('span')->text());
    }

    public function testLoginCheck()
    {
        // This code is never executed.
    }

    public function testLogoutCheck()
    {
        // This code is never executed.
    }
}
