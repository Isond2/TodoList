<?php

namespace Tests\AppBundle\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
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
        $crawler = $client->request('GET', '/tasks');

        $this->assertStatusCode(200, $client);
        $this->assertContains('Nom d\'utilisateur', $crawler->filter('label')->text());

        // User = Jean -> tasks
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Jean',
            'PHP_AUTH_PW'   => 'Jean',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/tasks');
        $this->assertStatusCode(200, $client2);
        $this->assertContains('Nouvelle tâche Jean', $client2->getResponse()->getContent());

        // User = Admin -> tasks
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Admin',
            'PHP_AUTH_PW'   => 'Admin',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/tasks');
        $this->assertStatusCode(200, $client2);
        $this->assertContains('Nouvelle tâche Admin 1', $client2->getResponse()->getContent());
    }

    public function testCreate()
    {

        // Annonymous = redirect to login
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/tasks/create');

        $this->assertStatusCode(200, $client);
        $this->assertContains('Nom d\'utilisateur', $crawler->filter('label')->text());

        // Jean = 200 -> Create Task
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Jean',
            'PHP_AUTH_PW'   => 'Jean',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/tasks/create');
        $this->assertStatusCode(200, $client2);
        $this->assertContains('Title', $crawler->filter('label')->text());

        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'Tâche test Jean';
        $form['task[content]'] = 'Content test Jean';
        $client2->submit($form);

        $this->assertContains('La tâche a été bien été ajoutée.', $client2->getResponse()->getContent()); // -> Task successfuly created
        $this->assertContains('Tâche test Jean', $client2->getResponse()->getContent());
    }

    public function testEdit()
    {
        // Annonymous = redirect to login
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/tasks/4/edit');

        $this->assertStatusCode(200, $client);
        $this->assertContains('Nom d\'utilisateur', $crawler->filter('label')->text());

        // Jean = 200 -> Edit task
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Jean',
            'PHP_AUTH_PW'   => 'Jean',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/tasks/4/edit');
        $this->assertStatusCode(200, $client2);
        $this->assertContains('Title', $crawler->filter('label')->text());

        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = 'Edit task Jean';
        $form['task[content]'] = 'Edit test';
        $client2->submit($form);

        $this->assertContains('La tâche a bien été modifiée.', $client2->getResponse()->getContent()); // -> Task successfuly edited
        $this->assertContains('Edit task Jean', $client2->getResponse()->getContent());

        $crawler = $client2->request('GET', '/tasks/15/edit'); // User try to edit an someone else's task
        $this->assertContains('Vous ne pouvez pas modifier cette tâche', $client2->getResponse()->getContent()); // -> Unauthorized
    }


    public function testDelete()
    {
        // Annonymous = redirect to login
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/tasks/5/delete');

        $this->assertStatusCode(200, $client);
        $this->assertContains('Nom d\'utilisateur', $crawler->filter('label')->text());

        // Jean = 200 -> Edit task
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Jean',
            'PHP_AUTH_PW'   => 'Jean',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/tasks/5/delete');
        $this->assertStatusCode(200, $client2);
        $this->assertContains('La tâche a bien été supprimée.', $client2->getResponse()->getContent()); // -> Task successfuly deleted by User

        $client3 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Admin',
            'PHP_AUTH_PW'   => 'Admin',));
        $client3->followRedirects();
        $crawler = $client3->request('GET', '/annoymmous-attachement-tasks'); // Admin link all tasks without owners to the Annonymous user

        $crawler = $client2->request('GET', '/tasks/15/delete'); // User try to delete Annonymous User's task
        $this->assertContains('Vous ne pouvez pas supprimer cette tâche', $client2->getResponse()->getContent()); //-> Unauthorized

        $crawler = $client3->request('GET', '/tasks/15/delete'); // Admin try to delete Annonymous User's task
        $this->assertContains('La tâche a bien été supprimée.', $client3->getResponse()->getContent()); // -> Task successfuly deleted by Admin

    }


    public function testToggle()
    {
        // Annonymous = redirect to login
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/tasks/6/toggle');

        $this->assertStatusCode(200, $client);
        $this->assertContains('Nom d\'utilisateur', $crawler->filter('label')->text());

        // Jean = 200 -> Mark task as done
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Jean',
            'PHP_AUTH_PW'   => 'Jean',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/tasks/6/toggle');
        $this->assertStatusCode(200, $client2);

        $this->assertContains('Marquer non terminée', $client2->getResponse()->getContent()); // -> Task marked as done successfuly

        $crawler = $client2->request('GET', '/tasks/15/toggle');
        $this->assertContains('Vous ne pouvez pas marquer cette tâche comme faite.', $client2->getResponse()->getContent());

    }


    public function testAnnonymousList()
    {
        // Annonymous = redirect to login
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/annoymmous-tasks');

        $this->assertStatusCode(200, $client);
        $this->assertContains('Nom d\'utilisateur', $crawler->filter('label')->text());

        // User = forbidden
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Jean',
            'PHP_AUTH_PW'   => 'Jean',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/annoymmous-tasks');
        $this->assertStatusCode(403, $client2);

        // Admin = 200 -> List of the tasks linked to the annonymous user
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Admin',
            'PHP_AUTH_PW'   => 'Admin',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/annoymmous-tasks');
        $this->assertStatusCode(200, $client2);
        $this->assertContains('pas encore de tâche enregistrée.', $client2->getResponse()->getContent());

    }

    public function testAnnonymousAttachement()
    {
        // Annonymous = redirect to login
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/annoymmous-attachement-tasks');

        $this->assertStatusCode(200, $client);
        $this->assertContains('Nom d\'utilisateur', $crawler->filter('label')->text());

        // User = forbidden
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Jean',
            'PHP_AUTH_PW'   => 'Jean',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/annoymmous-attachement-tasks');
        $this->assertStatusCode(403, $client2);

        // Admin = 200 -> Refresh the annonymous user's tasks
        $client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Admin',
            'PHP_AUTH_PW'   => 'Admin',));
        $client2->followRedirects();
        $crawler = $client2->request('GET', '/annoymmous-attachement-tasks');
        $this->assertStatusCode(200, $client2);
        $this->assertContains('La liste est désormais à jours', $client2->getResponse()->getContent()); // -> list updated
        $this->assertContains('Test', $client2->getResponse()->getContent());

    }

}
