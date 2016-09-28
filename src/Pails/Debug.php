<?php
/**
 * Debug.php
 *
 */


namespace Pails;


class Debug extends \Phalcon\Debug
{
    // set cdn
    public $_uri = "//static.pails.xueron.com/debug/3.0.x/";

    protected $_htmlLang = 'en';

    protected $_tableTitle = 'Error';

    protected $_backText = 'BACK';

    public function handle() {
        set_exception_handler([$this, 'friendlyExceptionHandler']);
        return $this;
    }

    public function setHtmlLang($lang)
    {
        $this->_htmlLang = $lang;
        return $this;
    }

    public function setTableTitle($title)
    {
        $this->_tableTitle = $title;
        return $this;
    }

    public function setBackText($text)
    {
        $this->_backText = $text;
        return $this;
    }

    public function friendlyExceptionHandler(\Throwable $exception)
    {
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();

        //
        $obLevel = ob_get_level();
        if ($obLevel > 0) {
            ob_end_clean();
        }

        if (self::$_isActive) {
            echo $message;
            return;
        }

        self::$_isActive = true;

        // Log Messages
        error_log("Exception: " . $message . " in $file on line $line");

        $html  = '<html lang="' . $this->_htmlLang . '"><head><meta charset="UTF-8"><title>' . $this->_escapeString($message) . '</title></head><body>';
        $html .= '<table width="100%" height="100%"><tr><td><table width="50%" border="0" align="center" cellpadding="4" cellspacing="1" bgcolor="#cccccc">';
        $html .= '<tr bgcolor="#dddddd"><td height="40">' . $this->_tableTitle . '</td></tr><tr bgcolor="#ffffff"><td height="150" align="center">';
        $html .= $this->_escapeString($message);
        $html .= '</td></tr><tr bgcolor="#f2f2f2"><td height="40" align="center"><a href="/">' . $this->_backText . '</a></td></tr></table>';
        $html .= '</td></tr><tr><td height="35%"></td></tr></table></body></html>';
        echo $html;

        self::$_isActive = false;

        //
        return true;
    }
}
