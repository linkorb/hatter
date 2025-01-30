<?php

namespace LinkORB\Hatter\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LinkORB\Component\Hatter\Factory\HatterFactory;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Connector\Connector;

class HatterLoadCommand extends Command
{
    // get environment variable `dsn` during construction
    public function __construct(private readonly string $dsn)
    {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->setName('load')
            ->setDescription('Reads Hatter specific YAML files and populates database')
            ->addArgument('filenames', InputArgument::IS_ARRAY, 'The YAML file(s) to load');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filenames = $input->getArgument('filenames');
        // print_r($filenames);exit();

        if (0 === count($filenames)) {
            $io->error('No filenames specified');
            return Command::FAILURE;
        }

        $hatter = HatterFactory::fromFilenames($filenames);

        if (empty($this->dsn)) {
            $io->error('Empty HATTER_DSN environment variable');
            return Command::FAILURE;
        }

        $connector = new Connector();
        $config = $connector->getConfig($this->dsn);
        if (!$connector->exists($config)) {
            $io->error('Database does not exist: ' . $config->getName());
            return Command::FAILURE;
        }
        $pdo = $connector->getPdo($config);

        $hatter->write($pdo);
        $config = $hatter->serialize();
        $output->write(Yaml::dump($config, 10, 2));
        return Command::SUCCESS;
    }
}
