<?php
/**
 * Handler.php
 *
 */
namespace Pails\Exception;

use Exception;
use Phalcon\DiInterface;
use Phalcon\Mvc\User\Component;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

class Handler extends Component
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * Create a new exception handler instance.
     * @param DiInterface $di
     */
    public function __construct(DiInterface $di = null)
    {
        if (is_object($di)) {
            $this->setDI($di);
        }
    }

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        if ($this->shouldReport($e)) {
            $message = addslashes($e->getMessage());

            // log locally
            error_log($message . "\n", 3, $this->getDI()->logPath() . DIRECTORY_SEPARATOR . '/pails.error.log');

            // log to system
            error_log($message);
        }
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param  \Exception  $e
     * @return bool
     */
    public function shouldReport(Exception $e)
    {
        return ! $this->shouldntReport($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param  \Exception  $e
     * @return bool
     */
    protected function shouldntReport(Exception $e)
    {
        $dontReport = array_merge($this->dontReport, []);

        foreach ($dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render an exception
     * @param Exception $e
     */
    public function render(Exception $e)
    {

        if ($this->isConsole()) {
            $output = new StreamOutput(fopen('php://stdout', 'w'));
            $output->setVerbosity(APP_DEBUG ? OutputInterface::VERBOSITY_DEBUG : OutputInterface::VERBOSITY_NORMAL);
            return $this->renderForConsole($output, $e);
        } else {
            return $this->renderForBrowser($e);
        }
    }

    /**
     * Render an exception to the console.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  \Exception  $e
     * @return void
     */
    public function renderForConsole($output, Exception $e)
    {
        (new ConsoleApplication)->renderException($e, $output);
    }

    public function renderForBrowser(Exception $e)
    {
        if ($this->isAjax()) {
            echo json_encode([
                "error" => $e->getMessage()
            ]);
        } else {
            if (APP_DEBUG) {
                $debug = new \Phalcon\Debug();
                $debug->setUri('//static.pails.xueron.com/debug/3.0.x/');
                $debug->onUncaughtException($e);
            } else {
                $e = FlattenException::create($e);
                $handler = new SymfonyExceptionHandler(APP_DEBUG);
                echo $handler->getHtml($e);
            }
        }
    }

    public function isConsole()
    {
        $sapi_type = php_sapi_name();
        return substr($sapi_type, 0, 3) == 'cli';
    }

    public function isAjax()
    {
        return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] === "XMLHttpRequest";
    }
}