<?php

namespace Ever2BoostPHP\Integration;

use EDAM\NoteStore\NoteCollectionCounts;
use EDAM\NoteStore\NoteFilter;
use Evernote\Client;
use Evernote\Model\Note;
use Evernote\Model\Notebook;
use Evernote\Model\SearchResult;

class Evernote
{
    /**
     * @var string
     */
    private $authToken;

    /**
     * @var bool
     */
    private $sandbox;

    /**
     * @var Client
     */
    private $client;

    /**
     * Evernote constructor.
     *
     * @param string $authToken
     * @param bool   $sandbox
     */
    public function __construct(string $authToken, bool $sandbox)
    {
        $this->authToken = $authToken;
        $this->sandbox = $sandbox;
        $this->client = new Client($authToken, $sandbox);
    }

    /**
     * @return Notebook[]
     */
    public function getNotebooks(): array
    {
        return $this->client->listNotebooks();
    }

    /**
     * @param NoteFilter|null $noteFilter
     *
     * @return NoteCollectionCounts
     */
    public function getNotebooksCount(?NoteFilter $noteFilter = null): NoteCollectionCounts
    {
        return $this->client
            ->getUserNotestore()
            ->findNoteCounts(
                $this->authToken,
                $noteFilter ?? new NoteFilter(),
                false
            );
    }

    /**
     * There is no way to know how to get the count of a notebook
     * in sdk there is no function that returns it (they just return
     * the quantity of notebooks and tags inside the notebooks (very useful info :p))
     * So, I just try to get all the notes inside a notebook.
     *
     * @param Notebook $notebook
     * @param int      $scope
     * @param int      $order
     * @param int      $count
     *
     * @return SearchResult[]
     */
    public function getNotesFromNotebook(
        Notebook $notebook,
        int $scope = Client::SEARCH_SCOPE_ALL,
        int $order = Client::SORT_ORDER_REVERSE | Client::SORT_ORDER_RECENTLY_CREATED,
        int $count = 9999
    ): array {
        return $this->client->findNotesWithSearch(
            '',
            $notebook,
            $scope,
            $order,
            $count
        );
    }

    /**
     * The notes from search contains half of the information of a note
     *
     * @param string $guid
     *
     * @return Note
     */
    public function getNoteCompleteInfo(string $guid): Note
    {
        return $this->client->getNote($guid);
    }
}
