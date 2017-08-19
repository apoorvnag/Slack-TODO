<?php
/**
 * Created by Sublime.
 * User: Sahil Chaddha
 * Date: 19/08/17
 * Time: 3:30 AM
 */

class OAuth extends AppModel
{
	public $useTable = 'oauth';

    const TOKEN         = 'access_token';
    const TEAM_ID       = 'team_id';
    const TEAM_NAME     = 'team_name';
    const USER_ID       = 'user_id';
    const BOT_USER_ID   = 'bot_user_id';
    const BOT_TOKEN     = 'bot_access_token';
    const IS_VERIFIED   = 'is_verified';
    const CREATED   = 'created';
}