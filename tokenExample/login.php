<?php 
    require_once(__DIR__.'/firebase/JWT.php');
    use \Firebase\JWT\JWT;

	class Login extends Rest {
        public function __construct() {
			parent::__construct();
        }
        
        public function generateToken() {
            $phoneNumber = $this->validateParameter('user', $this->param['phonenumber'], STRING);
            $password = $this->validateParameter('password', $this->param['password'], STRING);

            if ($phoneNumber == '1234' && $password == 'asdf') {
                $userId = 100;
            } else {
                $this->throwError(INVALID_USER_PASS, 'Username or Password is incorrect');
            }
            
            $payload = [
                'iat' => time(),
                'iss' => 'localhost',
                'exp' => time() + (60),
                'userId' => $userId
            ];

            $token = JWT::encode($payload, SECRET_KEY);
            $data = ['token' => $token];
            $this->returnResponse(SUCCESS_RESPONSE, $data);
        }
    }
?>