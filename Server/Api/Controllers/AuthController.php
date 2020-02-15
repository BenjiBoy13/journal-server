<?php


namespace Server\Api\Controllers;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use Exception;
use Server\Http\HttpRequest;
use Server\Http\JWT;
use Server\Models\UserEntity;

class AuthController extends BaseController
{
    /**
     * @param HttpRequest $httpRequest
     * @param JWT $jwt
     * @param array $postArgs
     * @throws DBALException
     * @throws ORMException
     */
    public function loginAction (HttpRequest $httpRequest, JWT $jwt, array $postArgs)
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
                    'loggedIn' => true,
                    'token' => $jwt->createToken(array(
                        'id' => $user->getId(),
                        'nickname' => $user->getNickname(),
                        'creationDate' => $user->getCreationDate()
                    ))
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
        $nickname = isset($postArgs['nickname']) ? $postArgs['nickname'] : null;
        $email = isset($postArgs['email']) ? $postArgs['email'] : null;
        $password = isset($postArgs['password']) ? $postArgs['password'] : null;

        if ($nickname != null && $email != null && $password != null) {
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
            $newUser->setNickname($nickname);
            $newUser->setEmail($email);
            $newUser->setPassword($password);
            $newUser->setCreationDate($datetime);

            $em->persist($newUser);
            $em->flush();
            $httpRequest->jsonResponse(200, "The user $nickname was created");
            return;
        }

        $httpRequest->jsonResponse(500, "Invalid parameters");
    }

    public function myselfAction (HttpRequest $httpRequest)
    {
        $authenticated = $httpRequest->authenticated();

        if ($authenticated !== null) {
            $httpRequest->jsonResponse(200, "Authentication confirmed", array(
                'user' => $authenticated->data,
                'iat' => $authenticated->iat,
                'expiration' => $authenticated->exp
            ));

            return;
        }

        $httpRequest->jsonResponse(401, "Access denied");
    }
}