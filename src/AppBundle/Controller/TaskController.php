<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Form\TaskType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class TaskController extends Controller
{
    /**
     * @Route("/tasks", name="task_list")
     * @Security("has_role('ROLE_USER')")
     */
    public function listAction()
    {
        $user = $this->getUser();
        return $this->render('task/list.html.twig', ['tasks' => $this->getDoctrine()->getRepository('AppBundle:Task')->findBy(['user' => $user])]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $task->setUser($this->getUser());

            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction(Task $task, Request $request)
    {
        $currentUser = $this->getUser();
        $taskUser = $task->getUser();

        if ($taskUser == $currentUser) {

            $form = $this->createForm(TaskType::class, $task);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', 'La tâche a bien été modifiée.');

                return $this->redirectToRoute('task_list');
            }

            return $this->render('task/edit.html.twig', [
                'form' => $form->createView(),
                'task' => $task,
            ]);

        } else {

            $this->addFlash('error', 'Vous ne pouvez pas modifier cette tâche.');
        }
        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * @Security("has_role('ROLE_USER')")
     */
    public function toggleTaskAction(Task $task)
    {

        $currentUser = $this->getUser();
        $taskUser = $task->getUser();

        if ($taskUser == $currentUser) {

            $task->toggle(!$task->isDone());
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

            return $this->redirectToRoute('task_list');

        } else {

            $this->addFlash('error', 'Vous ne pouvez pas marquer cette tâche comme faite.');
        }
        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteTaskAction(Task $task)
    {
        $currentUser = $this->getUser();
        $taskUser = $task->getUser();

        if ($taskUser == $currentUser) {

            $em = $this->getDoctrine()->getManager();
            $task->setUser(null);
            $em->remove($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a bien été supprimée.');

        } elseif ($task->getUser()->getUsername() === 'Annonymous' && $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {

            $em = $this->getDoctrine()->getManager();
            $task->setUser(null);
            $em->remove($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a bien été supprimée.');

        } else {

            $this->addFlash('error', 'Vous ne pouvez pas supprimer cette tâche');
        }

        return $this->redirectToRoute('task_list');
    }


    /**
     * Liste des tâches liées à l'utilisateur annonyme (ROLE_ADMIN Only)
     *
     * @Route("/annoymmous-tasks", name="annoymmous_task_list")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function annonymousListAction()
    {
        $announymousUser = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(['username' => 'Annonymous']);
        return $this->render('task/list.html.twig', ['tasks' => $this->getDoctrine()->getRepository('AppBundle:Task')->findBy(['user' => $announymousUser])]);
    }

    /**
     * Liaison des tâches sans propriétaires à l'utilisateur annonyme (ROLE_ADMIN Only)
     *
     * @Route("/annoymmous-attachement-tasks", name="annoymmous_task_attachement")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function annonymousAttachementAction()
    {
        $annonymousTasks = $this->getDoctrine()->getRepository('AppBundle:Task')->findBy(['user' => null]);
        $announymousUser = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(['username' => 'Annonymous']);
        foreach ($annonymousTasks as $task) {

            $em = $this->getDoctrine()->getManager();
            $task->setUser($announymousUser);

            $em->persist($task);
            $em->flush();
        }
        $this->addFlash('success', 'La liste est désormais à jours');

        return $this->redirectToRoute('annoymmous_task_list');
    }
}
