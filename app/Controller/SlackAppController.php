<?php
/**
 * Created by PhpStorm.
 * User: apoorv
 * Date: 12/08/17
 * Time: 1:32 AM
 */

App::import('Adapter', SlackAdapter::class);
App::import('Repository', SlackRepository::class);

/**
 * Class SlackAppController
 *
 * API Controller for Slack APP - Todolist incoming request
 */
class SlackAppController extends AppController
{

    /**
     * Adds a task
     *
     * @return string
     */
    public function addtodo()
    {
        $this->autoRender = false;
        $this->layout = false;

        $response = 'Sorry !!! Something went wrong.';
        if (!$this->request->isPost()) {
            return $response;
        }

        /** @var SlackAdapter $SlackAdapter */
        $SlackAdapter = new SlackAdapter($this->request);
        $requestParams = $SlackAdapter->parseRequest();

        if (empty($requestParams)) {
            return 'Task was missing.
             Try again with a task name after the command.';
        }

        /** @var SlackRepository $SlackRepository */
        $SlackRepository = new SlackRepository();
        $response = $SlackRepository->addtodo($requestParams);

        return $SlackAdapter->parseResponse($response);
    }

    /**
     * Retrieves all tasks of a slack channel
     *
     * @return string
     */
    public function listtodos()
    {
        $this->autoRender = false;
        $this->layout = false;

        $response = 'Sorry !!! Something went wrong.';
        if (!$this->request->isPost()) {
            return $response;
        }

        /** @var SlackAdapter $SlackAdapter */
        $SlackAdapter = new SlackAdapter($this->request);
        $requestParams = $SlackAdapter->parseRequest();

        /** @var SlackRepository $SlackRepository */
        $SlackRepository = new SlackRepository();
        $todos = $SlackRepository->listtodos($requestParams);

        return $SlackAdapter->parseTodoList($todos);
    }

    /**
     * Marks the Todo_item as completed by deleting it
     *
     * @return string
     */
    public function marktodo()
    {
        $this->autoRender = false;
        $this->layout = false;

        $response = 'Sorry !!! Something went wrong.';
        if (!$this->request->isPost()) {
            return $response;
        }

        /** @var SlackAdapter $SlackAdapter */
        $SlackAdapter = new SlackAdapter($this->request);
        $requestParams = $SlackAdapter->parseRequest();

        /** @var SlackRepository $SlackRepository */
        $SlackRepository = new SlackRepository();
        $isDeleted = $SlackRepository->marktodo($requestParams);

        if ($isDeleted) {
            $response = "Removed TODO for _*" . $requestParams[SlackAdapter::TEXT] . '*_';
        } else {
            $response = "TODO is not found for _*" . $requestParams[SlackAdapter::TEXT] . '*_';
        }

        return $SlackAdapter->parseResponse($response);

    }

}