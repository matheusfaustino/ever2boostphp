<?php

namespace Ever2BoostPHP\Integration;


use Ever2BoostPHP\Exception\NoBoostnoteFile;
use Symfony\Component\Filesystem\Filesystem;

class Boostnote
{
    private const BOOSTNOTE_JSON_FILE = 'boostnote.json';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $boostnoteFolder;

    /**
     * Boostnote constructor.
     *
     * @param Filesystem $filesystem
     * @param string     $boostnoteFolder
     */
    public function __construct(Filesystem $filesystem, string $boostnoteFolder)
    {
        $this->filesystem = $filesystem;
        $this->boostnoteFolder = $boostnoteFolder;
    }

    /**
     * @return array [['key' => string, 'name' => string], ...]
     */
    public function getFolders(): array
    {
        $path = \sprintf('%s/%s', $this->boostnoteFolder, self::BOOSTNOTE_JSON_FILE);
        if ($this->filesystem->exists($path) === false) {
            throw new NoBoostnoteFile();
        }

        $boostnoteFile = \json_decode(\file_get_contents($path), true);

        return $boostnoteFile['folders'];
    }
}
