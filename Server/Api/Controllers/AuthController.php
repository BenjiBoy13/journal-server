<?php


namespace Server\Api\Controllers;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use Exception;
use Server\Api\Services\MailingService;
use Server\Http\HttpRequest;
use Server\Http\JWT;
use Server\Models\UserEntity;

class AuthController extends BaseController
{
    /**
     * @param JWT $jwt
     * @param array $postArgs
     * @throws DBALException
     * @throws ORMException
     */
    public function loginAction (JWT $jwt, array $postArgs)
    {
        $email = isset($postArgs['email']) ? $postArgs['email'] : null;
        $password = isset($postArgs['password']) ? $postArgs['password'] : null;

        if ($email != null && $password != null) {
            $em = $this->getOrmManager();

            $repository = $em->getRepository('Server\Models\UserEntity');
            $user = $repository->findUserByEmail($email);

            if (empty($user)) {
                $this->httpRequest->jsonResponse(200, "Email not registered", array(
                    'loggedIn' => false
                ));

                return;
            }

            $user = $user[0];

            if (password_verify($password, $user->getPassword())) {
                if ($user->getToken()) {
                    $userToken = $user->getToken();

                    if (!$jwt->verifyToken($userToken)) {
                        $userToken = $jwt->generateNewToken($user);

                        $user->setToken($userToken);
                        $em->flush();
                    }

                    $this->httpRequest->jsonResponse(200, "Logged in with success, welcome back", array(
                        'loggedIn' => true,
                        'token' => $userToken
                    ));

                    return;
                }

                $newToken = $jwt->generateNewToken($user);
                $user->setToken($newToken);
                $em->flush();

                $this->httpRequest->jsonResponse(200, "Logged in with success", array(
                    'loggedIn' => true,
                    'token' => $newToken
                ));

                return;
            }

            $this->httpRequest->jsonResponse(200, "Invalid credentials", array(
                'loggedIn' => false
            ));
        }
    }

    /**
     * @param JWT $jwt
     * @param array $postArgs
     * @throws DBALException
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function registerAction (JWT $jwt, MailingService $mailingService, array $postArgs)
    {
        $nickname = isset($postArgs['nickname']) ? $postArgs['nickname'] : null;
        $email = isset($postArgs['email']) ? $postArgs['email'] : null;
        $password = isset($postArgs['password']) ? $postArgs['password'] : null;

        if ($nickname != null && $email != null && $password != null) {
            $em = $this->getOrmManager();

            $repository = $em->getRepository('Server\Models\UserEntity');
            $userAlreadyCreated = $repository->findUserByEmail($email);

            if (!empty($userAlreadyCreated)) {
                $this->httpRequest->jsonResponse(500, "Email already in use");
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

            $emailConfirmationToken = $jwt->createToken(array(
                'email' => $email
            ), true);

            $emailContent = "
                <h3> Confirm your email </h3>
                <p> Go to $emailConfirmationToken </p>
            ";

            $mailed = $mailingService->sendMail($email, $nickname, $emailContent);

            $this->httpRequest->jsonResponse(200, "The user $nickname was created", array(
                'mailed' => $mailed ? "Validation email send" : "Could not send validation email"
            ));

            return;
        }

        $this->httpRequest->jsonResponse(500, "Invalid parameters");
    }

    public function confirmEmailAction (JWT $jwt)
    {
        $getParams = $this->httpRequest->sanitizeData($_GET);
        $em = $this->getOrmManager();
        $userRepository = $em->getRepository(UserEntity::class);

        $token = isset($getParams['token']) ? $getParams['token'] : null;

        if ($token) {
            $tokenContent = $jwt->verifyToken($token);

            if ($tokenContent) {
                $mailToVerified = $tokenContent->data->email;
                $user = $userRepository->findUserByEmail($mailToVerified);

                if (!empty($user)) {
                    $user = $user[0];

                    if ($user->getEmailVerified()) {
                        $this->httpRequest->jsonResponse(406, "Email already verified");
                        return;
                    }

                    $user->setEmailVerified(true);
                    $em->flush();
                    $this->httpRequest->jsonResponse(200, "Email address verified with success");
                    return;
                }

                $this->httpRequest->jsonResponse(500, "User not found");
                return;
            }

            $this->httpRequest->jsonResponse(401, "Invalid token");
            return;
        }

        $this->httpRequest->jsonResponse(500, "Invalid parameters");
    }

    /**
     * @param JWT $jwt
     * @param MailingService $mailingService
     * @throws DBALException
     * @throws ORMException
     */
    public function resendEmailTokenAction (JWT $jwt, MailingService $mailingService)
    {
        $authenticated = $this->httpRequest->authenticated();
        $em = $this->getOrmManager();
        $userRepository = $em->getRepository(UserEntity::class);

        if ($authenticated) {
            $userId = $authenticated->data->id;
            $user = $userRepository->find($userId);

            if ($user) {
                $userEmail = $user->getEmail();
                $userNickname = $user->getNickname();

                if ($user->getEmailVerified()) {
                    $this->httpRequest->jsonResponse(406, "Already confirmed email address");
                    return;
                }

                $emailConfirmationToken = $jwt->createToken(array(
                    'email' => $userEmail
                ), true);

                $emailContent = "
                    <h3> Confirm your email </h3>
                    <p> Go to $emailConfirmationToken </p>
                ";

                $mailed = $mailingService->sendMail($userEmail, $userNickname, $emailContent);
                $this->httpRequest->jsonResponse(200, "Done", array(
                    'mailed' => $mailed ? "Email send with success" : "Could not send email"
                ));

                return;
            }

            $this->httpRequest->jsonResponse(500, "User not found");
            return;
        }

        $this->httpRequest->jsonResponse(401, "Access denied");
    }

    public function myselfAction ()
    {
        $authenticated = $this->httpRequest->authenticated();

        if ($authenticated !== null) {
            $this->httpRequest->jsonResponse(200, "Authentication confirmed", array(
                'user' => $authenticated->data,
                'iat' => $authenticated->iat,
                'expiration' => $authenticated->exp
            ));

            return;
        }

        $this->httpRequest->jsonResponse(401, "Access denied");
    }
}