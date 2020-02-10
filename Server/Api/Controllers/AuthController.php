<?php


namespace Server\Api\Controllers;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use Exception;
use Server\Http\HttpRequest;
use Server\Models\UserEntity;

class AuthController extends BaseController
{
    /**
     * @param HttpRequest $httpRequest
     * @param array $postArgs
     * @throws DBALException
     * @throws ORMException
     */
    public function loginAction (HttpRequest $httpRequest, array $postArgs)
    {
        $email = isset($postArgs['email']) ? $postArgs['email'] : null;
        $password = isset($postArgs['password']) ? $postArgs['password'] : null;

        if ($email != null && $password != null) {
            $em = $this->getOrmManager();

            $repository = $em->getRepository('Server\Models\UserEntity');
            $user = $repository->findUserByEmail($email);

            if (empty($user)) {
                $httpRequest->jsonResponse(200, "Email not registered", array(
                    'loggedIn' => false
                ));

                return;
            }

            $user = $user[0];

            if (password_verify($password, $user->getPassword())) {
                $httpRequest->jsonResponse(200, "Success, Welcome back", array(
                    'loggedIn' => true
                ));

                return;
            }

            $httpRequest->jsonResponse(200, "Invalid credentials", array(
                'loggedIn' => false
            ));
        }
    }

    /**
     * @param HttpRequest $httpRequest
     * @param array $postArgs
     * @throws Exception
     */
    public function registerAction (HttpRequest $httpRequest, array $postArgs)
    {
        $username = isset($postArgs['username']) ? $postArgs['username'] : null;
        $fullName = isset($postArgs['fullName']) ? $postArgs['fullName'] : null;
        $email = isset($postArgs['email']) ? $postArgs['email'] : null;
        $password = isset($postArgs['password']) ? $postArgs['password'] : null;

        if ($username != null && $fullName != null && $email != null && $password != null) {
            $em = $this->getOrmManager();

            $repository = $em->getRepository('Server\Models\UserEntity');
            $userAlreadyCreated = $repository->findUserByEmail($email);

            if (!empty($userAlreadyCreated)) {
                $httpRequest->jsonResponse(500, "Email already in use");
                return;
            }

            $zone = new DateTimeZone("America/Monterrey");
            $datetime = new DateTime();
            $datetime->setTimezone($zone);

            $newUser = new UserEntity();
            $newUser->setUsername($username);
            $newUser->setFullName($fullName);
            $newUser->setEmail($email);
            $newUser->setPassword($password);
            $newUser->setCreationDate($datetime);

            $em->persist($newUser);
            $em->flush();
            $httpRequest->jsonResponse(200, "The user $username was created");
            return;
        }

        $httpRequest->jsonResponse(500, "Invalid parameters");
    }
}