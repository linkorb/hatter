<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LinkORB\Component\Hatter\Factory\HatterFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Yaml;
use Connector\Connector;

class HatterLoadCommand extends Command
{
    protected static $defaultName = 'hatter:load';

    // get environment variable `dsn` during construction
    public function __construct(ParameterBagInterface $params)
    {
        $this->dsn = $params->get('dsn');
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->setDescription('Reads and outputs a YAML file')
            ->addArgument('filenames', InputArgument::IS_ARRAY, 'The YAML file(s) to load');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filenames = $input->getArgument('filenames');
        // print_r($filenames);exit();

        $hatter = HatterFactory::fromFilenames($filenames);
        
        $connector = new Connector();
        $config = $connector->getConfig($this->dsn);
        if (!$connector->exists($config)) {
            throw new \InvalidArgumentException('Database does not exist: ' . $config['dbname']);
        }
        $pdo = $connector->getPdo($config);

        $hatter->write($pdo);
        $config = $hatter->serialize();
        $output->write(Yaml::dump($config, 10, 2));
        return Command::SUCCESS;
    }
}