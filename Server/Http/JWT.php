<?php


namespace Server\Http;


use Symfony\Component\Yaml\Yaml;

class JWT
{
    private string $secret;
    private array $algorithm;

    public function __construct()
    {
        $authSettings = Yaml::parseFile('./config/auth.yml');
        $this->secret = $authSettings['secret'];
        $this->algorithm = array($authSettings['algorithm']);
    }

    public function createToken (array $data)
    {
        $time = time();
        $jwtToken = array(
            'iat' => $time,
            'exp' => $time + 2419200,
            'data' => $data
        );

        return \Firebase\JWT\JWT::encode($jwtToken, $this->secret);
    }

    public function verifyToken (string $token)
    {
        return \Firebase\JWT\JWT::decode($token, $this->secret, array('HS256'));
    }
}