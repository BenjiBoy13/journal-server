<?php


namespace Server\Api\Controllers;


use DateTime;
use DateTimeZone;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use Server\Http\HttpRequest;
use Server\Models\JournalEntity;

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

        if ($authenticated) {
            if ($title !== null && $content !== null) {
                $em = $this->getOrmManager();
                $userRepository = $em->getRepository('Server\Models\UserEntity');
                $journalRepository = $em->getRepository('Server\Models\JournalEntity');

                $authUser = $userRepository->find($authenticated->data->id);

                $zone = new DateTimeZone("America/Monterrey");
                $datetime = new DateTime();
                $datetime->setTimezone($zone);

                if ($journalRepository->findByUserAndDate($authenticated->data->id, $datetime)) {
                    $httpRequest->jsonResponse(406, "Failed to add new diary entry, already created for this day");
                    return;
                }

                $newJournal = new JournalEntity();
                $newJournal->setTitle($title);
                $newJournal->setContent($content);
                $newJournal->setCreationDate($datetime);
                $newJournal->setUser($authUser);

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
            $userRepository = $em->getRepository('Server\Models\UserEntity');

            $user = $userRepository->find($userId);
            $journals = array();

            foreach ($user->getJournals()->toArray() as $key => $journal) {
                $journals[$key]['title'] = $journal->getTitle();
                $journals[$key]['content'] = $journal->getContent();
                $journals[$key]['date'] = $journal->getCreationDate();
            }

            $httpRequest->jsonResponse(200, "Fetched journals with success", array(
                'journals' => $journals
            ));

            return;
        }

        $httpRequest->jsonResponse(401, "Access denied");
    }
}