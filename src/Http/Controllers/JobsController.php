<?php

namespace Laravel\Horizon\Http\Controllers;

<<<<<<< HEAD
=======
use App\Models\CompletedJob;
>>>>>>> bc8be47... Customização final do Horizon
use Laravel\Horizon\Contracts\JobRepository;

class JobsController extends Controller
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
     * Get the details of a recent job by ID.
     *
     * @param  string  $id
     * @return array
     */
    public function show($id)
    {
<<<<<<< HEAD
        return (array) $this->jobs->getJobs([$id])->map(function ($job) {
            return $this->decode($job);
        })->first();
=======
        $job = $this->jobs->getJobs([$id])->map(function ($job) {
            return $this->decode($job);
        })->first();

        if(!$job){
            $job = CompletedJob::find($id)->toArray();
            $job['payload'] = json_decode($job['payload']);
        }
        return (array) ( $job );
>>>>>>> bc8be47... Customização final do Horizon
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
