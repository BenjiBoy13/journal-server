<?php


namespace Server\Http;


use Server\Core\YamlParser;

class JWT
{
    private string $secret;

    public function __construct()
    {
        $ymlParser = new YamlParser();
        $authSettings = $ymlParser->parseIt('./config/auth.yml');
        $this->secret = $authSettings['secret'];
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