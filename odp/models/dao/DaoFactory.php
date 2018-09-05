<?php
/**
 * @name Dao_DaoFactory
 * @desc dao工厂类
 * @author ydp
 */

class Dao_DaoFactory
{
	/**
	 * Array of dal instances
	 * @var array
	 */
	public static $_instances = array();

	/**
	 * Get a dal instance
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
			$DBObj = empty($param) ? new $_className() : new $_className($param);
			self::$_instances[$_className] = $DBObj;
			return self::$_instances[$_className];
		}
		else
		{
			return self::$_instances[$_className];
		}
	}

}
?>