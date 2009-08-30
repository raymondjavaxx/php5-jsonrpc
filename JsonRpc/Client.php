<?php
/**
 * PHP5 JSON-RPC
 *
 * Copyright (c) 2009 Ramon Torres
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package JsonRpc
 * @author Ramon Torres
 * @copyright Copyright (c) 2009 Ramon Torres
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version $Id$
 */

/**
 * JSON-RPC client
 *
 * @package JsonRpc
 * @copyright Copyright (c) 2009 Ramon Torres
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class JsonRpc_Client {

	/**
	 * Server URL
	 *
	 * @var string
	 */
	protected $_url;
	
	/**
	 * json decode returns an associative array
	 * 
	 * @var boolean
	 */
	protected $_assoc = false;

	/**
	 * Notification mode
	 *
	 * @var boolean
	 */
	protected $_notification = false;

	/**
	 * Constructor
	 *
	 * @param string $url 
	 * @param boolean $assoc
	 */
	public function __construct($url, $assoc = false) {
		$this->_url = $url;
		$this->_assoc = $assoc;
	}

	/**
	 * Enables and disables the notification mode.
	 *
	 * @param boolean $notification 
	 * @return void
	 */
	public function setNotification($notification) {
		$this->_notification = (boolean)$notification;
	}

	/**
	 * undocumented function
	 *
	 * @param string $method 
	 * @param array $params 
	 * @return mixed
	 */
	public function __call($method, $params) {
		$request = array(
			'method' => $method,
			'params' => $params,
			'id'     => $this->_notification ? null : 123 //uniqid()
		);

		$requestBody = json_encode($request);
		$opts = array('http' => array(
			'method' => 'POST',
			'header' => 'Content-type: application/json',
			'content'=> $requestBody
		));

		$context = stream_context_create($opts);
		$response = file_get_contents($this->_url, 0, $context);
		if ($response === false) {
			throw new Exception('Unable to connect to ' . $this->_url);
		}

		$response = json_decode($response, $this->_assoc);
		if ($response === null) {
			throw new Exception('Malformed response');
		}
		if($this->_assoc) {
			if ($response['id'] != $request['id']) {
				throw new Exception('Incorrect response id. Expected:' . $request['id'] . ' Received:' . $response['id']);
			}
	
			if (!is_null($response['error'])) {
				throw new Exception('JSON-RPC Request Error: ' . $response['error']);
			}
	
			return $this->_notification ? true : $response['result'];
		} else {
			if ($response->id != $request['id']) {
				throw new Exception('Incorrect response id. Expected:' . $request['id'] . ' Received:' . $response->id);
			}
	
			if (!is_null($response->error)) {
				throw new Exception('JSON-RPC Request Error: ' . $response->error);
			}
	
			return $this->_notification ? true : $response->result;
		}
	}
}
