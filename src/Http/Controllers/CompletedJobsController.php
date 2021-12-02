<?php

namespace Laravel\Horizon\Http\Controllers;

<<<<<<< HEAD
use Illuminate\Http\Request;
=======
use App\Models\CompletedJob;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
>>>>>>> bc8be47... Customização final do Horizon
use Laravel\Horizon\Contracts\JobRepository;

class CompletedJobsController extends Controller
{
    /**
     * The job repository implementation.
     *
     * @var \Laravel\Horizon\Contracts\JobRepository
     */
    public $jobs;

    /**
     * Create a new controller instance.
     *
     * @param  \Laravel\Horizon\Contracts\JobRepository  $jobs
     * @return void
     */
    public function __construct(JobRepository $jobs)
    {
        parent::__construct();

        $this->jobs = $jobs;
    }

    /**
     * Get all of the completed jobs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index(Request $request)
    {
<<<<<<< HEAD
        $jobs = $this->jobs->getCompleted($request->query('starting_at', -1))->map(function ($job) {
            $job->payload = json_decode($job->payload);

=======
        $offset = $request->query('starting_at', 0);
        $jobs = CompletedJob::limit(10)->offset($offset)->get()->map(function ($job) {
            $job->payload = json_decode($job->payload);
            $job->status = "completed";
>>>>>>> bc8be47... Customização final do Horizon
            return $job;
        })->values();

        return [
            'jobs' => $jobs,
<<<<<<< HEAD
            'total' => $this->jobs->countCompleted(),
=======
            'total' => count(CompletedJob::all()),
>>>>>>> bc8be47... Customização final do Horizon
        ];
    }

    /**
     * Decode the given job.
     *
     * @param  object  $job
     * @return object
     */
    protected function decode($job)
    {
        $job->payload = json_decode($job->payload);

        return $job;
    }
}
