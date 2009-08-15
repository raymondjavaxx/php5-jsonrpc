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
 * @copyright Copyright (c) 2009 Ramon Torres
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * JSON-RPC server
 *
 * @package JsonRpc
 * @copyright Copyright (c) 2009 Ramon Torres
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class JsonRpc_Server {

	/**
	 * undocumented variable
	 *
	 * @var object
	 */
	protected $_object;

	/**
	 * undocumented function
	 *
	 * @param string $object 
	 * @return JsonRpc_Server
	 */
	public function setObject($object) {
		$this->_object = $object;
		return $this;
	}

	/**
	 * Handles request
	 *
	 * @return void
	 */
	public function handle() {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			die('JSON-RPC Server');
		}

		try {
			$request = json_decode(file_get_contents('php://input'));

			$reflection = new ReflectionMethod($this->_object, $request->method);
			if ($reflection->getNumberOfRequiredParameters() > count($request->params)) {
				throw new Exception("Params count doesn't match method signature.");
			}

			if (!$reflection->isPublic() || $reflection->isInternal()) {
				throw new Exception("Method is not callable");
			}

			$result = call_user_func_array(array($this->_object, $request->method), $request->params);
			$response = array(
				'id'     => $request->id,
				'result' => $result,
				'error'  => null
			);
		} catch (Exception $e) {
			$response = array(
				'id'     => $request->id,
				'result' => null,
				'error'  => $e->getMessage()
			);
		}

		$request = json_decode(file_get_contents('php://input'));
		header('content-type: text/javascript');
		echo json_encode($response);
	}
}
