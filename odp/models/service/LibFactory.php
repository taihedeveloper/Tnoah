<?php
/**
 * @name Service_LibFactory
 * @desc lib工厂类
 * @author ydp
 */

class Service_LibFactory
{
	/**
	 * Array of libs instances
	 * @var array
	 */
	public static $_instances = array();

	/**
	 * Get a libs instance
	 *
	 * @param string $classKey 类名关键字
	 * @param string $param 类构造初始化参数
	 */
	public static function getInstance($classKey, $param = null)
	{
		if(empty($classKey))
		{
			return null;
		}

		$_className = $classKey;

		if(!isset(self::$_instances[$_className]))
		{
			self::$_instances[$_className] = null;
			$libObj = empty($param) ? new $_className() : new $_className($param);
			self::$_instances[$_className] = $libObj;
			return self::$_instances[$_className];
		}
		else
		{
			return self::$_instances[$_className];
		}
	}

}
?>