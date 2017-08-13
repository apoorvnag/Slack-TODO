<?php
/**
 * Created by PhpStorm.
 * User: apoorv
 * Date: 12/08/17
 * Time: 3:39 AM
 */

class SlackAdapter
{
    const TOKEN = 'token';
    const TEAM_ID = 'team_id';
    const TEAM_DOMAIN = 'team_domain';
    const CHANNEL_ID = 'channel_id';
    const CHANNEL_NAME = 'channel_name';
    const USER_ID = 'user_id';
    const USER_NAME = 'user_name';
    const COMMAND = 'command';
    const TEXT = 'text';
    const RESPONSE_URL = 'response_url';
    const TRIGGER_ID = 'trigger_id';

    const RESPONSE_PREFIX = 'TODO BOT: ';

    private $request;

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }



    public function __construct($request)
    {
        $this->setRequest($request);
    }

    /**
     * @return array
     */
    public function parseRequest()
    {
        $requestParams = [];

        $data = !empty($this->getRequest()->data) ? $this->getRequest()->data : [];

        $requestParams[self::TOKEN] = isset($data[self::TOKEN]) ? $data[self::TOKEN] : '';
        $requestParams[self::TEAM_ID] = isset($data[self::TEAM_ID]) ? $data[self::TEAM_ID] : '';
        $requestParams[self::TEAM_DOMAIN] = isset($data[self::TEAM_DOMAIN]) ? $data[self::TEAM_DOMAIN] : '';
        $requestParams[self::CHANNEL_ID] = isset($data[self::CHANNEL_ID]) ? $data[self::CHANNEL_ID] : '';
        $requestParams[self::CHANNEL_NAME] = isset($data[self::CHANNEL_NAME]) ? $data[self::CHANNEL_NAME] : '';
        $requestParams[self::USER_ID] = isset($data[self::USER_ID]) ? $data[self::USER_ID] : '';
        $requestParams[self::USER_NAME] = isset($data[self::USER_NAME]) ? $data[self::USER_NAME] : '';
        $requestParams[self::COMMAND] = isset($data[self::COMMAND]) ? $data[self::COMMAND] : '';
        $requestParams[self::TEXT] = isset($data[self::TEXT]) ? trim($data[self::TEXT]) : '';
        $requestParams[self::RESPONSE_URL] = isset($data[self::RESPONSE_URL]) ? $data[self::RESPONSE_URL] : '';
        $requestParams[self::TRIGGER_ID] = isset($data[self::TRIGGER_ID]) ? $data[self::TRIGGER_ID] : '';

        return $requestParams;
    }

    public function parseResponse($response)
    {
        return self::RESPONSE_PREFIX . "\r\n" . $response;
    }

    /**
     * @param $todos
     * @return string
     */
    public function parseTodoList($todos)
    {
        $response = '';

        foreach ($todos as $todo){
            if ($todo) {
                $response .= " " . $todo . "\r\n";
            }
        }

        if (!$response) {
            $response = 'No TODOs';
        }

        $response = $this->parseResponse($response);

        return $response;
    }

}