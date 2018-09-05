<?php
/**
 * @name Util_OutPutUtil
 * @desc 公共输出类
 * @author ydp
 */

class Tnoah_OutPutUtil
{
    /**
     * @comment 输出应答
     *
     * @param array $result 应答的数组
     *
     * @return
     **/
    public static function response($result)
    {
        $req = Saf_SmartMain::getCgi();
        $req = $req['get'];
        $format = $req['format'];
        $req_callback = $req['callback'];

        if($req_callback){
            header('Content-type: text/javascript');
            $result = self::responseJson($result, $req_callback); 
        }
        else{
            header('Content-type: application/json');
            $result = self::responseJson($result, null);
        }

        echo $result;
        $msgArr = array('logType' => 'MAIN');
        exit;
    }

    /**
     * @comment 输出Json格式
     *
     * @param array $result 结果
     * @param string $callback 回调函数
     *
     * @return string
     **/
    private static function responseJson($result, $callback=null)
    {
        $result = json_encode($result);

        if($callback){
            $callback = preg_replace('/[^\w_\.()]/', '', $callback);
            $result = '/**/' . $callback . '(' . $result . ');';
        }

        return $result;
    }

    /**
     * @comment 返回参数错误信息
    */
    public static function responseParamError() {
        $ret = array(
            'error_code' => 22005,
            'error_msg'  => 'param error',
        );
        self::response($ret);
    }

    /**
     * @comment 返回数据库操作错误信息
    */
    public static function responseSqlError() {
        $ret = array(
            'error_code' => 22001,
            'error_msg'  => 'do sql failed',
        );
        self::response($ret);
    }

    /**
     * @comment 返回部分数据库操作错误信息
    */
    public static function responseSqlNotAllError($msg = array()) {
        $ret = array(
            'error_code' => 22002,
            'error_msg'  => 'do sql failed',
            'error_msg'  => $msg,
        );
        self::response($ret);
    }


    /**
     *  @comment 获取项目data路径
    */
    public static function getDataPath() {
        return '/home/work/odp/data/app/tnoah/';
    }

    /**
     *  @comment 获取项目data路径
    */
    public static function getEmptyDataPath() {
        return '/home/work/odp/data/app/tnoah/empty/';
    }

    /**
     * @comment 返回集群内监控项重名错误信息
    */
    public static function responseDupNameItemError($msg = array()) {
        $ret = array(
            'error_code' => 22003,
            'error_msg'  => 'item name dup in service line',
        );
        self::response($ret);
    }

    
}
