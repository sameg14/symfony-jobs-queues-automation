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
 * This controller also supports endpoints to get the status of a job and to return any data that this job may have produced
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
        // Get the email address entered by the user
        $email = $request->get('email');

        // Get the job runner service from the service container AppBundle\Resources\config\services.yml
        $runner = $this->get('service.job_runner');

        // Tell the runner which worker will handle this job
        $runner->setWorkerClass(HackerNewsEmailWorker::class);

        // What data do we need to associate with the job to give to the worker
        $runner->setJobData(['email' => $email]);

        // Scheduling a job for deferred execution will return a jobId
        $jobId = $runner->schedule();

        // Render a template with the jobId and some other actions
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