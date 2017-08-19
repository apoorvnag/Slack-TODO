<?php
/**
 * Created by Sublime.
 * User: Sahil Chaddha
 * Date: 19/08/17
 * Time: 2:52 AM
 */

// App::import('Adapter', SlackAdapter::class);
// App::import('Repository', SlackRepository::class);

/**
 * Class ReminderController
 *
 * API Controller for Slack APP - Reminders
 */
class ReminderController extends AppController
{

    /**
     * Adds a Reminder
     *
     * @return string
     */
    public function addReminder()
    {
        $this->autoRender = false;
        $this->layout = false;

        $response = 'Only POST Request Supported !';
        if (!$this->request->isPost()) {
            return $response;
        }

        var_dump($this->request);
        die;
    }


}