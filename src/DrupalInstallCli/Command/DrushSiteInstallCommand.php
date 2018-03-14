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


namespace DrupalInstallCli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DrushSiteInstallCommand
 * @package DrupalInstallCli\Command
 */
class DrushSiteInstallCommand extends BaseSiteInstallCommand
{

    /**
     * Return path to executable command.
     *
     * @return string
     */
    public function getCommandBin(): string
    {
        $binDir = $this->getBinDir();

        if (is_executable($binDir . DIRECTORY_SEPARATOR . 'drush')) {
            $drush = $binDir . DIRECTORY_SEPARATOR . 'drush';
        } else {
            die('Drush command not exists or not executable!');
        }

        return $drush;
    }


    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return array
     */
    public function buildExecution(InputInterface $input, OutputInterface $output): array
    {
        // Prepare command
        $db_pass = $this->getDataParam('db_pass');
        $db_name = $this->getDataParam('db_name');
        $db_user = $this->getDataParam('db_user');
        $db_host = $this->getDataParam('db_host');
        $profile = $this->getDataParam('profile');

        $exec = [
            "{$this->getCommandBin()} -y --notify",
            "--root={$this->getWebDir()}",
            "site-install {$profile}",
            "--db-url=mysql://{$db_user}:{$db_pass}@{$db_host}/{$db_name}",
        ];
        if ($site_name = $this->getDataParam('site_name')) $exec[] = "--site-name='{$site_name}'";
        if ($admin_login = $this->getDataParam('admin_login')) $exec[] = "--account-name={$admin_login}";
        if ($admin_pass = $this->getDataParam('admin_pass')) $exec[] = "--account-pass={$admin_pass}";
        if ($admin_mail = $this->getDataParam('admin_mail')) $exec[] = "--account-mail={$admin_mail}";
        if ($site_mail = $this->getDataParam('site_mail')) $exec[] = "--site-mail={$site_mail}";

        return $exec;
    }


    /**
     * @param array $exec
     *
     * @return bool
     */
    public function executeCommand(array $exec): bool
    {
        exec(implode(' ', $exec), $status);

        return (bool) $status;
    }
}
