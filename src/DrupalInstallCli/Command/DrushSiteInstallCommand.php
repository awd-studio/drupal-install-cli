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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;


/**
 * Class DrushSiteInstallCommand
 * @package DrupalInstallCli\Command
 */
class DrushSiteInstallCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('drupal:site-install')
            ->setDescription('Run Drush install from the console.')
            ->setHelp("This command allows you to install Drupal site from the console.")
            ->addOption('db-host', null, InputOption::VALUE_OPTIONAL, 'Database host')
            ->addOption('db-name', null, InputOption::VALUE_OPTIONAL, 'Database name')
            ->addOption('db-user', null, InputOption::VALUE_OPTIONAL, 'Database user name')
            ->addOption('db-pass', null, InputOption::VALUE_OPTIONAL, 'Database user password')
            ->addOption('profile', null, InputOption::VALUE_OPTIONAL, 'Installation profile name')
            ->addOption('site-name', null, InputOption::VALUE_OPTIONAL, 'Your future site name')
            ->addOption('site-mail', null, InputOption::VALUE_OPTIONAL, 'Site E-mail')
            ->addOption('admin-login', null, InputOption::VALUE_OPTIONAL, 'Admin user name')
            ->addOption('admin-pass', null, InputOption::VALUE_OPTIONAL, 'Admin user password')
            ->addOption('admin-mail', null, InputOption::VALUE_OPTIONAL, 'Admin user E-mail');
    }


    /**
     * @return string
     */
    public function getRoot(): string
    {
        $root = $this->getApplication()->getRootDir();

        if (!file_exists($root . '/composer.json')) {
            die(
                'You need to set up the project dependencies using the following commands:' . PHP_EOL .
                'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
                'php composer.phar install' . PHP_EOL
            );
        }

        $composer = json_decode(file_get_contents($root . '/composer.json'));

        $web = $composer->extra->{'drupal-composer-helper'}->{'web-prefix'};

        return $root . DIRECTORY_SEPARATOR . $web . DIRECTORY_SEPARATOR;
    }


    /**
     * @param string      $question
     * @param string|null $default
     * @return string
     */
    public function askData(InputInterface $input, OutputInterface $output, string $question, $default = null, $counter = 0): string
    {
        if ($counter > 5) {
            die(
                "No required data: {$question}" . PHP_EOL
            );
        }

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $text = $question;
        $text .= $default ? " [<info>{$default}</info>]" : '';
        $text .= ': ';

        $q = new Question($text, $default);

        return $helper->ask($input, $output, $q) ?? $this->askData($input, $output, $question, $default, ++$counter);
    }


    /**
     * @return string|null
     */
    public function getGitEmail()
    {
        exec('git config user.email', $output);
        $gitEmail = (isset($output[0]) && filter_var($output[0], FILTER_VALIDATE_EMAIL)) ? $output[0] : null;
        return $gitEmail;
    }


    /**
     * @param string $text
     * @return string
     */
    public function wrappText(string $text, int $max = 80): string
    {
        $textLen = strlen($text);
        if ($textLen <= $max) {
            $size = $max - $textLen;
            $side = str_repeat(' ', (int) round($size / 2));
        } else {
            $side = '';
        }

        return substr($side . $text . $side, 0, $max);
    }


    /**
     * @return string
     */
    public function getPromtMessage(string $text, string $type = 'fg=black;bg=cyan'): string
    {
        $message = $this->wrappText($text);

        return "<{$type}>{$message}</>";
    }


    /**
     * @return string
     */
    public function getLogo(): string
    {
        //         $logo = <<<EOT
        //  ____                         _
        // |  _ \ _ __ _   _ _ __   __ _| |
        // | | | | '__| | | | '_ \ / _` | |
        // | |_| | |  | |_| | |_) | (_| | |
        // |____/|_|   \__,_| .__/ \__,_|_|
        //                  |_|
        //
        // EOT;

        $logo = <<<EOT
8888888b.                                     888
888  "Y88b                                    888
888    888                                    888
888    888 888d888 888  888 88888b.   8888b.  888
888    888 888P"   888  888 888 "88b     "88b 888
888    888 888     888  888 888  888 .d888888 888
888  .d88P 888     Y88b 888 888 d88P 888  888 888
8888888P"  888      "Y88888 88888P"  "Y888888 888
                            888                  
                            888                  
                            888                  

EOT;

        $message = '';
        foreach (explode(PHP_EOL, $logo) as $str) {
            $message .= PHP_EOL . $this->wrappText($str);
        }

        return "<fg=blue>{$message}</>";
    }


    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $git_email = $this->getGitEmail();
        $root = $this->getRoot();

        $output->writeln($this->getLogo());
        $output->writeln($this->getPromtMessage('Welcome to Drupal instalation!'));
        $output->writeln('');

        $db_host = $input->getOption('db-host') ?? $this->askData($input, $output, 'Please, enter DB HOST',
                'localhost');
        $db_name = $input->getOption('db-name') ?? $this->askData($input, $output, 'Please, enter DB NAME');
        $db_user = $input->getOption('db-user') ?? $this->askData($input, $output, 'Please, enter DB USER', $db_name);
        $db_pass = $input->getOption('db-pass') ?? $this->askData($input, $output, 'Please, enter DB PASS');

        $install_profile = $input->getOption('profile') ?? $this->askData(
                $input,
                $output,
                'Please, enter the installation profile',
                'minimal'
            );

        $admin_login = $input->getOption('admin-login') ?? $this->askData(
                $input,
                $output,
                'Please, enter the Admin name',
                'admin'
            );

        $admin_pass = $input->getOption('admin-pass') ?? $this->askData(
                $input,
                $output,

                'Please, enter the Admin password',
                '1111'
            );

        $admin_mail = $input->getOption('admin-mail') ?? $this->askData(
                $input,
                $output,
                'Please, enter the Admin E-mail',
                $git_email
            );

        $site_name = $input->getOption('site-name') ?? $this->askData(
                $input,
                $output,
                'Please, enter the site name',
                'Site Name'
            );

        $site_mail = $input->getOption('site-mail') ?? $this->askData(
                $input,
                $output,
                'Please, enter the Site E-mail',
                $admin_mail
            );

        $exec = [
            'drush -y --notify',
            "--root={$root}",
            "site-install {$install_profile}",
            "--db-url=mysql://{$db_user}:{$db_pass}@{$db_host}/{$db_name}",
        ];

        if ($site_name) $exec[] = "--site-name='{$site_name}'";
        if ($admin_login) $exec[] = "--account-name={$admin_login}";
        if ($admin_pass) $exec[] = "--account-pass={$admin_pass}";
        if ($admin_mail) $exec[] = "--account-mail={$admin_mail}";
        if ($site_mail) $exec[] = "--site-mail={$site_mail}";

        exec(implode(' ', $exec), $status);

        $output->writeln('');
        $output->writeln($this->getPromtMessage('Instalation finished success!'));
        $output->writeln('');
    }
}
