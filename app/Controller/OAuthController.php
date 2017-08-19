<?php
/**
 * Created by Sublime.
 * User: Sahil Chaddha
 * Date: 19/08/17
 * Time: 3:07 AM
 */

App::import('Adapter', OAuthAdapter::class);
App::import('Repository', OAuthRepository::class);

/**
 * Class OAuthController
 *
 * API Controller for Slack APP - Issuing Tokens & Authentication
 */
class OAuthController extends AppController
{
    /**
     * Adds a Reminder
     *
     * @return string
     */
    public function redirectOAuth()
    {
        $this->autoRender = false;
        $this->layout = false;

        //Loading Config
        Configure::load('oauth');
        $config     = Configure::read('prod', 'default', false);
        $redirectUrl = $config['slack_url'] . '?client_id=' . $config['client_id'] . '&scope=' . $config['scope'] . '&redirect_uri=' . $config['redirect_uri'];
        
        $this->redirect($redirectUrl);
    }

    public function verifyOAuth() {
        $this->autoRender = false;
        $this->layout = false;

        //Loading Config
        Configure::load('oauth');
        $config     = Configure::read('prod', 'default', false);

        if(isset($this->request->query['code'])) {
            $verification_code = $this->request->query['code'];
            $url = $config['slack_oauth_url'];

            $data = array('client_id' => $config['client_id'], 'client_secret' => $config['client_secret'], 'code' => $verification_code);

            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                )
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            //API Failed
            if ($result === FALSE) {
                return 'OAuth Verification Failed';
            }

            $OAuthAdapter = new OAuthAdapter($result);
            $response = $OAuthAdapter->parseResponse();
            if($response[OAuthAdapter::IS_VERIFIED]) {

                $OAuthRepository = new OAuthRepository();
                $access_id = $OAuthRepository->saveToDB($response);
                if(isset($access_id)) {
                    return $this->redirect(
                         array('controller' => 'Install', 'action' => 'installed')
                    );
                }
                else {
                    //DB Failure
                    return 'Technical Error !';
                }
            }
            else {
                //Authentication Failed
                return 'OAuth Verification Failed';
            }

        }
        else {
            return 'Code Parameter Not Found !';
        }
    }
}