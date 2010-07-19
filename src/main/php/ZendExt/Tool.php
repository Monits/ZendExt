<?php
/**
 * Code generation tool.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.3.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */

/**
 * Code generation tool.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.3.0
 * @link      http://www.zendext.com
 * @since     1.3.0
 */
class ZendExt_Tool
{
    const GENERATORS_PATH = '/Tool/Generator';

    /**
     * Retrieves the options for the tool.
     *
     * Merges the default options with the given ones.
     *
     * @param array $opts The options to merge.
     *
     * @return ZendExt_Console_Getopt
     */
    public function getOptions(array $opts = array())
    {
        $opts['outputDir|o=s'] = 'The dir where to create the generated files';
        $opts['generator|g=s'] = 'Which generator to use. Case sensitive.';
        $opts['help-s']        = 'Displays help message for the given '
                                   . 'generator if any given, otherwise '
                                   . 'renders this message';

        return new ZendExt_Console_Getopt($opts);
    }

    /**
     * Retrieves the basic usage message.
	 *
     * @return string
     */
    public function getUsageMessage()
    {
        return $this->getOptions()->getUsageMessage()
           . "\nAvailable generators: "
           . implode(', ', $this->getGenerators()) . PHP_EOL;
    }

    /**
     * Executes the tool.
     *
     * @return string The output.
     */
    public static function execute()
    {
        $tool = new self();
        $opts = $tool->getOptions();

        // Basic help asked.
        if ($opts->help === true) {
            return $tool->getUsageMessage();
        } else if ($opts->help !== null) {

            // Help asked for a specific generator.
            $gen = $tool->getGenerator($opts->help);

            return $tool->getOptions($gen->getOptions())
                        ->getUsageMessage();
        }

       // No help asked.
       if ($opts->generator !== null) {

           foreach ($opts->getAsArray('generator') as $generator) {
               $gen = $tool->getGenerator($generator, $opts->outputDir);

               $gen->setOptions($tool->getOptions($gen->getOptions()));
               $gen->generate();
           }

       } else {
           // No required arg given.
           return $tool->getUsageMessage();
       }
    }

    /**
     * Retrieves a generator instance.
     *
     * @param string $generator Which generator to instance and retrieve.
     * @param string $outputDir Where the generated files will be stored.
     *
     * @return ZendExt_Tool_Generator_Abstract
     */
    public function getGenerator($generator, $outputDir = null) {
        $class = 'ZendExt_Tool_Generator_' . $generator;

        if (!file_exists(
            dirname(__FILE__)
            . self::GENERATORS_PATH
            . DIRECTORY_SEPARATOR . $generator . '.php'
        )) {
            throw new ZendExt_Tool_Exception(
                'The generator file doesn\'t exists (' . $generator . ')'
            );
        }

        if (!class_exists($class)) {
            throw new ZendExt_Tool_Exception(
            	'The generator class doesn\'t exists (' . $generator . ')'
            );
        } elseif (array_search(
        	'ZendExt_Tool_Generator_Abstract',
             class_parents($class)
        ) === false) {
            throw new ZendExt_Tool_Exception(
            	'The generator must extend ZendExt_Tool_Generator_Abstract'
            );
        }

        return new $class($outputDir);
    }

    /**
     * Retrieves a list of all available generators.
     *
     * @return array
     */
    public function getGenerators()
    {
        $files = glob(
            dirname(__FILE__) . self::GENERATORS_PATH
            . DIRECTORY_SEPARATOR . '*.php'
        );

        $ret = array();

        foreach ($files as $file) {
            $generator = str_replace('.php', '', basename($file));

            // List only actual generators.
            require_once $file;
            if (class_exists('ZendExt_Tool_Generator_' . $generator)
                && is_subclass_of(
            		'ZendExt_Tool_Generator_' . $generator,
                	'ZendExt_Tool_Generator_Abstract'
                )) {
                $ret[] = $generator;
            }
        }

        return $ret;
    }

}
