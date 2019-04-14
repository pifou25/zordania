<?php

/* 
 * ExceptionHandler:
 * get every uncatched exceptions
 * mode normal: conserver les erreurs dans $_errors et les logguer dans un fichier phperr
 * mode debug: afficher aussi dans la page les erreurs
 */
class ExceptionHandler {
    
    var $_errors = [];
    var $_user = "(user inconnu)";
    var $_tpl;
    
    static $_instance;

    const ERR_LEVEL = [
            E_ERROR        => 'Fatal Error',
            E_WARNING      => 'Warning',
            E_NOTICE       => 'Notice',
            E_USER_ERROR   => 'User Fatal Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE  => 'User Notice'
    ];
    
    public function __construct() {
        set_exception_handler(['ExceptionHandler', 'handleException']); 
        set_error_handler(['ExceptionHandler', 'handleError']);
        register_shutdown_function( ['ExceptionHandler', 'shutdownFunction']);
        self::set($this);
    } 

    public function __destruct(){
        if(!empty($this->_errors)) { // log des erreurs PHP
                $err_log = new log(SITE_DIR."logs/phperr/php_".date("d_m_Y").".log", "H:i:s", false);
                $err_log->text("****** Erreurs pour {$this->_user}  ".date("H:i:s")." *****");
                foreach($this->_errors as $key => $err){
                    // $err is exception
                    $err_log->text(self::formatException($err));
                    if($key>4) {
                            $err_log->text('Plus de 4 erreurs **** le reste ignoré *** '.count($this->_errors));
                            break;
                    }
                }
        }
        
    }
    
    /* singleton */
    public static function get(){
        return self::$_instance;
    }
    public static function set($instance){
        self::$_instance = $instance;
    }
    /* add an exception to the array */
    public static function add(Throwable $e){
        self::get()->_errors[] = $e;
    }
    
    public static function printException(Throwable $exception)
    {
        echo self::formatException($exception);
    }
    
    public static function formatException(Throwable $exception, $html = false){
        $code = isset(self::ERR_LEVEL[$exception->getCode()]) ?
            self::ERR_LEVEL[$exception->getCode()] : "ERREUR(" . $exception->getCode() . ')';
        if(CRON)
                $url = "CRON: ".$_SERVER['PHP_SELF'];
        else
                $url = "URL: ".$_SERVER["REQUEST_URI"];

        if($html){
            $msg = "<b>$code</b>:  Uncaught '" . get_class($exception) . "' with message: ";
        }else{
            $msg = "$code:  Uncaught '" . get_class($exception) . "' with message: ";
        }
        $msg .= htmlentities($exception->getMessage())."\n$url\n";
        $msg .= 'File: ' . $exception->getFile() . '[' . $exception->getLine() . "]\n";
        $msg .= 'Stack trace:' . $exception->getTraceAsString() ."\n";
        if($html)
            $msg = '<div style="border:1px #000 solid; text-align:left; font-family:monospace; background-color:#CCC; color:#000;">'.nl2br($msg).'</div>';

        return $msg;
    }

    /* gestion des exceptions comme objet */
    public static function handleException(Throwable $e)
    {
        try
        {
            if($e instanceof Illuminate\Database\QueryException){
                echo "<div class'error'>erreur SQL Eloquent</div>";
                echo self::formatException($e, true);
            }else if($e instanceof Error){
                self::dieWithTemplate(self::formatException($e, true));
            }
            
            if(SITE_DEBUG){
                echo self::formatException($e, true);
            }
            self::add($e);
            return false;
        }
        catch (Exception $e1)
        {
            echo get_class($e1)." thrown within the exception handler:".self::formatException($e1, true);
            echo "Previous exception was:".self::formatException($e, true);
        }
    }
    

    /* Gestion des erreurs classiques */
    public static function handleError($severity, $errstr, $errfile, $errline)
    {
            /* Ignore error, @ */
            if (error_reporting() === 0)
                    return ;

            // convert simple error into execption
            $ex = new ErrorException("[error $severity] ".$errstr, $severity, $severity, $errfile, $errline);
            self::handleException($ex);
    }

    /* proper dying with the template if available */
    public static function dieWithTemplate($msg){
        $_tpl = self::get()->_tpl;
        if ($_tpl != null) {
            $_tpl->set_lang('all');
            $_tpl->set('sv_site_debug', false);
            $_tpl->set('page', 'mysql_error.tpl');
            $_tpl->set('error', $msg);
            if (!$_tpl->var->_display != "xml")
                die($_tpl->get('index.tpl', 1));
        }
        die("$msg\nEnd of script");

    }
    
    /* list of queries for debug */
    public static function getQueryLog(){
        $msg = "\nNO QUERIES\n";
        if(!empty(DB::connection()->getQueryLog())){
            $msg = "\n\nLIST OF QUERIES\n";
            $i = 0;
            foreach(DB::connection()->getQueryLog() as $query){
                $i++;
                $msg .= "\n$i: {$query['query']}\nBinding:\n";
                $msg .= print_r($query['bindings'], TRUE);
            }
        }
        return $msg;
    }
    /** shutdown function may help to handle Fatal Errors */
    public static function shutdownFunction() {
        $error = error_get_last();

        if( $error !== NULL) {

            $ex = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            $type = array_search($error['type'], get_defined_constants());
            $msg = "FATAL ERROR:$type\n" . self::formatException($ex);
            $msg .= self::getQueryLog();

            // hide password in queries
            if(!empty(MYSQL_PASS))
                $msg = str_replace(MYSQL_PASS, '***', $msg);

            self::dieWithTemplate($msg);
        }

    }
}
