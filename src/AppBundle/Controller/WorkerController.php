<?php

namespace AppBundle\Controller;

use AppBundle\Util\JsonResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Job\Worker\HackerNewsEmailWorker;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class WorkerController is responsible for scheduling and executing workers
 * @package AppBundle\Controller
 */
class WorkerController extends Controller
{
    use JsonResponseTrait;

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

        $runner = $this->get('service.job_runner');

        $runner->setWorkerClass(HackerNewsEmailWorker::class);
        $runner->setJobData(['email' => $email]);

        $jobId = $runner->schedule();

        return $this->render('AppBundle:Demo:demo.scheduled.html.twig', [
            'jobId' => $jobId
        ]);
    }

    /**
     * Get the status of a job
     * @param string $jobId Unique identifier for this particular job
     * @return JsonResponse
     */
    public function getWorkerStatusAction($jobId)
    {
        $runner = $this->get('service.job_runner');
        $runner->setJobId($jobId);
        $status = $runner->getStatus();

        return $this->jsonResponse([
            'status' => $status
        ]);
    }

    /**
     * Get any data that this job may have produced
     * @param string $jobId Unique identifier for this particular job
     * @return JsonResponse
     */
    public function getWorkerDataAction($jobId)
    {
        $broker = $this->get('service.data_broker');
        $broker->connect();
        $broker->setJobId($jobId);
        $data = $broker->get();

        $data = $this->decodeJson($data);
        $data = $this->decodeJson($data);

        return $this->jsonResponse($data);
    }
}