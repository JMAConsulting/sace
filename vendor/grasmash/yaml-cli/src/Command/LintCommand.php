<?php

namespace Grasmash\YamlCli\Command;

<<<<<<< HEAD
use Dflydev\DotAccessData\Data;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
=======
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

/**
 * Class CreateProjectCommand
 *
 * @package Grasmash\YamlCli\Command
 */
class LintCommand extends CommandBase
{
<<<<<<< HEAD
=======

>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    /**
     * {inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('lint')
            ->setDescription('Validates that a given YAML file has valid syntax.')
            ->addUsage("path/to/file.yml")
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                "The filename of the YAML file"
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int 0 if everything went fine, or an exit code
     */
<<<<<<< HEAD
    protected function execute(InputInterface $input, OutputInterface $output)
=======
    protected function execute(InputInterface $input, OutputInterface $output): int
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    {
        $filename = $input->getArgument('filename');
        $yaml_parsed = $this->loadYamlFile($filename);
        if (!$yaml_parsed) {
            // Exit with a status of 1.
            return 1;
        }

        if (OutputInterface::VERBOSITY_VERBOSE === $output->getVerbosity()) {
            $output->writeln("<info>The file $filename contains valid YAML.</info>");
        }

        return 0;
    }
}
