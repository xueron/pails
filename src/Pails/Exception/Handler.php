<?php
/**
 * Handler.php
 */
namespace Pails\Exception;

use Exception;
use Pails\Injectable;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

/**
 * Class Handler
 *
 * @package Pails\Exception
 */
class Handler extends Injectable
{
    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * Create a new exception handler instance.
     */
    public function __construct()
    {
        $this->debug = constant('APP_DEBUG') ?: false;
    }

    /**
     * Report or log an exception.
     *
     * @param \Exception $e
     */
    public function report(Exception $e)
    {
        if ($this->shouldReport($e)) {
            $class = addslashes(get_class($e));
            $message = addslashes($e->getMessage());
            $file = addslashes($e->getFile());
            $line = addslashes($e->getLine());
            $time = date('c');
            // log locally
            $log = sprintf("[%s] [%s] %s thrown in %s on line %d\n", $time, $class, $message, $file, $line);
            error_log($log, 3, $this->di->logPath() . DIRECTORY_SEPARATOR . '/pails.error.log');
            // log to system
            $syslog = sprintf('[%s] %s thrown in %s on line %d', $class, $message, $file, $line);
            error_log($syslog);
        }
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param \Exception $e
     *
     * @return bool
     */
    public function shouldReport(Exception $e)
    {
        return !$this->shouldntReport($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param \Exception $e
     *
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
     *
     * @param Exception $e
     */
    public function render(Exception $e)
    {
        if ($this->isConsole()) {
            $output = new StreamOutput(fopen('php://stdout', 'w'));
            $output->setVerbosity($this->debug ? OutputInterface::VERBOSITY_DEBUG : OutputInterface::VERBOSITY_NORMAL);

            return $this->renderForConsole($output, $e);
        } else {
            return $this->renderForBrowser($e);
        }
    }

    /**
     * Render an exception to the console.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Exception                                        $e
     */
    public function renderForConsole($output, Exception $e)
    {
        (new ConsoleApplication)->renderException($e, $output);
    }

    /**
     * @param \Exception $e
     */
    public function renderForBrowser(Exception $e)
    {
        if ($this->expectJson()) {
            $this->apiResponse->withError($e->getMessage(), $e->getCode())->send();
        } else {
            if ($this->debug) {
                $debug = new \Phalcon\Debug();
                $debug->setUri('//static.pails.xueron.com/debug/3.0.x/');
                $debug->onUncaughtException($e);
            } else {
                $e = FlattenException::create($e);
                $handler = new SymfonyExceptionHandler($this->debug);
                $this->response->setContent($handler->getHtml($e))->send();
            }
        }
    }

    /**
     * @return bool
     */
    public function isConsole()
    {
        $sapi_type = php_sapi_name();

        return substr($sapi_type, 0, 3) == 'cli';
    }

    /**
     * @return bool
     */
    public function expectJson()
    {
        return $this->request->isAjax()
            || $this->wantJson()
            || $this->request->getHeader('X-PJAX')
            || preg_match('/\/api\//i', $this->request->getURI());
    }

    /**
     * @return bool
     */
    public function wantJson()
    {
        foreach ($this->request->getAcceptableContent() as $accept) {
            foreach ($accept as $key => $val) {
                if ($key === 'accept' && preg_match('/[\/\+]json/i', $val)) {
                    return true;
                }
            }
        }

        return false;
    }
}
