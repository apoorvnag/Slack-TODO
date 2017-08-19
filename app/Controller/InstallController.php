<?php
/**
 * Created by Sublime.
 * User: Sahil Chaddha
 * Date: 19/08/17
 * Time: 3:15 AM
 */


/**
 * Class InstallController
 *
 * API Controller for Slack APP - Installation for App
 */
class InstallController extends AppController
{
    /**
     * Adds a Reminder
     *
     * URL : HOST/oauth/redirectOAuth
     * @return string
     */
    public function index()
    {
        $this->autoRender = false;
        $this->layout = false;
        
        return $this->redirect(
            array('controller' => 'OAuth', 'action' => 'redirectOAuth')
        );
    }

    public function installed() {
        $this->autoRender = false;
        $this->layout = false;

        return 'ToDo-App Installed.';
    }


}