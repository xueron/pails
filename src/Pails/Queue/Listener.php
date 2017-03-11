<?php
namespace Pails\Queue;

use Exception;
use Pails\Exception\Handler;
use Pails\Injectable;
use Throwable;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * Class Listener
 * @package Pails\Queue
 */
class Listener extends Injectable
{

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Handler
     */
    protected $exceptions;

    /**
     * Indicates if the worker should exit.
     *
     * @var bool
     */
    protected $shouldQuit = false;

    /**
     * Indicates if the worker is paused.
     *
     * @var bool
     */
    protected $paused = false;

    /**
     * Create a new queue worker.
     *
     * @param string $queueName Name of queue
     */
    public function __construct($queueName)
    {
        // 获得队列
        $this->queue = $this->di->get(Queue::class, [$queueName]);

        // 注册异常处理器
        $this->exceptions = $this->di->get(Handler::class);
    }

    /**
     * Listen to the given queue in a loop.
     *
     * @param  ListenerOptions $options
     * @return void
     */
    public function daemon(ListenerOptions $options)
    {
        $this->listenForSignals();

        $lastRestart = $this->getTimestampOfLastQueueRestart();

        $this->getEventsManager()->fire("listener:beforeDaemonLoop", $this);

        while (true) {
            // Before reserving any jobs, we will make sure this queue is not paused and
            // if it is we will just pause this worker for a given amount of time and
            // make sure we do not need to kill this worker process off completely.
            if (! $this->daemonShouldRun($options)) {
                echo "parsed\n";
                $this->pauseWorker($options, $lastRestart);

                continue;
            }

            // First, we will attempt to get the next job off of the queue. We will also
            // register the timeout handler and reset the alarm for this job so it is
            // not stuck in a frozen state forever. Then, we can fire off this job.
            $job = $this->getNextJob($options);

            // If the daemon should run (not in maintenance mode, etc.), then we can run
            // fire off this job for processing. Otherwise, we will need to sleep the
            // worker so no more jobs are processed until they should be processed.
            if ($job) {
                $this->registerTimeoutHandler($job, $options);

                $this->runJob($job, $options);
            } else {
                $this->sleep($options->sleep);
            }

            // Finally, we will check to see if we have exceeded our memory limits or if
            // the queue should restart based on other indications. If so, we'll stop
            // this worker and let whatever is "monitoring" it restart the process.
            $this->stopIfNecessary($options, $lastRestart);
        }

        $this->getEventsManager()->fire("listener:afterDaemonLoop", $this);
    }

    /**
     * Process the next job on the queue.
     *
     * @param  ListenerOptions $options
     * @return void
     */
    public function runNextJob(ListenerOptions $options)
    {
        $job = $this->getNextJob($options);

        // If we're able to pull a job off of the stack, we will process it and then return
        // from this method. If there is no job on the queue, we will "sleep" the worker
        // for the specified number of seconds, then keep processing jobs after sleep.
        if ($job) {
            return $this->runJob($job, $options);
        }

        $this->sleep($options->sleep);
    }

    /**
     * Process the given job.
     *
     * @param  Job $job
     * @param  ListenerOptions $options
     * @return void
     */
    protected function runJob(Job $job, ListenerOptions $options)
    {
        try {
            $this->process($job, $options);
        } catch (Exception $e) {
            $this->exceptions->report($e);
        } catch (Throwable $e) {
            $this->exceptions->report(new FatalThrowableError($e));
        }
    }

    /**
     * Process a given job from the queue.
     *
     * @param  Job $job
     * @param  ListenerOptions $options
     * @return void
     *
     * @throws \Throwable
     */
    protected function process(Job $job, ListenerOptions $options)
    {
        try {
            // First we will raise the before job event and determine if the job has already ran
            // over the its maximum attempt limit, which could primarily happen if the job is
            // continually timing out and not actually throwing any exceptions from itself.
            $this->raiseBeforeJobEvent($job);

            $this->markJobAsFailedIfAlreadyExceedsMaxAttempts($job, (int) $options->maxTries);

            $this->di->getShared('queue:' . $this->queue->getName())->process($job, $options);

            $this->raiseAfterJobEvent($job);

        } catch (Exception $e) {
            $this->handleJobException($job, $options, $e);
        } catch (Throwable $e) {
            $this->handleJobException($job, $options, new FatalThrowableError($e));
        }
    }

    /**
     * Register the worker timeout handler (PHP 7.1+).
     *
     * @param  Job $job
     * @param  ListenerOptions $options
     * @return void
     */
    protected function registerTimeoutHandler(Job $job, ListenerOptions $options)
    {
        if ($options->timeout > 0 && $this->supportsAsyncSignals()) {
            // We will register a signal handler for the alarm signal so that we can kill this
            // process if it is running too long because it has frozen. This uses the async
            // signals supported in recent versions of PHP to accomplish it conveniently.
            pcntl_signal(SIGALRM, function () {
                $this->kill(1);
            });

            pcntl_alarm($this->timeoutForJob($job, $options) + $options->sleep);
        }
    }

    /**
     * Get the appropriate timeout for the given job.
     *
     * @param  Job|null $job
     * @param  ListenerOptions $options
     * @return int
     */
    protected function timeoutForJob(Job $job, ListenerOptions $options)
    {
        return $job && !is_null($job->timeout()) ? $job->timeout() : $options->timeout;
    }

    /**
     * Determine if the daemon should process on this iteration.
     *
     * @param  ListenerOptions $options
     * @return bool
     */
    protected function daemonShouldRun(ListenerOptions $options)
    {
        return !((!$options->force) || $this->paused);
    }

    /**
     * Pause the worker for the current loop.
     *
     * @param  ListenerOptions $options
     * @param  int $lastRestart
     * @return void
     */
    protected function pauseWorker(ListenerOptions $options, $lastRestart)
    {
        $this->sleep($options->sleep > 0 ? $options->sleep : 1);

        $this->stopIfNecessary($options, $lastRestart);
    }

    /**
     * Stop the process if necessary.
     *
     * @param  ListenerOptions $options
     * @param $lastRestart
     */
    protected function stopIfNecessary(ListenerOptions $options, $lastRestart)
    {
        if ($this->shouldQuit) {
            $this->kill();
        }

        if ($this->memoryExceeded($options->memory)) {
            $this->stop(12);
        } elseif ($this->queueShouldRestart($lastRestart)) {
            $this->stop();
        }
    }

    /**
     * Get the next job from the queue connection.
     * @param ListenerOptions $options
     * @return Job
     */
    protected function getNextJob(ListenerOptions $options)
    {
        try {

            $this->eventsManager->fire("listener:beforeGetJob", $this);

            $job = $this->queue->pop($options);

            $this->eventsManager->fire("listener:afterGetJob", $this, $job);

            return $job;

        } catch (Exception $e) {
            $this->exceptions->report($e);
        } catch (Throwable $e) {
            $this->exceptions->report(new FatalThrowableError($e));
        }
    }

    /**
     * Handle an exception that occurred while the job was running.
     *
     * @param  Job $job
     * @param  ListenerOptions $options
     * @param  \Exception $e
     * @return void
     *
     * @throws \Exception
     */
    protected function handleJobException(Job $job, ListenerOptions $options, $e)
    {
        try {
            // First, we will go ahead and mark the job as failed if it will exceed the maximum
            // attempts it is allowed to run the next time we process it. If so we will just
            // go ahead and mark it as failed now so we do not have to release this again.
            $this->markJobAsFailedIfWillExceedMaxAttempts($job, (int)$options->maxTries, $e);

            $this->raiseExceptionOccurredJobEvent($job, $e);
        } finally {
            if (!$job->isDeleted()) {
                $job->release($options->delay);
            }
        }

        throw $e;
    }

    /**
     * Mark the given job as failed if it has exceeded the maximum allowed attempts.
     *
     * This will likely be because the job previously exceeded a timeout.
     *
     * @param  Job $job
     * @param  int $maxTries
     * @return void
     */
    protected function markJobAsFailedIfAlreadyExceedsMaxAttempts(Job $job, $maxTries)
    {
        if ($maxTries === 0 || $job->attempts() <= $maxTries) {
            return;
        }

        $this->failJob($job, $e = new \RuntimeException('A queued job has been attempted too many times. The job may have previously timed out.'));

        throw $e;
    }

    /**
     * Mark the given job as failed if it has exceeded the maximum allowed attempts.
     *
     * @param  Job $job
     * @param  int $maxTries
     * @param  \Exception $e
     * @return void
     */
    protected function markJobAsFailedIfWillExceedMaxAttempts(Job $job, $maxTries, $e)
    {
        if ($maxTries > 0 && $job->attempts() >= $maxTries) {
            $this->failJob($job, $e);
        }
    }

    /**
     * 说明：Job failed 只当Job处理超过一定次数，仍旧没有处理掉的。如果是处理程序异常，Job会被释放，等待下一次处理。
     *
     * 这里是统一的将超过处理次数的消息摘出来，从消息队列删除。防止出现大量积压。
     *
     * Mark the given job as failed and remove job from queue and raise the relevant event.
     *
     * @param  Job $job
     * @param  \Exception $e
     * @return void
     */
    protected function failJob(Job $job, $e = null)
    {
        // 将失败的消息单独处理，放入失败数据库等。
        $job->markAsFailed();
        try {
            if (!$job->isDeleted()) {
                $job->delete();
            }
        } finally {
            $this->raiseFailedJobEvent($job, $e ?: new \Exception("Job failed"));
        }
    }

    /**
     * Raise the before queue job event.
     *
     * @param  Job $job
     * @return void
     */
    protected function raiseBeforeJobEvent(Job $job)
    {
        $this->getEventsManager()->fire('worker:beforeJobHandle', $this, $job);
    }

    /**
     * Raise the after queue job event.
     *
     * @param  Job $job
     * @return void
     */
    protected function raiseAfterJobEvent(Job $job)
    {
        $this->getEventsManager()->fire('worker:afterJobHandle', $this, $job);
    }

    /**
     * Raise the exception occurred queue job event.
     *
     * @param  Job $job
     * @param  \Exception $e
     * @return void
     */
    protected function raiseExceptionOccurredJobEvent(Job $job, $e)
    {
        $this->getEventsManager()->fire('worker:jobException', $this, [$job, $e]);
    }

    /**
     * Raise the failed queue job event.
     *
     * @param  Job $job
     * @param  \Exception $e
     * @return void
     */
    protected function raiseFailedJobEvent(Job $job, $e)
    {
        $this->getEventsManager()->fire('worker:jobFailed', $this, [$job, $e]);
    }

    /**
     * Determine if the queue worker should restart.
     *
     * @param  int|null $lastRestart
     * @return bool
     */
    protected function queueShouldRestart($lastRestart)
    {
        return $this->getTimestampOfLastQueueRestart() != $lastRestart;
    }

    /**
     * Get the last queue restart timestamp, or null.
     *
     * @return int|null
     */
    protected function getTimestampOfLastQueueRestart()
    {
        if ($this->cache) {
            return $this->cache->get('pails:queue:restart:' . $this->queue->getName());
        }
    }

    /**
     * Enable async signals for the process.
     *
     * @return void
     */
    protected function listenForSignals()
    {
        if ($this->supportsAsyncSignals()) {
            pcntl_async_signals(true);

            pcntl_signal(SIGTERM, function () {
                $this->shouldQuit = true;
            });

            pcntl_signal(SIGUSR2, function () {
                $this->paused = true;
            });

            pcntl_signal(SIGCONT, function () {
                $this->paused = false;
            });
        }
    }

    /**
     * Determine if "async" signals are supported.
     *
     * @return bool
     */
    protected function supportsAsyncSignals()
    {
        return version_compare(PHP_VERSION, '7.1.0') >= 0 &&
            extension_loaded('pcntl');
    }

    /**
     * Determine if the memory limit has been exceeded.
     *
     * @param  int $memoryLimit
     * @return bool
     */
    protected function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * Stop listening and bail out of the script.
     *
     * @param  int $status
     * @return void
     */
    protected function stop($status = 0)
    {
        $this->getEventsManager()->fire('listener:stop', $this);

        exit($status);
    }

    /**
     * Kill the process.
     *
     * @param  int $status
     * @return void
     */
    protected function kill($status = 0)
    {
        if (extension_loaded('posix')) {
            posix_kill(getmypid(), SIGKILL);
        }

        exit($status);
    }

    /**
     * Sleep the script for a given number of seconds.
     *
     * @param  int $seconds
     * @return void
     */
    protected function sleep($seconds)
    {
        sleep($seconds);
    }
}
