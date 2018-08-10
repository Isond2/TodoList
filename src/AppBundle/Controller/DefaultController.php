<?php

/*
 * This file is part of the Snowtricks community website.
 *
 * GOMEZ José-Adrian j.gomez17@hotmail.fr
 *
 */

namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/** DefaultController class */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     *
     * @return [<index.html>]
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }
}
