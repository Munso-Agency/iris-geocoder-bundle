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
    protected $entityName;

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

    protected function getEntityManager()
    {
        return $this->getContainer()->get('munso.iris_geocoder_entity_manager');
    }

    protected function getTableName()
    {
        $metadaClass = $this->getEntityManager()->getClassMetadata(
            $this->getContainer()->getParameter('munso.iris_geocoder.entity_name')
        );

        return sprintf('%s.%s', $metadaClass->getSchemaName(), $metadaClass->getTableName());
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $this->entityName = $this->getContainer()->getParameter('munso.iris_geocoder.entity_name');

        $shapeFile = realpath($input->getArgument('shapefile'));

        if (!file_exists($shapeFile)) {
            throw new \InvalidArgumentException(
                sprintf("SQL file '<info>%s</info>' does not exist.", $input->getArgument('shapefile'))
            );
        } elseif (!is_readable($shapeFile)) {
            throw new \InvalidArgumentException(
                sprintf("SQL file '<info>%s</info>' does not have read permissions.", $shapeFile)
            );
        }

        $DBALConnection = $this->getEntityManager()->getConnection();
        $DBALConnection->getConfiguration()->setSQLLogger(null);

        if ($input->getOption('truncate')) {
            $this->truncateTable($DBALConnection, $output);
        }

        try {
            $shpArgs = array(
                // '-W "latin1"',
                '-e',
                '-a', //append to existing base
                '-N skip', //null policy
                '-s 4326' //Srid
            );
            $importCmd = sprintf(
                'shp2pgsql %s -I %s %s | psql -U %s -d %s ',
                implode(' ', $shpArgs),
                $shapeFile,
                $this->getTableName(),
                $DBALConnection->getUsername(),
                $DBALConnection->getDatabase()
            );

            $output->writeln("<info>Importing file</info>");
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(sprintf('Executing command<info> %s</info>', $importCmd));
            }

            $process = new Process($importCmd);
            $process
                ->setTimeout(3600*2)
                ->mustRun();

            $output->writeln(sprintf('.shp file was imported into <info>%s</info>', $DBALConnection->getDatabase()));
        } catch (ProcessFailedException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
        }

    }

    protected function truncateTable($connection, OutputInterface $output)
    {
        $sequenceName = $this->getEntityManager()
            ->getClassMetadata($this->entityName)
            ->getSequenceName($connection->getDatabasePlatform());

        $connection->beginTransaction();
        try {
            $connection->query('DELETE FROM '.$this->entityName.';');
            $connection->query('ALTER SEQUENCE '.$sequenceName.'  RESTART WITH  1;');
            $connection->commit();
            $output->writeln(sprintf('<info>Table was successfully truncated.</info>'));
        } catch (\Exception $e) {
            $connection->rollback();
            $output->writeln(sprintf('<error>Error during truncate operation : %s </error>', $e->getMessage()));
        }
    }

}