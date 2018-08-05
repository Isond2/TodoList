<?php

namespace Tests\AppBundle\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
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

    public function testList()
    {

        // Annonymous = redirect to login
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/users');

        $this->assertStatusCode(200, $client);
        $this->assertContains('Nom d\'utilisateur', $crawler->filter('label')->text()); // -> redirect to login page

        // User = forbidden
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Jean',
            'PHP_AUTH_PW'   => 'Jean',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/users');
        $this->assertStatusCode(403, $client2); // -> forbidden

        // Admin = 200 -> Users List
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Admin',
            'PHP_AUTH_PW'   => 'Admin',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/users');
        $this->assertStatusCode(200, $client);
        $this->assertContains('Liste des utilisateurs', $crawler->filter('h1')->text()); // -> User's list
    }

    public function testCreate()
    {

        // Annonymous = redirect to login
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/users/create');

        $this->assertStatusCode(200, $client);
        $this->assertContains('Nom d\'utilisateur', $crawler->filter('label')->text()); // -> redirect to login page

        // User = forbidden
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Jean',
            'PHP_AUTH_PW'   => 'Jean',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/users/create');
        $this->assertStatusCode(403, $client2); // -> forbidden

        // Admin = 200 -> Create User
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Admin',
            'PHP_AUTH_PW'   => 'Admin',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/users/create');
        $this->assertStatusCode(200, $client);
        $this->assertContains('Créer un utilisateur', $crawler->filter('h1')->text());

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'NewUser';
        $form['user[password][first]'] = 'NewUser';
        $form['user[password][second]'] = 'NewUser';
        $form['user[email]'] = 'newuser@email.fr';
        $form['user[roles][0]']->tick();
        $client2->submit($form);

        $this->assertContains('utilisateur a bien été ajouté.', $client2->getResponse()->getContent()); // -> User successfuly created

        $crawler = $client2->request('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'NewUser1';
        $form['user[password][first]'] = 'NewUser1';
        $form['user[password][second]'] = 'NewUser1';
        $form['user[email]'] = 'newuser1@email.fr';
        $client2->submit($form);

        $this->assertContains('utilisateur a bien été ajouté.', $client2->getResponse()->getContent()); // -> NewUser1 created with default USER_ROLE
        $crawler = $client2->request('GET', '/users');
        $this->assertSame(1, $crawler->filter('span:contains("ROLE_USER")')->eq(5)->count()); // -> User's list -> NewUser1 -> USER_ROLE (->eq() starts at 0 so its the 6th user)

    }

    public function testEdit()
    {
                // Annonymous = redirect to login
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/users/2/edit');

        $this->assertStatusCode(200, $client);
        $this->assertContains('Nom d\'utilisateur', $crawler->filter('label')->text()); // -> redirect to login page

        // User = forbidden
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Jean',
            'PHP_AUTH_PW'   => 'Jean',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/users/2/edit');
        $this->assertStatusCode(403, $client2); // -> forbidden

        // Admin = 200 -> Create User
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Admin',
            'PHP_AUTH_PW'   => 'Admin',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/users/2/edit');
        $this->assertStatusCode(200, $client);
        $this->assertContains('Modifier', $crawler->filter('h1')->text());

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = 'Jean';
        $form['user[password][first]'] = 'Jean';
        $form['user[password][second]'] = 'Jean';
        $form['user[email]'] = 'editemail@email.com';
        $form['user[roles][0]']->tick();
        $client2->submit($form);

        $this->assertContains('utilisateur a bien été modifié', $client2->getResponse()->getContent()); // -> User successfuly edited
    }

}
