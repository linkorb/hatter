<?php

namespace LinkORB\Hatter\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addArgument('filenames', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'The YAML file(s) to load')
            ->addOption('skip-foreign-key-checks',
                null,
                InputOption::VALUE_NONE,
                "Skip foreign key checks when inserting data into a MySQL compatible database"
            )
            ->addOption('ignore-missing-tables',
                null,
                InputOption::VALUE_NONE,
                "Ignore missing tables when inserting data into a MySQL compatible database"
            )
            ->addOption('summary',
                null,
                InputOption::VALUE_NONE,
                "Show a short summary instead of detailed execution steps"
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $summarize = $input->getOption('summary');

        $hatter = HatterFactory::fromFilenames($input->getArgument('filenames'));

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

        $hatter->write(
            $pdo,
            $config->getName(),
            $input->getOption('skip-foreign-key-checks'),
            $input->getOption('ignore-missing-tables'),
            $summarize,
        );

        $output->write(Yaml::dump(
            $summarize ? $hatter->summary : $hatter->serialize(),
            10,
            2
        ));
        return Command::SUCCESS;
    }
}
