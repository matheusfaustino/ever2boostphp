<?php

namespace Ever2BoostPHP\Integration;

use Ever2BoostPHP\Helper\App;
use Evernote\Enml\Converter\EnmlToHtmlConverter;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class PrepareImport
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Evernote
     */
    private $evernote;

    /**
     * @var string
     */
    private $folderBoostnote;

    /**
     * PrepareImport constructor.
     *
     * @param OutputInterface $output
     * @param Filesystem      $filesystem
     * @param Evernote        $evernote
     * @param string          $folderBoostnote
     */
    public function __construct(
        OutputInterface $output,
        Filesystem $filesystem,
        Evernote $evernote,
        string $folderBoostnote
    ) {
        $this->output = $output;
        $this->filesystem = $filesystem;
        $this->evernote = $evernote;
        $this->folderBoostnote = $folderBoostnote;
    }

    /**
     * Process the notebooks and get the notes to persist it on drive
     */
    public function process(): void
    {
        $pathNotesDownloaded = App::homeFolder().'/notes.json';
        $notesDownloaded = [];
        \is_file($pathNotesDownloaded) and
        $notesDownloaded = \json_decode(\file_get_contents($pathNotesDownloaded), true);

        try {
            $notebooks = $this->evernote->getNotebooks();
            foreach ($notebooks as $notebook) {
                $this->output->writeln(['', 'Importing notes from <info>'.$notebook->getName().'</info>']);
                $notes = $this->evernote->getNotesFromNotebook($notebook);
                $progressBar = new ProgressBar($this->output, \count($notes));
                $progressBar->display();

                $transformer = new TransformEvernote2Boostnote(
                    $this->folderBoostnote,
                    new EnmlToHtmlConverter(),
                    $this->filesystem
                );

                foreach ($notes as $note) {
                    $progressBar->advance();
                    if (\in_array($note->guid, $notesDownloaded)) {
                        continue;
                    }

                    $note = $this->evernote->getNoteCompleteInfo($note->guid);
                    $transformer->dumpNoteFile($note);
                    $notesDownloaded[] = $note->guid;
                }

                $this->filesystem->dumpFile($pathNotesDownloaded, \json_encode($notesDownloaded));
            }
        } catch (\Exception $exception) {
            /* if there is an error, I save the last id searched */
            $this->filesystem->dumpFile($pathNotesDownloaded, \json_encode($notesDownloaded));

            $this->output->writeln([
                '',
                '<error>An error happened, due to Evernote access limitation. Try later (I don\'t know the exactly number, sorry)</error>',
            ]);
        }
    }
}
