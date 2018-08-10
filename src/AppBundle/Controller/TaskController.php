<?php

/*
 * This file is part of the Snowtricks community website.
 *
 * GOMEZ José-Adrian j.gomez17@hotmail.fr
 *
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Form\TaskType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/** TaskController class */
class TaskController extends Controller
{
    /**
     * List of the tasks of the current user ( ROLE_ADMIN )
     *
     * @Route("/tasks", name="task_list")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @return [<task/list.html>]
     */
    public function listAction()
    {
        $user = $this->getUser();

        return $this->render('task/list.html.twig', ['tasks' => $this->getDoctrine()->getRepository('AppBundle:Task')->findBy(['user' => $user])]);
    }

    /**
     * Create a task ( USER_ROLE )
     *
     * @Route("/tasks/create", name="task_create")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param request $request
     *
     * @return [<task/create.html>]
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
     * Edit a task ( USER_ROLE )
     *
     * @Route("/tasks/{id}/edit", name="task_edit")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param task    $task
     * @param request $request
     *
     * @return [<task/edit.html>]
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
        }
        $this->addFlash('error', 'Vous ne pouvez pas modifier cette tâche.');

        return $this->redirectToRoute('task_list');
    }

    /**
     * Toogle a task = mark a task as done ( USER_ ROLE )
     *
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param task $task
     *
     * @return [<redirect to task_list>]
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
        }

        $this->addFlash('error', 'Vous ne pouvez pas marquer cette tâche comme faite.');


        return $this->redirectToRoute('task_list');
    }

    /**
     * Delete a task ( USER_ROLE )
     * Exeption for the annonymous user's tasks , only ROLE_ADMIN can delete them.
     *
     * @Route("/tasks/{id}/delete", name="task_delete")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param task $task
     *
     * @return [<redirect to task_list>]
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
     * Annonymous user's task list (ROLE_ADMIN Only)
     *
     * @Route("/annoymmous-tasks", name="annoymmous_task_list")
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return [<redirect to the annonymous user task_list>]
     */
    public function annonymousListAction()
    {
        $announymousUser = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(['username' => 'Annonymous']);

        return $this->render('task/list.html.twig', ['tasks' => $this->getDoctrine()->getRepository('AppBundle:Task')->findBy(['user' => $announymousUser])]);
    }

    /**
     * Linking non-owner tasks to the anonymous user (ROLE_ADMIN Only)
     *
     * @Route("/annoymmous-attachement-tasks", name="annoymmous_task_attachement")
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return [<redirect to the annonymous user task_list>]
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
