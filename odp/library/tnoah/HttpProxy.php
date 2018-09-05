<?php
/**
 * @name Util_HttpProxy
 * @desc HttpProxy
 * @author ydp
 */

class Tnoah_HttpProxy
{
	public $proxy = NULL;

	public function __construct()
	{
		#CURLOPT_TIMEOUT_MS should be 155
		#CURLOPT_CONNECTTIMEOUT_MS should be 156
		if (!defined(CURLOPT_CONNECTTIMEOUT_MS)) define('CURLOPT_CONNECTTIMEOUT_MS', 156);
		if (!defined(CURLOPT_TIMEOUT_MS)) define('CURLOPT_TIMEOUT_MS', 155);
	}
	/**
	 * GET请求
	 *
	 * @param string $url 请求的url
	 * @param bool $isHttps 是否是https
	 * @return string 返回运行结果
	 */
	public function get($url, $ms = 1000, $needLog = 1, $isHttps = false)
	{
		return $this->request($url, array(), 'GET', $ms, $needLog, $isHttps);
	}
	/**
	 * POST请求
	 *
	 * @param string $url 请求的url
	 * @param array $data 请求传输的数据
	 * @param bool $isHttps 是否是https
	 * @return string 返回运行结果
	 */
	public function post($url, $data, $ms = 1000, $needLog = 1, $isHttps = false)
	{
		return $this->request($url, $data, 'POST', $ms, $needLog, $isHttps);
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
	public function request($url, $data = array(), $method  = 'GET', $ms = 1000, $needLog = 1, $isHttps = false, $cookie = null, $httpHeader= array())
	{
		if(empty($url)){
			return false;
		}
		$pos = strpos($url, '?');
/*
		if ($pos === false)
		{
			$url = $url . '?log_id=' . Dapper_Log::getLogId();
		}
		else 
		{
			$url = $url . '&log_id=' . Dapper_Log::getLogId();
		}
*/
		$ch = curl_init();
        $curlOptions = array(
            CURLOPT_URL				=>	$url,
            //毫秒级超时设定
            CURLOPT_NOSIGNAL 		=> 1,
            CURLOPT_CONNECTTIMEOUT_MS => $ms,
            CURLOPT_TIMEOUT_MS 		=> $ms,
            //CURLOPT_CONNECTTIMEOUT	=>	1, //curl时不能小于1秒
            //CURLOPT_TIMEOUT			=>	1,
            CURLOPT_RETURNTRANSFER	=>	true,
            CURLOPT_HEADER			=>	false,
            CURLOPT_FOLLOWLOCATION	=>	true,
            CURLOPT_HTTPHEADER		=>  $httpHeader,
        );
        if('POST' === $method)
        {
        	$curlOptions[CURLOPT_POST] = true;
        	if(is_array($data)){
				$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($data);
			}else{
				$curlOptions[CURLOPT_POSTFIELDS] = $data;
			}
        }
        if(true === $isHttps)
        {
        	$curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        }
		if(isset($cookie))
		{
			$curlOptions[CURLOPT_COOKIE] = $cookie;
		}
		if($this->proxy != NULL)
		{
			$curlOptions[CURLOPT_PROXY] = $this->proxy;
		}
        curl_setopt_array($ch, $curlOptions);
		//var_dump($curlOptions);die;
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        if(0 != $errno)
        {
        	return false;
        }
        curl_close($ch);
        return $response;
	}

    /**
     * request请求访问外网（GET || POST）
     *
     * @param string $url 请求的url
     * @param array $data 请求传输的数据
     * @param string $method 请求的方法：GET || POST
     * @param bool $isHttps 是否是https
     * @return string 返回运行结果
     */
    public static function requestout($url, $data = array(), $method  = 'GET', $isHttps = false, $cookie = NULL, $linkTime=1, $dealTime=1, $httpHeader= array())
    {
        $PROXY_IP_OUT = "***.***.***.***:port";
        $HTTPS_IP_OUT = "***.***.***.***:port";
		
        $ch = curl_init();
        $curlOptions = array(
            CURLOPT_URL				=>	$url,
            CURLOPT_CONNECTTIMEOUT	=>	$linkTime,
            CURLOPT_TIMEOUT			=>	$dealTime,
            CURLOPT_RETURNTRANSFER	=>	true,
            CURLOPT_HEADER			=>	false,
            CURLOPT_FOLLOWLOCATION	=>	true,
            CURLOPT_HTTPHEADER		=>  $httpHeader,
            CURLOPT_USERAGENT => 'tingapi',
        );

        if (strpos($url, '10.') === false && strpos($url, '192.168.') === false && $url != 'https://buy.itunes.apple.com/verifyReceipt' && $url != 'https://sandbox.itunes.apple.com/verifyReceipt')
        {
            $curlOptions[CURLOPT_PROXY] = $PROXY_IP_OUT;
        }
        if (strpos($url, 'https') !== false){
            $curlOptions[CURLOPT_PROXY] = $HTTPS_IP_OUT;
            $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        }

		if($method === 'POST'){
        	$curlOptions[CURLOPT_POST] = true;
        }
        if($method === 'PUT'){
        	$curlOptions[CURLOPT_PUT] = true;
        }
        
        if('POST' === $method || 'PUT' === $method){
			if(is_array($data)){
				$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($data);
			}else{
				$curlOptions[CURLOPT_POSTFIELDS] = $data;
			}
        }
        if(true === $isHttps){
        	$curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        }
		if(isset($cookie)){
			$curlOptions[CURLOPT_COOKIE] = $cookie;
		}
        curl_setopt_array($ch, $curlOptions);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);

        if(0 != $errno)
        {
			header("Cache-Control:no-cache");
        	curl_close($ch);

			$data['errno'] = $errno;

        	return false;
        }

        curl_close($ch);

        return $response;
    }

    public function requestBCC($url, $head) {
        $PROXY_IP_OUT = "***.***.***.***:port";
        $HTTPS_IP_OUT = "***.***.***.***:port";
        $curlp = curl_init();
	// 设置header
	$header = array();
	foreach($head as $key => $val) {
	    $header[] = $key . ":" . $val;
	}
        curl_setopt($curlp, CURLOPT_URL, $url);
        curl_setopt($curlp, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curlp, CURLINFO_HEADER_OUT, 1);
        curl_setopt($curlp, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlp, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlp, CURLOPT_TIMEOUT, 10);
        if (strpos($url, '10.') === false && strpos($url, '192.168.') === false && $url != 'https://buy.itunes.apple.com/verifyReceipt' && $url != 'https://sandbox.itunes.apple.com/verifyReceipt') {
            $curlOptions[CURLOPT_PROXY] = $PROXY_IP_OUT;
            curl_setopt($curlp, CURLOPT_PROXY, $PROXY_IP_OUT);
        }
        if (strpos($url, 'https') !== false) {
            curl_setopt($curlp, CURLOPT_PROXY, $HTTPS_IP_OUT);
            curl_setopt($curlp, CURLOPT_SSL_VERIFYPEER, $HTTPS_IP_OUT);
        }
        $response = curl_exec($curlp);
        $request = curl_getinfo($curlp, CURLINFO_HEADER_OUT);
        $status = curl_getinfo($curlp, CURLINFO_HTTP_CODE);
        curl_close($curlp);
	return $response;
    }
 
}
?>