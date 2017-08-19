<?php
/**
 * Created by Sublime.
 * User: Sahil Chaddha
 * Date: 19/08/17
 * Time: 3:17 AM
 */

class OAuthAdapter
{
    const TOKEN         = 'access_token';
    const TEAM_ID       = 'team_id';
    const TEAM_NAME     = 'team_name';
    const USER_ID       = 'user_id';
    const BOT           = 'bot';
    const BOT_USER_ID   = 'bot_user_id';
    const BOT_TOKEN     = 'bot_access_token';
    const IS_VERIFIED   = 'ok';
 

    private $response;

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $request
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }



    public function __construct($response)
    {
        $this->setResponse(json_decode($response, true));
    }

    /**
     * @return array
     */
    public function parseResponse()
    {
        $responseParams = [];

        $data = !empty($this->getResponse()) ? $this->getResponse() : [];
        $responseParams[self::IS_VERIFIED] = isset($data[self::IS_VERIFIED]) ? $data[self::IS_VERIFIED] : false;
        $responseParams[self::TOKEN] = isset($data[self::TOKEN]) ? $data[self::TOKEN] : '';
        $responseParams[self::TEAM_ID] = isset($data[self::TEAM_ID]) ? $data[self::TEAM_ID] : '';
        $responseParams[self::TEAM_NAME] = isset($data[self::TEAM_NAME]) ? $data[self::TEAM_NAME] : '';
        $responseParams[self::USER_ID] = isset($data[self::USER_ID]) ? $data[self::USER_ID] : '';
        $responseParams[self::BOT_USER_ID] = isset($data[self::BOT]) && isset($data[self::BOT][self::BOT_USER_ID])  ? $data[self::BOT][self::BOT_USER_ID] : '';
        $responseParams[self::BOT_TOKEN] = isset($data[self::BOT]) && isset($data[self::BOT][self::BOT_TOKEN]) ? $data[self::BOT][self::BOT_TOKEN] : '';
        return $responseParams;
    }

}