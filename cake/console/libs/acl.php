<?php
/**
 * Acl Shell provides Acl access in the CLI environment
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.console.libs
 * @since         CakePHP(tm) v 1.2.0.5012
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::import('Component', 'Acl');
App::import('Model', 'DbAcl');

/**
 * Shell for ACL management.
 *
 * @package       cake
 * @subpackage    cake.cake.console.libs
 */
class AclShell extends Shell {

/**
 * Contains instance of AclComponent
 *
 * @var AclComponent
 * @access public
 */
	public $Acl;

/**
 * Contains arguments parsed from the command line.
 *
 * @var array
 * @access public
 */
	public $args;

/**
 * Contains database source to use
 *
 * @var string
 * @access public
 */
	public $connection = 'default';

/**
 * Contains tasks to load and instantiate
 *
 * @var array
 * @access public
 */
	public $tasks = array('DbConfig');

/**
 * Override startup of the Shell
 *
 */
	public function startup() {
		if (isset($this->params['connection'])) {
			$this->connection = $this->params['connection'];
		}

		if (!in_array(Configure::read('Acl.classname'), array('DbAcl', 'DB_ACL'))) {
			$out = "--------------------------------------------------\n";
			$out .= __('Error: Your current Cake configuration is set to') . "\n";
			$out .= __('an ACL implementation other than DB. Please change') . "\n";
			$out .= __('your core config to reflect your decision to use') . "\n";
			$out .= __('DbAcl before attempting to use this script') . ".\n";
			$out .= "--------------------------------------------------\n";
			$out .= sprintf(__('Current ACL Classname: %s'), Configure::read('Acl.classname')) . "\n";
			$out .= "--------------------------------------------------\n";
			$this->err($out);
			$this->_stop();
		}

		if ($this->command && !in_array($this->command, array('help'))) {
			if (!config('database')) {
				$this->out(__('Your database configuration was not found. Take a moment to create one.'), true);
				$this->args = null;
				return $this->DbConfig->execute();
			}
			require_once (CONFIGS.'database.php');

			if (!in_array($this->command, array('initdb'))) {
				$this->Acl =& new AclComponent();
				$controller = null;
				$this->Acl->startup($controller);
			}
		}
	}

/**
 * Override main() for help message hook
 *
 */
	public function main() {
		$out  = __('Available ACL commands:') . "\n";
		$out .= "\t - create\n";
		$out .= "\t - delete\n";
		$out .= "\t - setParent\n";
		$out .= "\t - getPath\n";
		$out .= "\t - check\n";
		$out .= "\t - grant\n";
		$out .= "\t - deny\n";
		$out .= "\t - inherit\n";
		$out .= "\t - view\n";
		$out .= "\t - initdb\n";
		$out .= "\t - help\n\n";
		$out .= __("For help, run the 'help' command.  For help on a specific command, run 'help <command>'");
		$this->out($out);
	}

/**
 * Creates an ARO/ACO node
 *
 */
	public function create() {
		$this->_checkArgs(3, 'create');
		$this->checkNodeType();
		extract($this->__dataVars());

		$class = ucfirst($this->args[0]);
		$parent = $this->parseIdentifier($this->args[1]);

		if (!empty($parent) && $parent != '/' && $parent != 'root') {
			$parent = $this->_getNodeId($class, $parent);
		} else {
			$parent = null;
		}

		$data = $this->parseIdentifier($this->args[2]);
		if (is_string($data) && $data != '/') {
			$data = array('alias' => $data);
		} elseif (is_string($data)) {
			$this->error(__('/ can not be used as an alias!'), __("\t/ is the root, please supply a sub alias"));
		}

		$data['parent_id'] = $parent;
		$this->Acl->{$class}->create();
		if ($this->Acl->{$class}->save($data)) {
			$this->out(sprintf(__("New %s '%s' created.\n"), $class, $this->args[2]), true);
		} else {
			$this->err(sprintf(__("There was a problem creating a new %s '%s'."), $class, $this->args[2]));
		}
	}

/**
 * Delete an ARO/ACO node.
 *
 */
	public function delete() {
		$this->_checkArgs(2, 'delete');
		$this->checkNodeType();
		extract($this->__dataVars());

		$identifier = $this->parseIdentifier($this->args[1]);
		$nodeId = $this->_getNodeId($class, $identifier);

		if (!$this->Acl->{$class}->delete($nodeId)) {
			$this->error(__('Node Not Deleted'), sprintf(__('There was an error deleting the %s. Check that the node exists'), $class) . ".\n");
		}
		$this->out(sprintf(__('%s deleted'), $class) . ".\n", true);
	}

/**
 * Set parent for an ARO/ACO node.
 *
 */
	public function setParent() {
		$this->_checkArgs(3, 'setParent');
		$this->checkNodeType();
		extract($this->__dataVars());
		$target = $this->parseIdentifier($this->args[1]);
		$parent = $this->parseIdentifier($this->args[2]);

		$data = array(
			$class => array(
				'id' => $this->_getNodeId($class, $target),
				'parent_id' => $this->_getNodeId($class, $parent)
			)
		);
		$this->Acl->{$class}->create();
		if (!$this->Acl->{$class}->save($data)) {
			$this->out(__('Error in setting new parent. Please make sure the parent node exists, and is not a descendant of the node specified.'), true);
		} else {
			$this->out(sprintf(__('Node parent set to %s'), $this->args[2]) . "\n", true);
		}
	}

/**
 * Get path to specified ARO/ACO node.
 *
 */
	public function getPath() {
		$this->_checkArgs(2, 'getPath');
		$this->checkNodeType();
		extract($this->__dataVars());
		$identifier = $this->parseIdentifier($this->args[1]);

		$id = $this->_getNodeId($class, $identifier);
		$nodes = $this->Acl->{$class}->getPath($id);

		if (empty($nodes)) {
			$this->error(
				sprintf(__("Supplied Node '%s' not found"), $this->args[1]),
				__('No tree returned.')
			);
		}
		$this->out(__('Path:'));
		$this->hr();
		for ($i = 0; $i < count($nodes); $i++) {
			$this->_outputNode($class, $nodes[$i], $i);
		}
	}

/**
 * Outputs a single node, Either using the alias or Model.key
 *
 * @param string $class Class name that is being used.
 * @param array $node Array of node information.
 * @param integer $indent indent level.
 * @return void
 */
	protected function _outputNode($class, $node, $indent) {
		$indent = str_repeat('  ', $indent);
		$data = $node[$class];
		if ($data['alias']) {
			$this->out($indent . "[" . $data['id'] . "] " . $data['alias']);
		 } else {
			$this->out($indent . "[" . $data['id'] . "] " . $data['model'] . '.' . $data['foreign_key']);
		}
	}

/**
 * Check permission for a given ARO to a given ACO.
 *
 */
	public function check() {
		$this->_checkArgs(3, 'check');
		extract($this->__getParams());

		if ($this->Acl->check($aro, $aco, $action)) {
			$this->out(sprintf(__('%s is allowed.'), $aroName), true);
		} else {
			$this->out(sprintf(__('%s is not allowed.'), $aroName), true);
		}
	}

/**
 * Grant permission for a given ARO to a given ACO.
 *
 */
	public function grant() {
		$this->_checkArgs(3, 'grant');
		extract($this->__getParams());

		if ($this->Acl->allow($aro, $aco, $action)) {
			$this->out(__('Permission granted.'), true);
		} else {
			$this->out(__('Permission was not granted.'), true);
		}
	}

/**
 * Deny access for an ARO to an ACO.
 *
 */
	public function deny() {
		$this->_checkArgs(3, 'deny');
		extract($this->__getParams());

		if ($this->Acl->deny($aro, $aco, $action)) {
			$this->out(__('Permission denied.'), true);
		} else {
			$this->out(__('Permission was not denied.'), true);
		}
	}

/**
 * Set an ARO to inhermit permission to an ACO.
 *
 */
	public function inherit() {
		$this->_checkArgs(3, 'inherit');
		extract($this->__getParams());

		if ($this->Acl->inherit($aro, $aco, $action)) {
			$this->out(__('Permission inherited.'), true);
		} else {
			$this->out(__('Permission was not inherited.'), true);
		}
	}

/**
 * Show a specific ARO/ACO node.
 *
 */
	public function view() {
		$this->_checkArgs(1, 'view');
		$this->checkNodeType();
		extract($this->__dataVars());

		if (isset($this->args[1])) {
			$identity = $this->parseIdentifier($this->args[1]);

			$topNode = $this->Acl->{$class}->find('first', array(
				'conditions' => array($class . '.id' => $this->_getNodeId($class, $identity))
			));

			$nodes = $this->Acl->{$class}->find('all', array(
				'conditions' => array(
					$class . '.lft >=' => $topNode[$class]['lft'],
					$class . '.lft <=' => $topNode[$class]['rght']
				),
				'order' => $class . '.lft ASC'
			));
		} else {
			$nodes = $this->Acl->{$class}->find('all', array('order' => $class . '.lft ASC'));
		}

		if (empty($nodes)) {
			if (isset($this->args[1])) {
				$this->error(sprintf(__('%s not found'), $this->args[1]), __('No tree returned.'));
			} elseif (isset($this->args[0])) {
				$this->error(sprintf(__('%s not found'), $this->args[0]), __('No tree returned.'));
			}
		}
		$this->out($class . " tree:");
		$this->hr();

		$stack = array();
		$last  = null;

		foreach ($nodes as $n) {
			$stack[] = $n;
			if (!empty($last)) {
				$end = end($stack);
				if ($end[$class]['rght'] > $last) {
					foreach ($stack as $k => $v) {
						$end = end($stack);
						if ($v[$class]['rght'] < $end[$class]['rght']) {
							unset($stack[$k]);
						}
					}
				}
			}
			$last = $n[$class]['rght'];
			$count = count($stack);

			$this->_outputNode($class, $n, $count);
		}
		$this->hr();
	}

/**
 * Initialize ACL database.
 *
 */
	public function initdb() {
		$this->Dispatch->args = array('schema', 'run', 'create', 'DbAcl');
		$this->Dispatch->dispatch();
	}

/**
 * Show help screen.
 *
 */
	public function help() {
		$head = "-----------------------------------------------\n";
		$head .= __('Usage: cake acl <command> <arg1> <arg2>...') . "\n";
		$head .= "-----------------------------------------------\n";
		$head .= __('Commands:') . "\n";

		$commands = array(
			'create' => "create aro|aco <parent> <node>\n" .
				"\t" . __("Creates a new ACL object <node> under the parent") . "\n" .
				"\t" . __("specified by <parent>, an id/alias.") . "\n" .
				"\t" . __("The <parent> and <node> references can be") . "\n" .
				"\t" . __("in one of the following formats:") . "\n\n" .
				"\t\t- " . __("<model>.<id> - The node will be bound to a") . "\n" .
				"\t\t" . __("specific record of the given model.") . "\n\n" .
				"\t\t- " . __("<alias> - The node will be given a string alias,") . "\n" .
				"\t\t" . __(" (or path, in the case of <parent>)") . "\n" .
				"\t\t  " . __("i.e. 'John'.  When used with <parent>,") . "\n" .
				"\t\t" . __("this takes the form of an alias path,") . "\n" .
				"\t\t  " . __("i.e. <group>/<subgroup>/<parent>.") . "\n\n" .
				"\t" . __("To add a node at the root level,") . "\n" .
				"\t" . __("enter 'root' or '/' as the <parent> parameter.") . "\n",

			'delete' => "delete aro|aco <node>\n" .
				"\t" . __("Deletes the ACL object with the given <node> reference") . "\n" .
				"\t" . __("For more detailed parameter usage info,") . "\n" .
				"\t" . __("see help for the 'create' command."),

			'setparent' => "setParent aro|aco <node> <parent node>\n" .
				"\t" . __("Moves the ACL object specified by <node> beneath") . "\n" .
				"\t" . __("the parent ACL object specified by <parent>.") . "\n" .
				"\t" . __("For more detailed parameter usage info,") . "\n" .
				"\t" . __("see help for the 'create' command."),

			'getpath' => "getPath aro|aco <node>\n" .
				"\t" . __("Returns the path to the ACL object specified by <node>. This command") . "\n" .
				"\t" . __("is useful in determining the inhertiance of permissions for a certain") . "\n" .
				"\t" . __("object in the tree.") . "\n" .
				"\t" . __("For more detailed parameter usage info,") . "\n" .
				"\t" . __("see help for the 'create' command."),

			'check' => "check <node> <node> [<aco_action>] " . __("or") . " all\n" .
				"\t" . __("Use this command to check ACL permissions.") . "\n" .
				"\t" . __("For more detailed parameter usage info,") . "\n" .
				"\t" . __("see help for the 'create' command."),

			'grant' => "grant <node> <node> [<aco_action>] " . __("or") . " all\n" .
				"\t" . __("Use this command to grant ACL permissions. Once executed, the ARO") . "\n" .
				"\t" . __("specified (and its children, if any) will have ALLOW access to the") . "\n" .
				"\t" . __("specified ACO action (and the ACO's children, if any).") . "\n" .
				"\t" . __("For more detailed parameter usage info,") . "\n" .
				"\t" . __("see help for the 'create' command."),

			'deny' => "deny <node> <node> [<aco_action>]" . __("or") . " all\n" .
				"\t" . __("Use this command to deny ACL permissions. Once executed, the ARO") . "\n" .
				"\t" . __("specified (and its children, if any) will have DENY access to the") . "\n" .
				"\t" . __("specified ACO action (and the ACO's children, if any).") . "\n" .
				"\t" . __("For more detailed parameter usage info,") . "\n" .
				"\t" . __("see help for the 'create' command."),

			'inherit' => "inherit <node> <node> [<aco_action>]" . __("or") . " all\n" .
				"\t" . __("Use this command to force a child ARO object to inherit its") . "\n" .
				"\t" . __("permissions settings from its parent.") . "\n" .
				"\t" . __("For more detailed parameter usage info,") . "\n" .
				"\t" . __("see help for the 'create' command."),

			'view' => "view aro|aco [<node>]\n" .
				"\t" . __("The view command will return the ARO or ACO tree.") . "\n" .
				"\t" . __("The optional node parameter allows you to return") . "\n" .
				"\t" . __("only a portion of the requested tree.") . "\n" .
				"\t" . __("For more detailed parameter usage info,") . "\n" .
				"\t" . __("see help for the 'create' command."),

			'initdb' => "initdb\n".
				"\t" . __("Uses this command : cake schema run create DbAcl"),

			'help' => "help [<command>]\n" .
				"\t" . __("Displays this help message, or a message on a specific command.")
		);

		$this->out($head);
		if (!isset($this->args[0])) {
			foreach ($commands as $cmd) {
				$this->out("{$cmd}\n\n");
			}
		} elseif (isset($commands[strtolower($this->args[0])])) {
			$this->out($commands[strtolower($this->args[0])] . "\n\n");
		} else {
			$this->out(sprintf(__("Command '%s' not found"), $this->args[0]));
		}
	}

/**
 * Check that first argument specifies a valid Node type (ARO/ACO)
 *
 */
	public function checkNodeType() {
		if (!isset($this->args[0])) {
			return false;
		}
		if ($this->args[0] != 'aco' && $this->args[0] != 'aro') {
			$this->error(sprintf(__("Missing/Unknown node type: '%s'"), $this->args[0]), __('Please specify which ACL object type you wish to create. Either "aro" or "aco"'));
		}
	}

/**
 * Checks that given node exists
 *
 * @param string $type Node type (ARO/ACO)
 * @param integer $id Node id
 * @return boolean Success
 */
	public function nodeExists() {
		if (!$this->checkNodeType() && !isset($this->args[1])) {
			return false;
		}
		extract($this->__dataVars($this->args[0]));
		$key = is_numeric($this->args[1]) ? $secondary_id : 'alias';
		$conditions = array($class . '.' . $key => $this->args[1]);
		$possibility = $this->Acl->{$class}->find('all', compact('conditions'));
		if (empty($possibility)) {
			$this->error(sprintf(__('%s not found'), $this->args[1]), __('No tree returned.'));
		}
		return $possibility;
	}

/**
 * Parse an identifier into Model.foriegnKey or an alias.
 * Takes an identifier determines its type and returns the result as used by other methods.
 *
 * @param string $identifier Identifier to parse
 * @return mixed a string for aliases, and an array for model.foreignKey
 */
	function parseIdentifier($identifier) {
		if (preg_match('/^([\w]+)\.(.*)$/', $identifier, $matches)) {
			return array(
				'model' => $matches[1],
				'foreign_key' => $matches[2],
			);
		}
		return $identifier;
	}

/**
 * Get the node for a given identifier. $identifier can either be a string alias
 * or an array of properties to use in AcoNode::node()
 *
 * @param string $class Class type you want (Aro/Aco)
 * @param mixed $identifier A mixed identifier for finding the node.
 * @return int Integer of NodeId. Will trigger an error if nothing is found.
 */
	function _getNodeId($class, $identifier) {
		$node = $this->Acl->{$class}->node($identifier);
		if (empty($node)) {
			if (is_array($identifier)) {
				$identifier = var_export($identifier, true);
			}
			$this->error(sprintf(__('Could not find node using reference "%s"'), $identifier));
		}
		return Set::extract($node, "0.{$class}.id");
	}

/**
 * get params for standard Acl methods
 *
 * @return array aro, aco, action
 * @access private
 */
	function __getParams() {
		$aro = is_numeric($this->args[0]) ? intval($this->args[0]) : $this->args[0];
		$aco = is_numeric($this->args[1]) ? intval($this->args[1]) : $this->args[1];
		$aroName = $aro;
		$acoName = $aco;

		if (is_string($aro)) {
			$aro = $this->parseIdentifier($aro);
		}
		if (is_string($aco)) {
			$aco = $this->parseIdentifier($aco);
		}
		$action = null;
		if (isset($this->args[2])) {
			$action = $this->args[2];
			if ($action == '' || $action == 'all') {
				$action = '*';
			}
		}
		return compact('aro', 'aco', 'action', 'aroName', 'acoName');
	}

/**
 * Build data parameters based on node type
 *
 * @param string $type Node type  (ARO/ACO)
 * @return array Variables
 * @access private
 */
	function __dataVars($type = null) {
		if ($type == null) {
			$type = $this->args[0];
		}
		$vars = array();
		$class = ucwords($type);
		$vars['secondary_id'] = (strtolower($class) == 'aro') ? 'foreign_key' : 'object_id';
		$vars['data_name'] = $type;
		$vars['table_name'] = $type . 's';
		$vars['class'] = $class;
		return $vars;
	}
}
?>