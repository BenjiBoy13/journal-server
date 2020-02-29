<?php


namespace Server\Http;


use Server\Core\YamlParser;
use Server\Models\UserEntity;

class JWT
{
    private string $secret;

    public function __construct()
    {
        $ymlParser = new YamlParser();
        $authSettings = $ymlParser->parseIt('./config/auth.yml');
        $this->secret = $authSettings['secret'];
    }

    public function createToken (array $data, $emailToken = false)
    {
        $expirationTime = 2419200;

        if ($emailToken) {
            $expirationTime = 86400;
        }

        $time = time();
        $jwtToken = array(
            'iat' => $time,
            'exp' => $time + $expirationTime,
            'data' => $data
        );

        return \Firebase\JWT\JWT::encode($jwtToken, $this->secret);
    }

    public function verifyToken (string $token) : ?object
    {
        try {
            return \Firebase\JWT\JWT::decode($token, $this->secret, array('HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function generateNewToken (UserEntity $user)
    {
        return $this->createToken(
            array(
                'id' => $user->getId(),
                'nickname' => $user->getNickname(),
                'creationDate' => $user->getCreationDate()
            )
        );
    }
}