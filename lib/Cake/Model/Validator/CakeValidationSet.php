<?php
/**
 * CakeValidationSet.
 *
 * Provides the Model validation logic.
 *
 * PHP versions 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Model.Validator
 * @since         CakePHP(tm) v 2.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('ModelValidator', 'Model');
App::uses('CakeRule', 'Model/Validator');

/**
 * CakeValidationSet object.
 *
 * @package       Cake.Model.Validator
 * @link          http://book.cakephp.org/2.0/en/data-validation.html
 */
class CakeValidationSet implements ArrayAccess, IteratorAggregate {

/**
 * Holds the ValidationRule objects
 *
 * @var array
 */
	protected $_rules = array();

/**
 * Set of methods available for validation
 *
 * @var array
 **/
	protected $_methods = array();

/**
 * I18n domain for validation messages.
 *
 * @var string
 **/
	protected $_validationDomain = null;

/**
 * If the validation is stopped
 *
 * @var boolean
 */
	public $isStopped = false;

/**
 * Holds the fieldname
 *
 * @var string
 */
	public $field = null;

/**
 * Holds the original ruleSet
 *
 * @var array
 */
	public $ruleSet = array();

/**
 * Constructor
 *
 * @param string $fieldName The fieldname
 * @param array $ruleset
 */
	public function __construct($fieldName, $ruleSet) {
		$this->field = $fieldName;

		if (!is_array($ruleSet) || (is_array($ruleSet) && isset($ruleSet['rule']))) {
			$ruleSet = array($ruleSet);
		}

		foreach ($ruleSet as $index => $validateProp) {
			$this->_rules[$index] = new CakeRule($validateProp);
		}
		$this->ruleSet = $ruleSet;
	}

/**
 * Sets the list of methods to use for validation
 *
 * @return void
 **/
	public function setMethods(&$methods) {
		$this->_methods =& $methods;
	}

/**
 * Sets the I18n domain for validation messages.
 *
 * @param string $validationDomain The validation domain to be used.
 * @return void
 */
	public function setValidationDomain($validationDomain) {
		$this->_validationDomain = $validationDomain;
	}

/**
 * Validates a ModelField
 *
 * @return array list of validation errors for this field
 */
	public function validate($data, $isUpdate = false) {
		$errors = array();
		foreach ($this->getRules() as $name => $rule) {
			$rule->isUpdate($isUpdate);
			if ($rule->skip()) {
				continue;
			}

			$checkRequired = $rule->checkRequired($this->field, $data);
			if (!$checkRequired && array_key_exists($this->field, $data)) {
				if ($rule->checkEmpty($this->field, $data)) {
					break;
				}
				$rule->process($this->field, $data, $this->_methods);
			}

			if ($checkRequired || !$rule->isValid()) {
				$errors[] = $this->_processValidationResponse($name, $rule);
				if ($rule->isLast()) {
					break;
				}
			}
		}

		return $errors;
	}

/**
 * Gets a rule for a certain index
 *
 * @param mixed index
 * @return ValidationRule
 */
	public function getRule($index) {
		if (!empty($this->_rules[$index])) {
			return $this->_rules[$index];
		}
	}

/**
 * Gets all rules for this ModelField
 *
 * @return array
 */
	public function getRules() {
		return $this->_rules;
	}

/**
 * Sets a ValidationRule $rule for key $key
 *
 * @param mixed $key The key under which the rule should be set
 * @param CakeRule|array $rule The validation rule to be set
 * @return ModelField
 */
	public function setRule($key, $rule) {
		if (!$rule instanceof CakeRule) {
			$rule = new CakeRule($rule);
		}
		$this->_rules[$key] = $rule;
		return $this;
	}

/**
 * Sets the rules for a given field
 *
 * @param array $rules The rules to be set
 * @param bolean $mergeVars [optional] If true, merges vars instead of replace. Defaults to true.
 * @return ModelField
 */
	public function setRules($rules = array(), $mergeVars = true) {
		if ($mergeVars === false) {
			$this->_rules = $rules;
		} else {
			$this->_rules = array_merge($this->_rules, $rules);
		}
		return $this;
	}

/**
 * Fetches the correct error message for a failed validation
 *
 * @param string $name the name of the rule as it was configured
 * @param CakeRule $rule the object containing validation information
 * @return string
 */
	protected function _processValidationResponse($name, $rule) {
		$message = $rule->getValidationResult();
		if (is_string($message)) {
			return $message;
		}
		$message = $rule->message;

		if ($message !== null) {
			$args = null;
			if (is_array($message)) {
				$result = $message[0];
				$args = array_slice($message, 1);
			} else {
				$result = $message;
			}
			if (is_array($rule->rule) && $args === null) {
				$args = array_slice($rule->rule, 1);
			}
			if (!empty($args)) {
				foreach ($args as $k => $arg) {
					$args[$k] = __d($this->_validationDomain, $arg);
				}
			}

			$message = __d($this->_validationDomain, $result, $args);
		} elseif (is_string($name)) {
			if (is_array($rule->rule)) {
				$args = array_slice($rule->rule, 1);
				if (!empty($args)) {
					foreach ($args as $k => $arg) {
						$args[$k] = __d($this->_validationDomain, $arg);
					}
				}
				$message = __d($this->_validationDomain, $name, $args);
			} else {
				$message = __d($this->_validationDomain, $name);
			}
		} else {
			$message = __d('cake_dev', 'This field cannot be left blank');
		}

		return $message;
	}

/**
 * Returns wheter an index exists in the rule set
 *
 * @param string $index name of the rule
 * @return boolean
 **/
	public function offsetExists($index) {
		return isset($this->_rules[$index]);
	}

/**
 * Returns a rule object by its index
 *
 * @param string $index name of the rule
 * @return CakeRule
 **/
	public function offsetGet($index) {
		return $this->_rules[$index];
	}

/**
 * Sets or replace a validation rule
 *
 * @param string $index name of the rule
 * @param CakeRule|array rule to add to $index
 **/
	public function offsetSet($index, $rule) {
		$this->setRule($index, $rule);
	}

/**
 * Unsets a validation rule
 *
 * @param string $index name of the rule
 * @return void
 **/
	public function offsetUnset($index) {
		unset($this->_rules[$index]);
	}

/**
 * Returns an iterator for each of the rules to be applied
 *
 * @return ArrayIterator
 **/
	public function getIterator() {
		return new ArrayIterator($this->_rules);
	}

}