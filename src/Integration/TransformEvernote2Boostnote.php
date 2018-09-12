<?php

namespace Ever2BoostPHP\Integration;

use EDAM\Types\Resource;
use Ever2BoostPHP\Helper\App;
use Evernote\Enml\Converter\EnmlToHtmlConverter;
use Evernote\Model\Note;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class TransformEvernote2Boostnote
 *
 * @package Ever2BoostPHP\Integration
 */
class TransformEvernote2Boostnote
{
    private const RESOURCE_WITHNO_NAME = 'imported_';
    private const REGEX_IMAGE = '/\/images\/%s.*?(?=")/';

    /**
     * @var string
     */
    private $categoryBoostnote;

    /**
     * @var EnmlToHtmlConverter
     */
    private $converter;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $templateFile;

    /**
     * TransformEvernote2Boostnote constructor.
     *
     * @param string              $categoryBoostnote
     * @param EnmlToHtmlConverter $converter
     * @param Filesystem          $filesystem
     */
    public function __construct(string $categoryBoostnote, EnmlToHtmlConverter $converter, Filesystem $filesystem)
    {
        $this->categoryBoostnote = $categoryBoostnote;
        $this->converter = $converter;
        $this->filesystem = $filesystem;
    }

    /**
     * @param Note $note
     *
     * @return string
     */
    private function noteContentToHtml(Note $note)
    {
        // the html of the note keep the apos code... so change it to converter it properly
        $enexContent = str_replace('&apos;', '\'', $note->getContent());

        return $this->converter->convertToHtml($enexContent);
    }

    /**
     * Save resources from note and return placeholder and replacement
     * for resouces (images, etc) into bootsnote schema using relative path
     *
     * @param Note   $note
     *
     * @param string $noteId
     *
     * @return array [['placeholders'],['newPaths']]
     */
    private function saveResourceAndReturnReplaces(Note $note, string $noteId): array
    {
        if ( ! \count($note->getResources())) {
            return [null, null];
        }

        $folderOutput = App::homeFolder().'/output/attachments/'.$noteId;

        $placeholders = $newPaths = [];
        foreach ($note->getResources() as $resource) {
            /* @var $resource Resource */
            $imageHashEvernote = \bin2hex($resource->data->bodyHash);
            $imageRealName = $resource->attributes->fileName ?? uniqid(self::RESOURCE_WITHNO_NAME);

            $placeholders[] = \sprintf(self::REGEX_IMAGE, $imageHashEvernote);
            $newPaths[] = sprintf(':storage/%s/%s', $noteId, $imageRealName);

            $filename = $folderOutput.'/'.$imageRealName;
            $this->filesystem->dumpFile($filename, $resource->data->body);
        }

        return [$placeholders, $newPaths];
    }

    /**
     * Create string based on boostnote template
     *
     * @param Note $note
     */
    public function dumpNoteFile(Note $note): void
    {
        $noteId = (Uuid::uuid4())->toString();
        $htmlNote = $this->noteContentToHtml($note);
        [$placeholders, $newPaths] = $this->saveResourceAndReturnReplaces($note, $noteId);

        $placeholders and
        $newPaths and
        $htmlNote = \preg_replace(
            $placeholders,
            $newPaths,
            $htmlNote
        );

        $folderOutput = App::homeFolder().'/output/notes/';
        $filenameNote = $folderOutput.$noteId.'.cson';
        $this->templateFile or $this->templateFile = \file_get_contents(__DIR__.'/../template_note');
        $this->filesystem->dumpFile(
            $filenameNote,
            \sprintf(
                $this->templateFile,
                (new \DateTime())->format('Y-m-d\TH:i:s\Z'),
                (new \DateTime())->format('Y-m-d\TH:i:s\Z'),
                $htmlNote,
                $this->categoryBoostnote,
                $note->getTitle()
            )
        );
    }
}
