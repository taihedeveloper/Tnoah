<?php
/**
 * @name Dao_DBFactory.php
 * @desc 数据库工厂类，返回指定的数据库连接类DB
 * @author API Group
 */
class Tnoah_SsdbFactory {
    private static $_DBArray = array();
    
    /*
    *   获取对象
    *   @param   string   $DBName
    *   @return  Dao_DB() / NULL
    */
    public static function getInstance($DBName) {
        if(!array_key_exists($DBName, self::$_DBArray)) {
            $dbObj = new Dao_DB($DBName);
            self::$_DBArray[$DBName] = $dbObj;
        }
        return self::$_DBArray[$DBName];
    }
}
