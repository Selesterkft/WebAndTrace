<?php
    require_once('constants.php');
    class Rest {
		private $request;
        public function __construct() {
            if($_SERVER['REQUEST_METHOD'] !== 'POST') {
				$this->throwError(REQUEST_METHOD_NOT_VALID, 'Request Method is not valid.');
            }
            
            $handler = fopen('php://input', 'r');
            $this->request = stream_get_contents($handler);
            $this->validateRequest();

            /**/
            if( 'generatetoken' != strtolower( $this->serviceName) ) {
				$this->validateToken();
			}
        }

        public function validateRequest() {
			if($_SERVER['CONTENT_TYPE'] !== 'application/json; charset=utf-8' && $_SERVER['CONTENT_TYPE'] !== 'application/json') {
				$this->throwError(REQUEST_CONTENTTYPE_NOT_VALID, 'Request content type is not valid'.$_SERVER['CONTENT_TYPE']);
			}
			/*if(!(is_string($this->request) && is_array(json_decode($this->request, true)))) {
				$this->throwError(REQUEST_CONTENTTYPE_NOT_VALID, 'Request content type is not valid'.$_SERVER['CONTENT_TYPE']);
			}*/

			$data = json_decode($this->request, true);

			if(!isset($data['name']) || $data['name'] == "") {
				$this->throwError(API_NAME_REQUIRED, "API name is required.");
			}
			$this->serviceName = $data['name'];

			if(!is_array($data['param'])) {
				$this->throwError(API_PARAM_REQUIRED, "API PARAM is required.");
			}
			$this->param = $data['param'];
        }

        public function validateParameter($fieldName, $value, $dataType, $required = true) {
			if($required == true && empty($value) == true) {
				$this->throwError(VALIDATE_PARAMETER_REQUIRED, $fieldName . " parameter is required.");
			}

			switch ($dataType) {
				case BOOLEAN:
					if(!is_bool($value)) {
						$this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName . '. It should be boolean.');
					}
					break;
				case INTEGER:
					if(!is_numeric($value)) {
						$this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName . '. It should be numeric.');
					}
					break;

				case STRING:
					if(!is_string($value)) {
						$this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName . '. It should be string.');
					}
					break;
				
				default:
					$this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldName);
					break;
			}

			return $value;
		}
        
        public function throwError($code, $message) {
			header("content-type: application/json");
			$errorMsg = json_encode(['error' => ['status' => $code, 'message' => $message]]);
            echo $errorMsg;
            exit;
		}
		
		public function returnResponse($code, $data) {
			header("content-type: application/json");
			$response = json_encode(['resonse' => ['status' => $code, 'result' => $data]]);
			echo $response; 
			exit;
		}

        public function processApi() {
			try {
				$rMethod = new reflectionMethod('Login', $this->serviceName);
				if(!method_exists($this, $this->serviceName)) {
					$this->throwError(API_DOST_NOT_EXIST, "API does not exist.");
				}
				$rMethod->invoke($this);
			} catch (Exception $e) {
				$this->throwError(API_DOST_NOT_EXIST, "API does not exist.");
			}
		}
    }
?>
