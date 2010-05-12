<?php
/**
 * Launcher script for offline processes.
 *
 * @category  Tools
 * @package   Tools_Cron
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

error_reporting(E_ALL);
define('CONFIG_FILE', 'src/main/resources/config/process.xml');
define('STRATEGY_PATH', 'strategy/');

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('ZendExt_');

try {

    $opts = new Zend_Console_Getopt(array(
        'config|c=s' => 'Path to process config file. Optional'
    ));

    $opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {

    die($e->getUsageMessage());
}


$config = loadConfig($opts->config);
$strategyDir = $config->launcher->strategyDir ?
    $config->launcher->strategyDir : STRATEGY_PATH;

if ( $config->launcher->strategyNamespace ) {

    $loader->registerNamespace($config->launcher->strategyNamespace);
}

if (!file_exists($config->launcher->logDir)) {

    mkdir($config->launcher->logDir, 0744, true);
}

instanceLogger($config->launcher->logDir.'/'.$config->launcher->logFile);
ZendExt_Cron_Persistance::setDataDirectory(
    $config->data->dir ? $config->data->dir.'/' : 'data/'
);

$strategyList = $opts->getRemainingArgs();
if ( empty($strategyList) ) {

    logger('info', 'You must specify at least one strategy.');
} else if ( count($strategyList) == 1 ) {

    spawnProcess($strategyList[0], $strategyDir, $config->process);
} else {

    $forked = 0;

    foreach ($strategyList as $strategyName) {

        sleep(1);
        $pid = pcntl_fork();
        if ( $pid == 0 ) {

            spawnProcess($strategyName, $strategyDir, $config->process);
            exit();
        } else if ( $pid == -1 ) {

            $log = 'An error ocurred while trying to start '
                .$strategyName.' strategy.';

            logger(
                'info',
                $log
            );

            logger('info', 'Waiting for other process to end...');

            waitForChildren($forked);

            die();
        }
        ++$forked;
    }

    waitForChildren($forked);
}

logger('info', 'All processes finished executing successfully.');

/**
 * Wait for all children processes to end.
 *
 * @param integer $forked The number of forked processes.
 *
 * @return void
 */
function waitForChildren($forked)
{

    while ( $forked > 0 ) {

        $ret = pcntl_wait($status);

        if ( $ret == -1 ) {

            logger(
                'crit',
                'An error ocurred while waiting for the processes to end'
            );
        } else if ( $ret != 0 ) {

            --$forked;
            logger('info', 'Process '.$ret.' exited with status '.$status);
        }
    }
}

/**
 * Spawn a new process.
 *
 * @param string      $strategyName The name of the strategy for the process.
 * @param string      $strategyDir  The directory to load strategies from.
 * @param Zend_Config $config       The config to use
 *
 * @return void
 */
function spawnProcess($strategyName, $strategyDir, Zend_Config $config)
{

    $loaded = Zend_Loader_Autoloader::autoload($strategyName);

    if ( !$loaded ) {

        $strategyPath = $strategyDir.'/'.$strategyName.'.php';
        if ( !file_exists($strategyPath) ) {

            logger('crit', 'The specified strategy was not found.');
            return;
        } else {

            require_once($strategyPath);
            if (!class_exists($strategyName)) {

                logger('crit', 'The specified strategy was not found.');
                return;
            }

        }
    }

    register_shutdown_function('shutdownHandler');
    set_error_handler('errorHandler');

    ZendExt_Cron_Persistance::setCurrentProcess($strategyName);


    try {

        $process = new ZendExt_Cron_Process(new $strategyName, $config);
        $process->execute();
    } catch ( ZendExt_Cron_LockException $e ) {

        logger('crit', $e->getMessage());
    } catch ( ZendExt_Cron_ErrorException $e ) {

        logger('crit', $e->getMessage());
    } catch ( Exception $e ) {

        logger('crit', $e->__toString());
        if ( isset($process) ) {

            $process->forceCleanup();
        }
    }
}

/**
 * Instance the logger.
 *
 * @param string $file The file to log to.
 *
 * @return void
 */
function instanceLogger($file)
{
    $log = new Zend_Log();
    $writer = new Zend_Log_Writer_Stream($file);

    $log->addWriter($writer);

    ZendExt_Cron_Log::setLauncherLog($log);
}

/**
 * Log a message.
 *
 * @param string $level   The log level.
 * @param string $message The message to log.
 *
 * @return void
 */
function logger($level, $message)
{

    if (class_exists('ZendExt_Cron_Log') &&
        ($logger = ZendExt_Cron_Log::getLauncherLog()) !== null ) {

        $logger->$level($message);

    } else {

        echo $message.PHP_EOL;
    }
}

/**
 * Load the config file.
 *
 * @param string $configFile The path to the config file.
 *
 * @return void
 */
function loadConfig($configFile)
{
    if ( !$configFile ) {

        $configFile = CONFIG_FILE;
    }

    if (!file_exists($configFile)) {
        error_log('Cant find the config file!! Crashing hard.');
        die();
    }

    try {

        return new Zend_Config_Xml($configFile);
    } catch ( Zend_Config_Exception $e ) {

        error_log('Config file parsing failed, crashing hard.');
        error_log($e->__toString());

        die();
    }
}

/**
 * Custom error handler for errors triggered by PHP itself.
 *
 * @param integer $errno      The code of the error raised.
 * @param string  $errstr     The error message.
 * @param string  $errfile    The file in which the error was found.
 * @param integer $errline    The line at which the error was found.
 * @param array   $errcontext The active symbol table when the error occurred.
 *
 * @return boolean True if the error was handled, false if the default
 *                      error handler should process it.
 */
function errorHandler($errno, $errstr, $errfile, $errline, array $errcontext)
{

    if ( $errno == E_STRICT ) {

        return false;
    }

    $output =
        'An error ocurred:'.PHP_EOL.
        $errstr.PHP_EOL.
        'Error on line '.$errline.' in file '.$errfile;

    if ( $errno == E_USER_ERROR ) {

        $level = 'crit';

    } else {

        $level = 'err';
    }

    logger($level, $output);

    return false;
}

/**
 * Custom shutdown handler.
 *
 * This is called by PHP when the script is being shutdown,
 * it checks for errors and logs accordingly.
 *
 * @return void
 */
function shutdownHandler()
{

    $isError = false;

    if ( $error = error_get_last() ) {

        switch ( $error['type'] ) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $isError = true;
                break;

            default:
                /*
                 * Any other error state is handled by errorHandler
                 * so there's no need for this function to do it too.
                 */
                break;
        }
    }

    if ( $isError ) {

        //TODO: Somehow clean up the .pid file
        error_log($error['message']);
    }
}