<?php

/**
 * @file
 * This file is part of DrupalInstallCli PHP library.
 *
 * @author  Anton Karpov <awd.com.ua@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    https://github.com/awd-studio/drupal-install-cli
 */

declare(strict_types=1); // strict mode


namespace DrupalInstallCli\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;


/**
 * Class Application
 * @package DrupalInstallCli\Console
 */
class Application extends BaseApplication
{

    /**
     * Path to project root directory.
     *
     * @var string
     */
    private $rootDir;


    /**
     * Constructor.
     *
     * @param string $rootDir path to project root directory
     * @param string $name    The name of the application
     * @param string $version The version of the application
     */
    public function __construct($rootDir, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->rootDir = $rootDir;

        parent::__construct($name, $version);
    }


    /**
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }


    /**
     * @return int
     */
    public function getConsoleWidth(): int
    {
        return $this->getTerminalWidth();
    }
}
