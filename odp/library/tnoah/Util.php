<?php
/*
 *  工具类
 */
class Tnoah_Util{
    
    /**
     * 获取一个二维数组的一列数据
     * 
     * @param array $array 输入数组
     * @param string $colKey 指定列
     * @return array 
     */
    public static function getColumn($array,$colKey){
        if(!$array || !is_array($array) || !$colKey){
            return null;
        }
        if(is_object($array)){
            $array = self::objectToArray($array);
        }
        
        $colArray = array();
        foreach ($array as $index => $value){
            if(is_object($value)){
                $curValue = $value->$colKey;
            } else if(is_array($value)){
                $curValue = $value[$colKey];
            } else {
                continue;
            }
            
            if(isset($curValue) && $curValue !== ''){
                $colArray[] = $curValue;
            }
        }
        return $colArray;
    }
    
    /**
     * 常用返回数组数据判断
     * 判断返回的数组数据是否正确
     * 
     */
    public static function isComResult($result){
        if(!$result || !is_array($result) || empty($result)){
            return false;
        }
        return true;
    }
    
    /**
     * 将数组按指定列的值 为关键字 构造新数组返回
     * 注意 如果有多个数据   关键字相同将会覆盖
     * 
     * @param array $array 输入数组
     * @param string $colKey 指定列名
     * @return array 构造好的数组
     */
    public static function assColumn($array,$colKey){
        if(!$array || !is_array($array) || !$colKey){
            return null;
        }
        
        $newArray = array();
        foreach ($array as $index => $one){
            $key = $one[$colKey];
            if(isset($key) && !isset($newArray[$key])){
                $newArray[$key] = $one;
            }
        }
        return $newArray;
    }
    
    public static function objectToArray($obj){
        $_arr = is_object($obj) ? get_object_vars($obj) :$obj;
        foreach ($_arr as $key=>$val){
            $val = (is_array($val) || is_object($val)) ? self::objectToArray($val):$val;
            $arr[$key] = $val;
        }
        return $arr;
    }
    
    
    /**
     * 对数组排序
     * @param array $array 待排序的数组
     * @param string $key 二维数组的话    标示排序的列名
     * @param string $order SORT_ASC or SORT_DESC
     * @return array: 排好序的数组
     */
    public static function Sort($array,$key = null,$order = SORT_ASC){
        if(!self::isComResult($array)){
            return array();
        }
        $keyArray = array();
        $sortedArray = array();
    
        //分配
        foreach ($array as $index =>$value){
            $currentKey = '';
            if(is_array($value)){
                if(!$key){
                    $currentKey = $index;
                } else if(isset($value[$key])){
                    $currentKey = $value[$key];
                }
            } else {
                $currentKey = $value;
            }
                
            $keyArray[$currentKey][] = $index;
        }
    
    
        ///排序
        switch ($order){
            case SORT_DESC:
                krsort($keyArray);
                break;
            case SORT_ASC:
            default:
                ksort($keyArray);
                break;
        }
    
        //组装
        foreach ($keyArray as $indexArray){
            foreach ($indexArray as $index){
                $sortedArray[$index] = $array[$index];
            }
        }
    
        return $sortedArray;
    }
    
    public static function Unique($array,$key){
        if(!self::isComResult($array)){
            return array();
        }
        
        $tmp = array();
        foreach ($array as $index => $one){
            $value = $one[$key];
            if($tmp[$value]){
                unset($array[$index]);
            } else {
                $tmp[$value] = true;
            }
        }
        
        return $array;
    }
    
    public static function GetRandomArray($array,$start=0,$size=0){
        $size = $size ? $size : count($array);
        if(!$size || $start >= count($array)){
            return $array;
        }
    
        $arrayStart = array_slice($array, 0,$start);
        $arrayEnd = array_slice($array, $start+$size);
        $tempArray = array_slice($array, $start,$size);
        $count = count($tempArray);
        $arrayCenter = array();
        
        for($i=0;$i<$count;$i++){
            $key = array_rand($tempArray);
            $arrayCenter[$key] = $tempArray[$key];
            unset($tempArray[$key]);
        }
    
        $result = array_merge($arrayStart,$arrayCenter,$arrayEnd);
        return $result;
    }
    
}