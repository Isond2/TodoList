<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use AppBundle\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class AppFixture extends Fixture  implements ContainerAwareInterface
{

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
    //password encoder
    $encoder = $this->container->get('security.password_encoder');

    //Users
        //Admin
        $admin = new User();
        $admin->setUsername('Admin');
        $admin->setPassword($encoder->encodePassword($admin, 'Admin'));
        $admin->setEmail('admin.admin@gmail.com');
        $admin->setRoles(array('ROLE_ADMIN'));
        $manager->persist($admin);

        //Jean
        $jean = new User();
        $jean->setUsername('Jean');
        $jean->setPassword($encoder->encodePassword($jean, 'Jean'));
        $jean->setEmail('jean.jean@orange.fr');
        $jean->setRoles(array('ROLE_USER'));
        $manager->persist($jean);

        //Marie
        $marie = new User();
        $marie->setUsername('Marie');
        $marie->setPassword($encoder->encodePassword($marie, 'Marie'));
        $marie->setEmail('marie.marie@laposte.net');
        $marie->setRoles(array('ROLE_USER'));
        $manager->persist($marie);

        //Pierre
        $pierre = new User();
        $pierre->setUsername('Pierre');
        $pierre->setPassword($encoder->encodePassword($pierre, 'Pierre'));
        $pierre->setEmail('pierre.pierre@hotmail.fr');
        $pierre->setRoles(array('ROLE_USER'));
        $manager->persist($pierre);

        //the annonymous user
        $annonymous = new User();
        $annonymous->setUsername('Annonymous');
        $annonymous->setPassword($encoder->encodePassword($annonymous, 'Annonymous'));
        $annonymous->setEmail('annonymous.annonymous@hotmail.fr');
        $annonymous->setRoles(array('ROLE_USER'));
        $manager->persist($annonymous);

    //Tasks
        //Admin
            //Task 1 admin
            $task1 = new Task();
            $task1->setTitle('Nouvelle tâche Admin 1');
            $task1->setContent('Texte 1');
            $task1->setUser($admin);
            $manager->persist($task1);

            //Task 2 Admin
            $task2 = new Task();
            $task2->setTitle('Nouvelle tâche Admin 2');
            $task2->setContent('Texte 2');
            $task2->setUser($admin);
            $manager->persist($task2);

            //Task 3
            $task3 = new Task();
            $task3->setTitle('Nouvelle tâche Admin 2');
            $task3->setContent('Texte 3');
            $task3->setUser($admin);
            $manager->persist($task3);

        //Jean
            //Task 1 Jean
            $task4 = new Task();
            $task4->setTitle('Nouvelle tâche Jean');
            $task4->setContent('Faire les courses');
            $task4->setUser($jean);
            $manager->persist($task4);

            //Task 2 Jean
            $task5 = new Task();
            $task5->setTitle('Réviser');
            $task5->setContent('Partie 1 et 2 cours économie gestion');
            $task5->setUser($jean);
            $manager->persist($task5);

            //Task 3 Jean
            $task6 = new Task();
            $task6->setTitle('Anniversaire Marie');
            $task6->setContent('Préparer l\'anniversaire de Marie');
            $task6->setUser($jean);
            $manager->persist($task6);

        //Marie
            //Task 1 Marie
            $task7 = new Task();
            $task7->setTitle('Footing');
            $task7->setContent('Faire mon footing matinale');
            $task7->setUser($marie);
            $manager->persist($task7);

            //Task 2 Marie
            $task8 = new Task();
            $task8->setTitle('Présetation');
            $task8->setContent('Finaliser la présentation de la semaine prochaine');
            $task8->setUser($marie);
            $manager->persist($task8);

            //Task 3 Marie
            $task9 = new Task();
            $task9->setTitle('Judo');
            $task9->setContent('Ne pas oublier d\'aller au Judo à 18h');
            $task9->setUser($marie);
            $manager->persist($task9);

        //Pierre
            //Task 1 Pierre
            $task10 = new Task();
            $task10->setTitle('Léa');
            $task10->setContent('Aller chercher Léa à l\'école');
            $task10->setUser($pierre);
            $manager->persist($task10);

            //Task 2 Pierre
            $task11 = new Task();
            $task11->setTitle('Voiture');
            $task11->setContent('Passer changer les pneus de la voiture');
            $task11->setUser($pierre);
            $manager->persist($task11);

            //Task 3 Pierre
            $task12 = new Task();
            $task12->setTitle('Jardin');
            $task12->setContent('Tondre la pelouse');
            $task12->setUser($pierre);
            $manager->persist($task12);

        //Annonymous
            //Task 1 Annonymous
            $task13 = new Task();
            $task13->setTitle('Test');
            $task13->setContent('test');
            $manager->persist($task13);

            //Task 2 Annonymous
            $task14 = new Task();
            $task14->setTitle('Test');
            $task14->setContent('test');
            $manager->persist($task14);

            //Task 3 Annonymous
            $task15 = new Task();
            $task15->setTitle('Test');
            $task15->setContent('test');
            $manager->persist($task15);





    $manager->flush();

    }
       public function getOrder()
        {
            return 1;
        }
}
?>