<?php
/**
 * @abstract http请求类
 * @author yanyugang<yanyugang@baidu.com>
 * @since 2010-12-15
 */
class HttpProxy
{
	const CONNECT_TIMEOUT = 5000000;//连接超时时间，单位ms，利用curl时不能小于1000
	const TIMEOUT = 5000000;//交互超时时间，单位ms
	/**
	 * GET请求
	 *
	 * @param string $url 请求的url
	 * @param bool $isHttps 是否是https
	 * @return string 返回运行结果
	 */
	public static function get($url, $isHttps = false, $action=NULL)
	{
		return self::request($url, array(), 'GET', $isHttps, $action);
	}
	/**
	 * POST请求
	 *
	 * @param string $url 请求的url
	 * @param array $data 请求传输的数据
	 * @param bool $isHttps 是否是https
	 * @return string 返回运行结果
	 */
	public static function post($url, $data, $isHttps = false, $action=NULL)
	{
		return self::request($url, $data, 'POST', $isHttps, $action);
	}
	/**
	 * request请求（GET || POST）
	 *
	 * @param string $url 请求的url
	 * @param array $data 请求传输的数据
	 * @param string $method 请求的方法：GET || POST
	 * @param bool $isHttps 是否是https
	 * @return string 返回运行结果
	 */
	public static function request($url, $data = array(), $method  = 'GET', $isHttps = false, $action=NULL)
	{
		$ch = curl_init();
        $curlOptions = array(
            CURLOPT_URL				=>	$url,
            CURLOPT_CONNECTTIMEOUT	=>	intval(self::CONNECT_TIMEOUT/1000),
            CURLOPT_TIMEOUT			=>	intval(self::TIMEOUT/1000),
            CURLOPT_RETURNTRANSFER	=>	true,
            CURLOPT_HEADER			=>	false,
            CURLOPT_FOLLOWLOCATION	=>	true,
        );
        if('POST' === $method)
        {
        	$curlOptions[CURLOPT_POST] = true;
        	if('onlineOrder' === $action)
	        {
	        	$curlOptions[CURLOPT_HTTPHEADER] = array("Action:$action");
	        	$curlOptions[CURLOPT_HEADER] = true;
	        	$curlOptions[CURLOPT_POSTFIELDS] = $data;
	        }
	        else
        		$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($data);
        }
        if(true === $isHttps)
        {
        	$curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        }
	//添加外网访问代理
	$curlOptions[CURLOPT_PROXY] = '10.16.87.254:8080';	
        curl_setopt_array($ch, $curlOptions);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        if(0 != $errno)
        {
        	return false;
        }
        curl_close($ch);
        return $response;
	}
}
?>
