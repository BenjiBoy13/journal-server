<?php


namespace Server\Repositories;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityRepository;
use Exception;
use Server\Models\JournalEntity;


class JournalRepository extends EntityRepository
{
    /**
     * @param int $userId
     * @param DateTime $date
     * @return JournalEntity|null
     * @throws Exception
     */
    public function findByUserAndDate (int $userId, DateTime $date) : ?JournalEntity
    {
        $zone = new DateTimeZone("America/Monterrey");
        $from = new DateTime($date->format("Y-m-d")." 00:00:00");
        $to   = new DateTime($date->format("Y-m-d")." 23:59:59");
        $from->setTimezone($zone);
        $to->setTimezone($zone);

        $dql = /** @lang DQL */ "
            SELECT j 
            FROM Server\Models\JournalEntity j, Server\Models\UserEntity u
            WHERE u.id = j.user
            AND j.user = :id
            AND j.creationDate BETWEEN :from and :to
        ";

        $results = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('id', $userId)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getResult();

        if (!empty($results)) {
            return $results[0];
        }

        return null;
    }

    public function getSharedJournals () : array
    {
        $dql = /** @lang DQL */ "
            SELECT j
            FROM Server\Models\JournalEntity j
            WHERE j.share = :share
        ";

        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('share', 1)
            ->getResult();
    }
}