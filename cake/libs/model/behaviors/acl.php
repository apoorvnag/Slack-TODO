<?php

class AclBehavior extends ModelBehavior {

	var $__typeMaps = array('requester' => 'Aro', 'controlled' => 'Aco');

	function setup(&$model, $config = array()) {
		if (is_string($config)) {
			$config = array('type' => $config);
		}
		$this->settings[$model->name] = am(array('type' => 'Requester'), $config);
		$type = $this->__typeMaps[$this->settings[$model->name]['type']];

		if (!ClassRegistry::isKeySet($type)) {
			uses('controller' . DS . 'components' . DS . 'dbacl' . DS . 'models' . DS . 'aclnode');
			uses('controller' . DS . 'components' . DS . 'dbacl' . DS . 'models' . DS . low($type));
			$object =& new $type();
		} else {
			$object =& ClassRegistry::getObject($type);
		}
		$model->{$type} =& $object;
		if (!method_exists($model, 'parentNode')) {
			trigger_error("Callback parentNode() not defined in {$model->name}", E_USER_WARNING);
		}
	}

/**
 * Retrieves the Aro/Aco node for this model
 *
 * @param mixed $ref
 * @return array
 */
	function node(&$model, $ref = null) {
		$db =& ConnectionManager::getDataSource($model->useDbConfig);
		$type = $this->__typeMaps[low($this->settings[$model->name]['type'])];
		$table = low($type) . 's';
		$prefix = $model->tablePrefix;
		$axo =& $model->{$type};

		if (empty($ref)) {
			$ref = array('model' => $model->name, 'foreign_key' => $model->id);
		} elseif (is_string($ref)) {
			$path = explode('/', $ref);
			$start = $path[count($path) - 1];
			unset($path[count($path) - 1]);

			$query = "SELECT {$type}0.* From {$prefix}{$table} AS {$type}0 ";
			foreach ($path as $i => $alias) {
				$j = $i - 1;
				$k = $i + 1;
				$query .= "LEFT JOIN {$prefix}{$table} AS {$type}{$k} ";
				$query .= "ON {$type}{$k}.lft > {$type}{$i}.lft && {$type}{$k}.rght < {$type}{$i}.rght ";
				$query .= "AND {$type}{$k}.alias = " . $db->value($alias) . " ";
			}
			$result = $axo->query("{$query} WHERE {$type}0.alias = " . $db->value($start));

			if (!empty($result)) {
				$result = $result[0]["{$type}0"];
			}
		} elseif (is_object($ref) && is_a($ref, 'Model')) {
			$ref = array('model' => $ref->name, 'foreign_key' => $ref->id);
		}

		if (is_array($ref)) {
			list($result) = array_values($axo->find($ref, null, null, -1));
		}
		return $result;
	}

	function afterSave(&$model, $created) {
		if ($created) {
			$type = $this->__typeMaps[low($this->settings[$model->name]['type'])];
			$model->{$type}->save(array(
				'parent_id'		=> $model->parentNode(),
				'model'			=> $model->name,
				'foreign_key'	=> $model->id
			));
		}
	}

	function afterDelete(&$model) {
		$node = $this->node($model);
		$type = $this->__typeMaps[low($this->settings[$model->name]['type'])];
		$model->{$type}->delete($node['id']);
	}
}

?>