<?php

namespace Ever2BoostPHP\Command;

use Ever2BoostPHP\Helper\App;
use Ever2BoostPHP\Integration\Boostnote;
use Ever2BoostPHP\Integration\Evernote;
use Ever2BoostPHP\Integration\PrepareImport;
use Ever2BoostPHP\Server\OAuthConnector;
use React\EventLoop\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Class Ever2BoostPHP
 */
final class Ever2BoostPHP extends Command
{
    public const NAME = 'ever2bootsphp';
    public const VERSION = '1.0.0';
    public const TOKEN_FILENAME = 'token';
    private const DEFAULT_PORT_LOCALSERVER = 9900;

    /**
     * Command conf
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Import notes from evernote to boostnote')
            ->addArgument('boostnoteDir', InputArgument::REQUIRED, 'Boostnote Location folder')
            ->addOption('consumerKey', 'ck', InputOption::VALUE_OPTIONAL)
            ->addOption('consumerSecret', 'cs', InputOption::VALUE_OPTIONAL)
            ->addOption('overwriteToken', 'o', InputOption::VALUE_NONE, 'Do you want overwrite token?')
            ->addOption('sandbox', 's', InputOption::VALUE_OPTIONAL, 'Your key and secret is sandbox?', false);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $boostnote = new Boostnote(new Filesystem(), $input->getArgument('boostnoteDir'));
        $folder = $this->selectFolderBoostnote($input, $output, $boostnote);

        $token = $this->requestToken($input, $output);
        $evernote = new Evernote($token, $input->getOption('sandbox'));

        $output->writeln(\sprintf(
            '<info>%s</info>',
            \count($evernote->getNotebooksCount()->notebookCounts).' notebooks found'
        ));
        $output->writeln('<comment>If you have a lot of notes, it may not finish the import in one run</comment>');

        $startTime = new \DateTime();

        $prepare = new PrepareImport(
            $output,
            new Filesystem(),
            $evernote,
            $folder
        );
        $prepare->process();

        $interval = $startTime->diff(new \DateTime());
        $output->writeln([
            '',
            '',
            \sprintf('It took %s to conclude.', $interval->format('%H hours, %i minutes and %s seconds')),
            '',
            \sprintf(
                '<info>Your notes are located on %s. Copy and paste the folders on boostnote root folder.</info>',
                App::homeFolder()
            ),
            '<comment>It\'s good to have a backup of your current boostnote folder before the paste.</comment>',
        ]);

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return string
     */
    private function requestToken(InputInterface $input, OutputInterface $output): string
    {
        $tokenPath = App::homeFolder().'/'.self::TOKEN_FILENAME;
        $hasFileToken = is_file($tokenPath);

        $token = null;
        if ( ! $hasFileToken || $input->getOption('overwriteToken')) {
            $output->writeln('Requesting Token...');
            $connector = new OAuthConnector(
                Factory::create(),
                self::DEFAULT_PORT_LOCALSERVER,
                $input->getOption('sandbox'),
                $input->getOption('consumerKey') ?? 'electronimportexport',
                $input->getOption('consumerSecret') ?? 'bcc3bce1eb287730',
                new Browser(new Process('')),
                $output,
                new Filesystem()
            );

            return $connector->receiveToken();
        }

        $token or $token = file_get_contents($tokenPath);

        return $token;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Boostnote       $boostnoteClass
     *
     * @return string
     */
    private function selectFolderBoostnote(
        InputInterface $input,
        OutputInterface $output,
        Boostnote $boostnoteClass
    ): string {
        $folderOptions = [];
        foreach ($boostnoteClass->getFolders() as $folder) {
            $folderOptions[$folder['key']] = $folder['name'];
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            '<question>Which folder you would like to import the list on boostnote?</question>',
            $folderOptions
        );
        $question->setErrorMessage('Folder %s is invalid.');
        $folder = $helper->ask($input, $output, $question);

        return $folder;
    }
}
