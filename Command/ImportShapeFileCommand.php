<?php
namespace Munso\IRISGeocoderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\ProcessBuilder;

ini_set('memory_limit', -1);

/**
 * Class ImportShapeFileCommand
 * @package Munso\IRISGeocoderBundle\Command
 */
class ImportShapeFileCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('munso:iris:import-shape')
            ->setDefinition(
                array(
                    new InputArgument('shapefile', InputArgument::REQUIRED, '.shp filename'),
                    new InputOption(
                        'connection',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'Set doctrine connection name',
                        'psql'
                    ),
                    new InputOption('only-dump', null, InputOption::VALUE_NONE, 'Disable import SQL into database'),
                    new InputOption('truncate', null, InputOption::VALUE_NONE, 'Truncate table'),
                )
            );
    }

    public function getEntityManager()
    {
        return $this->getContainer()->get('munso.iris_geocoder_entity_manager');
    }


    public function getTableName()
    {
        $metadaClass = $this->getEntityManager()->getClassMetadata(
            $this->getContainer()->getParameter('munso.iris_geocoder.entity_name')
        );

        return sprintf('%s.%s', $metadaClass->getSchemaName(), $metadaClass->getTableName());
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $shapeFile = realpath($input->getArgument('shapefile'));

        $this->getEntityManager()->getConnection()->getConfiguration()->setSQLLogger(null);
        $dumpFilepath = $this->getContainer()->getParameter('kernel.cache_dir').DIRECTORY_SEPARATOR.'shapefile.sql';

        if (!file_exists($shapeFile)) {
            throw new \InvalidArgumentException(
                sprintf("SQL file '<info>%s</info>' does not exist.", $shapeFile)
            );
        } elseif (!is_readable($shapeFile)) {
            throw new \InvalidArgumentException(
                sprintf("SQL file '<info>%s</info>' does not have read permissions.", $shapeFile)
            );
        }

        if ($input->getOption('truncate')) {
            $this->truncateTable($output);
        }

        try {
            $cmdArguments = array(
                // '-W "latin1"',
                '-e',
                '-a', //append to existing base
                '-N skip' //null policy
            );
            $importCmd = sprintf(
                'shp2pgsql %s -I %s %s > %s',
                implode(' ', $cmdArguments),
                $shapeFile,
                $this->getTableName(),
                $dumpFilepath
            );
            $process = new Process($importCmd);

            $output->writeln("<info>Executing dump</info>");

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(sprintf('<info> %s</info>', $importCmd));
            }
            $process->mustRun();

            $output->writeln(sprintf('.shp file dumped into <info>%s</info>', $dumpFilepath));

            if (!$input->getOption('only-dump')) {
                $this->executeDoctrineImport($dumpFilepath, $input, $output);
            }

        } catch (ProcessFailedException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
        } finally {
            if (!$input->getOption('only-dump')) {
                $fs = new FileSystem();
                $fs->remove(array($dumpFilepath));
            }
        }

    }

    protected function importBatchFile($file, InputInterface $input, OutputInterface $output)
    {

    }


    protected function executeDoctrineImport($file, InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:database:import');
        $arguments = array(
            'command' => 'doctrine:database:import',
            'file' => $file,
            '--connection' => $input->getOption('connection'),
            '-vvv' => ($output->getVerbosity() > OutputInterface::VERBOSITY_VERBOSE),
        );

        $greetInput = new ArrayInput($arguments);

        return $command->run($greetInput, $output);
    }


    protected function truncateTable(OutputInterface $output)
    {
        $connection = $this->getEntityManager()->getConnection();
        $entityName = $this->getContainer()->getParameter('munso.iris_geocoder.entity_name');
        $sequenceName = $this->getEntityManager()->getClassMetadata(
            $this->getContainer()->getParameter('munso.iris_geocoder.entity_name')
        )->getSequenceName($connection->getDatabasePlatform());


        $connection->beginTransaction();
        try {
            $connection->query('DELETE FROM '.$entityName.';');
            $connection->query('ALTER SEQUENCE '.$sequenceName.'  RESTART WITH  1;');
            $connection->commit();
            $output->writeln(sprintf('<info>Table %s was successfully truncated.</info>', $this->getTableName()));
        } catch (\Exception $e) {
            $connection->rollback();
            $output->writeln(sprintf('<error>Error during truncate operation : %s </error>', $this->getTableName()));
        }
    }

}