<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HomeController
 * @package AppBundle\Controller
 */
class HomeController extends Controller
{
    /**
     * Show the home page
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('AppBundle:Home:home.html.twig');
    }
}
