<?php

namespace AppBundle\Controller;

use AppBundle\Job\Worker\HackerNewsEmailWorker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WorkerController is responsible for scheduling and executing workers
 * @package AppBundle\Controller
 */
class WorkerController extends Controller
{
    /**
     * Show the index page for the demo
     * @return Response
     */
    public function demoIndexAction()
    {
        return $this->render('AppBundle:Demo:demo.index.html.twig');
    }

    /**
     * Schedule a hacker news email sending job
     * @param Request $request
     * @return Response
     */
    public function scheduleEmailWorkerAction(Request $request)
    {
        $email = $request->get('email');

        $scheduler = $this->get('service.job_scheduler');

        $scheduler->setWorkerClass(HackerNewsEmailWorker::class);
        $scheduler->setJobData(['email' => $email]);

        $jobId = $scheduler->schedule();

        return $this->render('AppBundle:Demo:demo.scheduled.html.twig', [
            'jobId' => $jobId
        ]);
    }
}