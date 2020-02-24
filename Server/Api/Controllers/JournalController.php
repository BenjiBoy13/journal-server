<?php


namespace Server\Api\Controllers;


use DateTime;
use DateTimeZone;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use Exception;
use Server\Http\HttpRequest;
use Server\Models\JournalEntity;
use Server\Models\UserEntity;

class JournalController extends BaseController
{
    /**
     * @param HttpRequest $httpRequest
     * @param array $postArgs
     * @throws DBALException
     * @throws ORMException
     */
    public function addJournalAction (HttpRequest $httpRequest, array $postArgs)
    {
        $authenticated = $httpRequest->authenticated();

        $title = isset($postArgs['title']) ? $postArgs['title'] : null;
        $content = isset($postArgs['content']) ? $postArgs['content'] : null;
        $share = isset($postArgs['share']) ? $postArgs['share'] : null;

        if ($authenticated) {
            if ($title && $content && $share) {
                $em = $this->getOrmManager();
                $userRepository = $em->getRepository(UserEntity::class);
                $journalRepository = $em->getRepository(JournalEntity::class);

                $authUser = $userRepository->find($authenticated->data->id);

                $zone = new DateTimeZone("America/Monterrey");
                $datetime = new DateTime();
                $datetime->setTimezone($zone);

                if ($share == "false") {
                    $share = false;
                } else {
                    $share = true;
                }

                if ($journalRepository->findByUserAndDate($authenticated->data->id, $datetime)) {
                    $httpRequest->jsonResponse(406, "Failed to add new diary entry, already created for this day");
                    return;
                }

                $newJournal = new JournalEntity();
                $newJournal->setTitle($title);
                $newJournal->setContent($content);
                $newJournal->setCreationDate($datetime);
                $newJournal->setUser($authUser);
                $newJournal->setShare($share);

                $em->persist($newJournal);
                $em->flush();

                $httpRequest->jsonResponse(201, "Added diary entry", array(
                    'title' => $title,
                    'author' => $authUser->getNickname()
                ));

                return;
            }

            $httpRequest->jsonResponse(500, "Failed to add journal, invalid parameters");

            return;
        }

        $httpRequest->jsonResponse(401, "Access denied");
    }

    /**
     * @param HttpRequest $httpRequest
     * @throws DBALException
     * @throws ORMException
     */
    public function getUserJournals (HttpRequest $httpRequest)
    {
        $authenticated = $httpRequest->authenticated();

        if ($authenticated) {
            $userId = $authenticated->data->id;
            $em = $this->getOrmManager();
            $userRepository = $em->getRepository(UserEntity::class);

            $user = $userRepository->find($userId);
            $journals = array();

            foreach ($user->getJournals()->toArray() as $key => $journal) {
                $journals[$key]['id'] = $journal->getId();
                $journals[$key]['title'] = $journal->getTitle();
                $journals[$key]['content'] = $journal->getContent();
                $journals[$key]['share'] = $journal->getShare();
                $journals[$key]['date'] = $journal->getCreationDate();
            }

            $httpRequest->jsonResponse(200, "Fetched journals with success", array(
                'journals' => $journals
            ));

            return;
        }

        $httpRequest->jsonResponse(401, "Access denied");
    }

    /**
     * @param HttpRequest $httpRequest
     * @throws DBALException
     * @throws ORMException
     * @throws Exception
     */
    public function retrieveJournalAction (HttpRequest $httpRequest)
    {
        $getParams = $httpRequest->sanitizeData($_GET);
        $authenticated = $httpRequest->authenticated();
        $em = $this->getOrmManager();
        $journalRepository = $em->getRepository(JournalEntity::class);

        $date = isset($getParams['date']) ? $getParams['date'] : null;

        if ($date) {
            if ($authenticated) {
                $userId = $authenticated->data->id;
                $date = new DateTime($date);
                $entry = $journalRepository->findByUserAndDate($userId, $date);

                if ($entry) {
                    $httpRequest->jsonResponse(200, "Entry found", array(
                        'id' => $entry->getId(),
                        'title' => $entry->getTitle(),
                        'content' => $entry->getContent(),
                        'share' => $entry->getShare(),
                        'date' => $entry->getCreationDate()
                    ));

                    return;
                }

                $httpRequest->jsonResponse(404, "No entry found");
                return;
            }

            $httpRequest->jsonResponse(401, "Access denied");
            return;
        }

        $httpRequest->jsonResponse(500, "Invalid request, date missing");
    }

    /**
     * @param HttpRequest $httpRequest
     * @param array $postArgs
     * @throws Exception
     */
    public function editJournalAction (HttpRequest $httpRequest, array $postArgs)
    {
        $date = isset($postArgs['date']) ? $postArgs['date'] : null;
        $title = isset($postArgs['title']) ? $postArgs['title'] : null;
        $content = isset($postArgs['content']) ? $postArgs['content'] : null;
        $share = isset($postArgs['share']) ? $postArgs['share'] : null;
        $authenticated = $httpRequest->authenticated();
        $em = $this->getOrmManager();

        if ($authenticated) {
            if ($title && $content && $date && $share) {
                $today = strtotime(date("Y-m-d"));
                $dateStr = strtotime($date);

                if ($share == "false") {
                    $share = false;
                } else {
                    $share = true;
                }

                $dateDifference = $dateStr - $today;
                $difference = floor($dateDifference / (60 * 60 * 24));

                if ($difference == 0) {
                    $userId = $authenticated->data->id;

                    $journalRepository = $em->getRepository(JournalEntity::class);
                    $journal = $journalRepository->findByUserAndDate($userId, new DateTime($date));

                    if ($journal) {
                        $journal->setTitle($title);
                        $journal->setContent($content);
                        $journal->setShare($share);
                        $em->flush();

                        $httpRequest->jsonResponse(200, "Entry updated");
                        return;
                    }

                    $httpRequest->jsonResponse(404, "No journal found, cant update");
                    return;
                }

                $httpRequest->jsonResponse(406, "Cannot update old entries");
                return;
            }

            $httpRequest->jsonResponse(500, "Invalid parameters");
            return;
        }

        $httpRequest->jsonResponse(401, "Access denied");
    }

    /**
     * @param HttpRequest $httpRequest
     * @throws Exception
     */
    public function deleteJournalAction (HttpRequest $httpRequest)
    {
        $authenticated = $httpRequest->authenticated();
        $em = $this->getOrmManager();

        if ($authenticated) {
            $data = $httpRequest->getJsonBodyFromRequest();

            if (!$data) {
                $httpRequest->jsonResponse(500, "Invalid request body");
                return;
            }

            $id = $data->id;

            $journal = $em->find(JournalEntity::class, $id);

            if ($journal) {
                if ($journal->getUser()->getId() === $authenticated->data->id) {
                    $em->remove($journal);
                    $em->flush();

                    $httpRequest->jsonResponse(200, "Deleted with success");
                    return;
                }

                $httpRequest->jsonResponse(401, "You dont have permission to delete this entry");
                return;
            }

            $httpRequest->jsonResponse(404, "No journal found, nothing to delete");
            return;
        }

        $httpRequest->jsonResponse(401, "Access denied");
    }

    /**
     * @param HttpRequest $httpRequest
     * @throws DBALException
     * @throws ORMException
     */
    public function showSharedAction (HttpRequest $httpRequest)
    {
        $em = $this->getOrmManager();
        $journalRepository = $em->getRepository(JournalEntity::class);
        $results = $journalRepository->getSharedJournals();
        $entriesFound = array();

        foreach ($results as $key => $entry) {
            $entriesFound[$key]['id'] = $entry->getId();
            $entriesFound[$key]['user'] = $entry->getUser()->getNickname();
            $entriesFound[$key]['title'] = $entry->getTitle();
            $entriesFound[$key]['content'] = $entry->getContent();
            $entriesFound[$key]['date'] = $entry->getCreationDate();
        }

        $httpRequest->jsonResponse(200, "Retrieved shared journals with success", array(
            'journals' => $entriesFound
        ));;
    }
}