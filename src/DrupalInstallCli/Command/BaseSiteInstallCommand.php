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
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Terminal;


/**
 * Class BaseSiteInstallCommand
 * @package DrupalInstallCli\Command
 */
abstract class BaseSiteInstallCommand extends Command
{

    /**
     * @var array Execution parameters.
     */
    protected $dataParams = [];

    /**
     * Path to project root directory.
     *
     * @var string
     */
    protected $rootDir;


    /**
     * BaseSiteInstallCommand constructor.
     * @param string $rootDir
     *
     * @param null   $name
     */
    public function __construct(string $rootDir, $name = null)
    {
        $this->rootDir = $rootDir;

        parent::__construct($name);
    }


    /**
     * @inheritdoc
     */
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
            ->addOption('admin-mail', null, InputOption::VALUE_OPTIONAL, 'Admin user E-mail')
            ->addOption('web-dir', null, InputOption::VALUE_OPTIONAL, 'Publick WEB directory',
                $this->getDefaultWebDir());
    }


    /**
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }


    /**
     * @return string
     */
    public function getBinDir(): string
    {
        $rootDir = $this->getRootDir();

        if (isset($this->getComposerConfig()->{'config'}->{'bin-dir'})) {
            $binDir = $this->getComposerConfig()->{'config'}->{'bin-dir'};
        } elseif (is_dir($rootDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin')) {
            $binDir = $rootDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin';
        } else {
            die('There no bin directory found.');
        }

        return $binDir;
    }


    /**
     * @return object
     */
    public function getComposerConfig()
    {
        $root = $this->getRootDir();

        if (!file_exists($root . '/composer.json')) {
            die(
                'You need to set up the project dependencies using the following commands:' . PHP_EOL .
                'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
                'php composer.phar install' . PHP_EOL
            );
        }

        return json_decode(file_get_contents($root . '/composer.json'));
    }


    /**
     * @return string
     */
    public function getDefaultWebDir(): string
    {
        $composer = $this->getComposerConfig();
        $webDir = 'web';

        if (isset($composer->extra->{'drupal-composer-helper'}->{'web-prefix'})) {
            $webDir = $composer->extra->{'drupal-composer-helper'}->{'web-prefix'};
        }

        return $this->getRootDir() . DIRECTORY_SEPARATOR . $webDir;
    }


    /**
     * @return null|string
     */
    public function getWebDir()
    {
        $defaultWebDir = $this->getDefaultWebDir();

        return is_dir($defaultWebDir) ? $defaultWebDir . DIRECTORY_SEPARATOR : null;
    }


    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $question
     * @param string|null                                       $default
     * @param int                                               $counter
     * @return string
     */
    public function askData(
        InputInterface $input,
        OutputInterface $output,
        string $question,
        $default = null,
        $counter = 0
    ): string {
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
     * @return int
     */
    public function getTerminalWidth(): int
    {
        return (new Terminal())->getWidth();
    }


    /**
     * @param string $text
     * @return string
     */
    public function wrappText(string $text): string
    {
        $max = $this->getTerminalWidth();

        $textLen = strlen($text);
        if ($textLen <= $max) {
            $side = str_repeat(' ', (int) round(($max - $textLen) / 2));
        } else {
            $side = '';
        }

        return substr($side . $text . $side, 0, $max);
    }


    /**
     * @param string $text
     * @param string $type
     * @return string
     */
    public function getPromtMessage(string $text, string $type = 'fg=black;bg=cyan'): string
    {
        $end = strstr($type, '/=/', true) ? '' : $type;
        $message = $this->wrappText($text);

        return "<{$type}>{$message}</{$end}>";
    }


    /**
     * @return string
     */
    public function getLogo(): string
    {
        return <<<EOT
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
    }


    /**
     * @param string $message
     * @return string
     */
    public function writeMessage(string $message): string
    {
        return PHP_EOL . $message . PHP_EOL;
    }


    /**
     * @param string $message
     * @return string
     */
    public function writeLogoMessage($message = ''): string
    {
        foreach (explode(PHP_EOL, $this->getLogo()) as $str) {
            $message .= PHP_EOL . $this->wrappText($str);
        }

        return "<fg=blue>{$message}</>";
    }


    /**
     * @return string
     */
    public function writeWelcomeMessage(): string
    {
        return $this->writeMessage($this->getPromtMessage('Welcome to Drupal instalation!'));
    }


    /**
     * @param bool $success
     *
     * @return string
     */
    public function writeFinishMessage(bool $success = true): string
    {
        $message = $success ? 'Installation finished success!' : 'Instalation failed!';
        $type = $success ? 'fg=black;bg=cyan' : 'fg=black;bg=red';

        return $this->writeMessage($this->getPromtMessage($message, $type));
    }


    /**
     * @return string
     */
    public function getExecuteMessage(): string
    {
        return $this->writeMessage($this->getPromtMessage('Start instalation...', 'fg=black;bg=blue'));
    }


    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $paramName
     * @param string                                            $question
     * @param mixed|null                                        $default
     */
    public function getParam(
        InputInterface $input,
        OutputInterface $output,
        string $paramName,
        string $question,
        $default = null
    ) {
        $paramData = $input->getOption($paramName) ?? $this->askData($input, $output, $question, $default);
        $this->setDataParam($paramName, $paramData);
    }


    /**
     * Return path to executable command.
     *
     * @return string
     */
    abstract public function getCommandBin(): string;


    /**
     * @return array
     */
    public function getDataParams(): array
    {
        return $this->dataParams;
    }


    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getDataParam(string $name)
    {
        $name = str_replace('_', '-', $name);

        return $this->dataParams[$name] ?? null;
    }


    /**
     * @param string $name
     * @param mixed  $data
     */
    public function setDataParam(string $name, $data)
    {
        $this->dataParams[$name] = $data;
    }


    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function getUserData(InputInterface $input, OutputInterface $output): void
    {
        // Ask DB data
        $this->getParam($input, $output, 'db-host', 'Please, enter DB HOST', 'localhost');
        $this->getParam($input, $output, 'db-name', 'Please, enter DB NAME');
        $db_name = $this->getDataParam('db_name');
        $this->getParam($input, $output, 'db-user', 'Please, enter DB USER', $db_name);
        $this->getParam($input, $output, 'db-pass', 'Please, enter DB PASS');

        // Ask site settings
        $this->getParam($input, $output, 'profile', 'Please, enter the installation profile', 'minimal');
        $this->getParam($input, $output, 'admin-login', 'Please, enter the Admin name', 'admin');
        $this->getParam($input, $output, 'admin-pass', 'Please, enter the Admin password', '1111');
        $this->getParam($input, $output, 'admin-mail', 'Please, enter the Admin E-mail', $this->getGitEmail());
        $this->getParam($input, $output, 'site-name', 'Please, enter the site name', 'Site Name');
        $admin_mail = $this->getDataParam('admin_mail');
        $this->getParam($input, $output, 'site-mail', 'Please, enter the Site E-mail', $admin_mail);

        // Ask WEB dir
        $this->getParam($input, $output, 'web-dir', 'Please, enter the WEB directory');
    }


    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return array
     */
    abstract public function buildExecution(InputInterface $input, OutputInterface $output): array;


    /**
     * @param array $exec
     *
     * @return bool
     */
    abstract public function executeCommand(array $exec): bool;


    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->isInteractive()) {
            die(
                'You should use command in interactive mode!' . PHP_EOL
            );
        }

        // Welcome
        $output->writeln($this->writeLogoMessage());
        $output->writeln($this->writeWelcomeMessage());

        // Get data
        $this->getUserData($input, $output);

        // Execute command
        $exec = $this->buildExecution($input, $output);
        $output->writeln($this->getExecuteMessage());
        $success = $this->executeCommand($exec);

        // Finish
        $output->writeln($this->writeFinishMessage($success));
    }
}
