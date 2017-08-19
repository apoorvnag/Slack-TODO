<?php
/**
 * Created by Sublime.
 * User: Sahil Chaddha
 * Date: 19/08/17
 * Time: 3:30 AM
 */

App::import('Model', OAuth::class);

class OAuthRepository
{
    public function saveToDB($response)
    {
        /** @var OAuth $OAuth */
        $OAuth = ClassRegistry::init(OAuth::class);
        $params = [
            OAuth::TOKEN => $response[OAuth::TOKEN],
            OAuth::TEAM_ID => $response[OAuth::TEAM_ID],
            OAuth::USER_ID => $response[OAuth::USER_ID],
            OAuth::TEAM_NAME => $response[OAuth::TEAM_NAME],
            OAuth::BOT_USER_ID => $response[OAuth::BOT_USER_ID],
            OAuth::BOT_TOKEN => $response[OAuth::BOT_TOKEN],
            OAuth::IS_VERIFIED => $response['ok'],
        ];

        $conditions = [
            OAuth::TEAM_ID => $response[OAuth::TEAM_ID]
        ];
          
        //TODO Refactor : Convert it to Update instead of Delete
        if ($OAuth->hasAny($conditions)) {
            $OAuth->deleteAll(array(OAuth::TEAM_ID => $params[OAuth::TEAM_ID]), false);
        }

            return $OAuth->save($params);
    }
}