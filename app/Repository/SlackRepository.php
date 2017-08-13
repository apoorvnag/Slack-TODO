<?php
/**
 * Created by PhpStorm.
 * User: apoorv
 * Date: 12/08/17
 * Time: 2:54 PM
 */

App::import('Model', Slack::class);

class SlackRepository
{
    public function addtodo($requestParams)
    {
        /** @var Slack $Slack */
        $Slack = ClassRegistry::init(Slack::class);
        $conditions = [
            Slack::TOKEN => $requestParams[Slack::TOKEN],
            Slack::TEAM_ID => $requestParams[Slack::TEAM_ID],
            Slack::CHANNEL_ID => $requestParams[Slack::CHANNEL_ID],
            Slack::TEXT => $requestParams[Slack::TEXT],
        ];
        if (empty($requestParams[Slack::TOKEN]) || empty($requestParams[Slack::TEAM_ID])) {
            return 'Technical Error _*Invalid Request*_';
        }
        if (empty($requestParams[Slack::TEXT])) {
            return 'Error _*Task Name should not be empty*_';
        }
        if ($Slack->hasAny($conditions)) {
            return 'TODO already exist for _*' . $requestParams[Slack::TEXT] .  '*_';
        } else {
            $Slack->save($requestParams);
            return 'Added TODO for _*' . $requestParams[Slack::TEXT] . '*_';
        }
    }

    public function listtodos($requestParams)
    {
        $conditions = [
            Slack::TOKEN => $requestParams[Slack::TOKEN],
            Slack::TEAM_ID => $requestParams[Slack::TEAM_ID],
            Slack::CHANNEL_ID => $requestParams[Slack::CHANNEL_ID],
        ];

        // Filter out user only TODOs if its in DIRECT MESSAGES
        if ($requestParams[Slack::CHANNEL_NAME] == Slack::CHANNEL_DIRECT_MESSAGE) {
            $conditions[Slack::USER_ID] = $requestParams[Slack::USER_ID];
        }

        /** @var Slack $Slack */
        $Slack = ClassRegistry::init(Slack::class);
        $todos = $Slack->find('list', [
            'fields' => [Slack::TEXT],
            'conditions' => $conditions
        ]);

        return $todos;
    }

    public function marktodo($requestParams)
    {
        $conditions = [
            Slack::TOKEN => $requestParams[Slack::TOKEN],
            Slack::TEAM_ID => $requestParams[Slack::TEAM_ID],
            Slack::CHANNEL_ID => $requestParams[Slack::CHANNEL_ID],
            Slack::TEXT => $requestParams[Slack::TEXT],
        ];

        /** @var Slack $Slack */
        $Slack = ClassRegistry::init(Slack::class);
        if ($Slack->hasAny($conditions)) {
            return $Slack->deleteAll($conditions);
        } else {
            return false;
        }
    }
}