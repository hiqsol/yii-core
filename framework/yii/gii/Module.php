<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii;

use Yii;
use yii\web\HttpException;

/**
 * This is the main module class for the Gii module.
 *
 * To use Gii, include it as a module in the application configuration like the following:
 *
 * ~~~
 * return array(
 *     ......
 *     'modules' => array(
 *         'gii' => array(
 *             'class' => 'yii\gii\Module',
 *         ),
 *     ),
 * )
 * ~~~
 *
 * Because Gii generates new code files on the server, you should only use it on your own
 * development machine. To prevent other people from using this module, by default, Gii
 * can only be accessed by localhost. You may configure its [[allowedIPs]] property if
 * you want to make it accessible on other machines.
 *
 * With the above configuration, you will be able to access GiiModule in your browser using
 * the URL `http://localhost/path/to/index.php?r=gii`
 *
 * If your application enables [[UrlManager::enablePrettyUrl|pretty URLs]] and you have defined
 * custom URL rules or enabled [[UrlManager::enableStrictParsing], you may need to add
 * the following URL rules at the beginning of your URL rule set in your application configuration
 * in order to access Gii:
 *
 * ~~~
 * 'rules'=>array(
 *     'gii' => 'gii',
 *     'gii/<controller>' => 'gii/<controller>',
 *     'gii/<controller>/<action>' => 'gii/<controller>/<action>',
 *     ...
 * ),
 * ~~~
 *
 * You can then access Gii via URL: `http://localhost/path/to/index.php/gii`
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Module extends \yii\base\Module
{
	/**
	 * @inheritdoc
	 */
	public $controllerNamespace = 'yii\gii\controllers';
	/**
	 * @var array the list of IPs that are allowed to access this module.
	 * Each array element represents a single IP filter which can be either an IP address
	 * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
	 * The default value is `array('127.0.0.1', '::1')`, which means the module can only be accessed
	 * by localhost.
	 */
	public $allowedIPs = array('127.0.0.1', '::1');
	/**
	 * @var array|Generator[] a list of generator configurations or instances. The array keys
	 * are the generator IDs (e.g. "crud"), and the array elements are the corresponding generator
	 * configurations or the instances.
	 *
	 * After the module is initialized, this property will become an array of generator instances
	 * which are created based on the configurations previously taken by this property.
	 *
	 * Newly assigned generators will be merged with the [[coreGenerators()|core ones]], and the former
	 * takes precedence in case when they have the same generator ID.
	 */
	public $generators = array();
	/**
	 * @var integer the permission to be set for newly generated code files.
	 * This value will be used by PHP chmod function.
	 * Defaults to 0666, meaning the file is read-writable by all users.
	 */
	public $newFileMode = 0666;
	/**
	 * @var integer the permission to be set for newly generated directories.
	 * This value will be used by PHP chmod function.
	 * Defaults to 0777, meaning the directory can be read, written and executed by all users.
	 */
	public $newDirMode = 0777;


	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		foreach (array_merge($this->coreGenerators(), $this->generators) as $id => $config) {
			$this->generators[$id] = Yii::createObject($config);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		if ($this->checkAccess()) {
			return parent::beforeAction($action);
		} else {
			throw new HttpException(403, 'You are not allowed to access this page.');
		}
	}

	/**
	 * @return boolean whether the module can be accessed by the current user
	 */
	protected function checkAccess()
	{
		$ip = Yii::$app->getRequest()->getUserIP();
		foreach ($this->allowedIPs as $filter) {
			if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns the list of the core code generator configurations.
	 * @return array the list of the core code generator configurations.
	 */
	protected function coreGenerators()
	{
		return array(
			'model' => array(
				'class' => 'yii\gii\generators\model\Generator',
			),
			'crud' => array(
				'class' => 'yii\gii\generators\crud\Generator',
			),
			'controller' => array(
				'class' => 'yii\gii\generators\controller\Generator',
			),
			'form' => array(
				'class' => 'yii\gii\generators\form\Generator',
			),
			'module' => array(
				'class' => 'yii\gii\generators\module\Generator',
			),
		);
	}
}
