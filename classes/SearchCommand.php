<?php
/**
 * SearchCommand class
 *
 * PHP Version 5.4
 *
 * @category API
 * @package  Infogreffe
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  LGPL https://www.gnu.org/copyleft/lesser.html
 * @link     https://github.com/Rudloff/infogreffe-unofficial-api
 * */
namespace InfogreffeUnofficial;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Psr\Log\LogLevel;

/**
 * CLI search command
 *
 * PHP Version 5.4
 *
 * @category API
 * @package  Infogreffe
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  LGPL https://www.gnu.org/copyleft/lesser.html
 * @link     https://github.com/Rudloff/infogreffe-unofficial-api
 * */
class SearchCommand extends Command
{
    /**
     * Configure command
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('search')
            ->setDescription('Search')
            ->addArgument(
                'query',
                InputArgument::REQUIRED,
                'Search query'
            )
            ->addOption(
                'url',
                null,
                InputOption::VALUE_NONE,
                'If set, will print the URL of each result'
            )->addOption(
                'debug',
                null,
                InputOption::VALUE_NONE,
                'If set, will print every HTTP query'
            );
    }

    /**
     * Execute command
     * @param  InputInterface  $input  Input
     * @param  OutputInterface $output Output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('debug')) {
            $verbosityLevelMap = array(
                LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
                LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
            );
        } else {
            $verbosityLevelMap = array();
        }
        $result = Infogreffe::search($input->getArgument('query'), new ConsoleLogger($output, $verbosityLevelMap));
        if (empty($result)) {
            $output->writeln('<error>No result :/</error>');
        } else {
            $table = new Table($output);
            if (!$input->getOption('url')) {
                $table->setHeaders(
                    array(
                        'Name',
                        'SIRET',
                        'Address',
                        'Removed'
                    )
                );
            }
            foreach ($result as $org) {
                if ($input->getOption('url')) {
                    $table->addRow(array($org->getURL()));
                } else {
                    $org->address['lines'] = implode(PHP_EOL, $org->address['lines']);
                    $rows = array(
                        $org->name, $org->siret,
                        $org->address['lines'].PHP_EOL.$org->address['zipcode'].' '.$org->address['city']
                    );
                    if ($org->removed) {
                        $rows[] = '❌';
                    } else {
                        $rows[] = '';
                    }
                    $table->addRow($rows);
                }
            }
            $table->render();
        }
    }
}
