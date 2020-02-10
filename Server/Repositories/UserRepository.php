<?php

namespace Server\Repositories;


class UserRepository extends \Doctrine\ORM\EntityRepository
{
    public function findUserByEmail (string $email)
    {
        $dql = /** @lang DQL */ "SELECT u FROM Server\Models\UserEntity u WHERE u.email = ?1";

        return $this->getEntityManager()->createQuery($dql)->setParameter(1, $email)->getResult();
    }
}
