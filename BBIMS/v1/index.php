  <?php

  require_once '../include/DbHandler.php';
  require_once '../include/PassHash.php';
  require '.././libs/Slim/Slim.php';

  \Slim\Slim::registerAutoloader();

  $app = new \Slim\Slim();

  // User id from db - Global Variable
  $user_id = NULL;

  /**
  * Adding Middle Layer to authenticate every request
  * Checking if the request has valid api key in the 'Authorization' header
  */

  /**
  * ----------- METHODS WITHOUT AUTHENTICATION ---------------------------------
  */
  /**
  * User Registration
  * url - /register
  * method - POST
  * params - name, email, password
  */


  $app->post('/registerUser', function() use ($app) {
   $request = \Slim\Slim::getInstance()->request();

   $body = array();
    //$body = $request->post();
   $id = $request->post('snu_id');
   $email = $request->post('email');
   $name = $request->post('name');
   $age = $request->post('age');
   $contact_no = $request->post('contact_no');
   $password = $request->post('password');
   $hostel = $request->post('hostel');
   $department = $request->post('department');
   $year = $request->post('year');


   $db = new DbHandler();
   $res = $db->registerUser($id, $email, $name, $age, $contact_no, $password, $hostel, $department, $year);

   if ($res == USER_CREATED_SUCCESSFULLY) {
    $response["error"] = false;
    $response["message"] = "USER_REGISTRATION_SUCCESSFUL";
    echoRespnse(201, $response);
  }
  else if ($res == USER_CREATE_FAILED) {
    $response["error"] = true;
    $response["message"] = "USER_REGISTRATION_UNSUCCESSFUL";
    echoRespnse(200, $response);
  }
  else if ($res == USER_ALREADY_EXISTED) {
    $response["error"] = true;
    $response["message"] = "EMAIL_EXISTS";
    echoRespnse(200, $response);
  }
  });


  $app->post('/deregisterUser', function() use ($app) {
   $request = \Slim\Slim::getInstance()->request();

   $body = array();
    //$body = $request->post();
   $id = $request->post('snu_id');
   $email = $request->post('email');
   $password = $request->post('password');


   $db = new DbHandler();
   $res = $db->deregisterUser($id, $email, $password);

   if ($res == 'USER_DEREGISTERED_SUCCESSFULLY') {
    $response["error"] = false;
    $response["message"] = "USER_REGISTRATION_SUCCESSFUL";
    echoRespnse(201, $response);
  }
  else if ($res == 'USER_DEREGISTRATION_FAILED') {
    $response["error"] = true;
    $response["message"] = "USER_DEREGISTRATION_UNSUCCESSFUL";
    echoRespnse(200, $response);
  }
  else if ($res == 'USER_DOES_NOT_EXIST') {
    $response["error"] = true;
    $response["message"] = "USER_DOES_NOT_EXIST";
    echoRespnse(200, $response);
  }
  else if ($res == 'USER_CREDENTIALS_WRONG') {
    $response["error"] = true;
    $response["message"] = "USER_CREDENTIALS_WRONG";
    echoRespnse(200, $response);
  }
  });


  $app->post('/login', function() use ($app) {
    $request = \Slim\Slim::getInstance()->request();
            // check for required params
    verifyRequiredParams(array('email', 'password'));

            // reading post params
    $email = $app->request()->post('email');
    $password = $app->request()->post('password');
    $response = array();

    $db = new DbHandler();
            // check for correct email and password
    if ($db->checkLogin($email, $password)) {
                // get the user by email
      $user = $db->getUserByEmail($email);

      if ($user != NULL) {
        $response["error"] = false;
        $response['message'] = "Logged In";

      } else {
                    // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
      }
    } else {
                // user credentials are wrong
      $response['error'] = true;
      $response['message'] = 'Login failed. Incorrect credentials';
    }

    echoRespnse(200, $response);
  });


  $app->post('/registerTag', function() use ($app) {
   $request = \Slim\Slim::getInstance()->request();

   $body = array();
    //$body = $request->post();
   $tag_id = $request->post('tag_id');
   $user_id = $request->post('user_id');


   $db = new DbHandler();
   $res = $db->registerTag($tag_id, $user_id);

   if ($res == 'TAG_CREATED_SUCCESSFULLY') {
    $response["error"] = false;
    $response["message"] = "TAG_REGISTRATION_SUCCESSFUL";
    echoRespnse(201, $response);
  }
  else if ($res == 'TAG_CREATE_FAILED') {
    $response["error"] = true;
    $response["message"] = "TAG_REGISTRATION_UNSUCCESSFUL";
    echoRespnse(200, $response);
  }
  else if ($res == 'TAG_ALREADY_EXISTED') {
    $response["error"] = true;
    $response["message"] = "TAG_EXISTS";
    echoRespnse(200, $response);
  }

  });


  $app->post('/deregisterTag', function() use ($app) {
   $request = \Slim\Slim::getInstance()->request();

   $body = array();
    //$body = $request->post();
   $tag_id = $request->post('tag_id');
   $user_id = $request->post('user_id');


   $db = new DbHandler();
   $res = $db->deregisterTag($tag_id, $user_id);

   if ($res == 'TAG_DEREGISTERED_SUCCESSFULLY') {
    $response["error"] = false;
    $response["message"] = "TAG_DEREGISTRATION_SUCCESSFUL";
    echoRespnse(201, $response);
  }
  else if ($res == 'TAG_DEREGISTRATION_FAILED') {
    $response["error"] = true;
    $response["message"] = "TAG_DEREGISTRATION_UNSUCCESSFUL";
    echoRespnse(200, $response);
  }
  else if ($res == 'TAG_DOES_NOT_EXIST') {
    $response["error"] = true;
    $response["message"] = "TAG_DOES_NOT_EXIST";
    echoRespnse(200, $response);
  }
  else if ($res == 'USER_DOES_NOT_EXIST') {
    $response["error"] = true;
    $response["message"] = "USER_DOES_NOT_EXIST";
    echoRespnse(200, $response);
  }
  });


  $app->post('/displayAllUserTags', function() use($app){
    $response = array();
    $db = new DbHandler();

    $user_id = $app->request()->post('user_id');

    $result = array();
    $result = $db->getAllTags($user_id);

    $response["error"] = false;
    $response["tag"] = $result;
    echoRespnse(200, $response);
  });

  

  $app->post('/registerObject', function() use ($app) {
   $request = \Slim\Slim::getInstance()->request();

   $body = array();

   $object_id = $request->post('object_id');
   $tag_id = $request->post('tag_id');
   $name = $request->post('name');
   $description = $request->post('description');

   $db = new DbHandler();
   $res = $db->registerObject($object_id, $tag_id, $name, $description);

   if ($res == 'OBJECT_CREATED_SUCCESSFULLY') {
    $response["error"] = false;
    $response["message"] = "OBJECT_REGISTRATION_SUCCESSFUL";
    echoRespnse(201, $response);
  }
  else if ($res == 'OBJECT_CREATE_FAILED') {
    $response["error"] = true;
    $response["message"] = "OBJECT_REGISTRATION_UNSUCCESSFUL";
    echoRespnse(200, $response);
  }
  else if ($res == 'OBJECT_ALREADY_EXISTED') {
    $response["error"] = true;
    $response["message"] = "OBJECT_EXISTS";
    echoRespnse(200, $response);
  }
  else if ($res == 'TAG_DOES_NOT_EXIST') {
    $response["error"] = true;
    $response["message"] = "TAG_DOES_NOT_EXIST";
    echoRespnse(200, $response);
  }
  });


  $app->post('/deregisterObject', function() use ($app) {
   $request = \Slim\Slim::getInstance()->request();

   $body = array();

   $object_id = $request->post('object_id');
   $tag_id = $request->post('tag_id');

   $db = new DbHandler();
   $res = $db->deregisterObject($object_id, $tag_id);

   if ($res == 'OBJECT_DEREGISTERED_SUCCESSFULLY') {
    $response["error"] = false;
    $response["message"] = "OBJECT_DEREGISTRATION_SUCCESSFUL";
    echoRespnse(201, $response);
  }
  else if ($res == 'OBJECT_DEREGISTRATION_FAILED') {
    $response["error"] = true;
    $response["message"] = "OBJECT_DEREGISTRATION_UNSUCCESSFUL";
    echoRespnse(200, $response);
  }
  else if ($res == 'OBJECT_DOES_NOT_EXIST') {
    $response["error"] = true;
    $response["message"] = "OBJECT_DOES_NOT_EXIST";
    echoRespnse(200, $response);
  }
  });

  $app->post('/displayAllUserObjects', function() use($app){
    $response = array();
    $db = new DbHandler();

    $user_id = $app->request()->post('user_id');

    $result = array();
    $result = $db->getAllObjects($user_id);

    $response["error"] = false;
    $response["object"] = $result;
    echoRespnse(200, $response);
  });

  $app->post('/reportLostObject', function() use ($app) {
   $request = \Slim\Slim::getInstance()->request();

   $body = array();

   $object_id = $request->post('object_id');
   $tag_id = $request->post('tag_id');
   $last_seen_lat = $request->post('last_seen_lat');
   $last_seen_lng = $request->post('last_seen_lng');
   
   $db = new DbHandler();
   $res = $db->reportLostObject($object_id, $tag_id, $last_seen_lat, $last_seen_lng);

   if ($res == 'LOST_REPORT_SUBMISSION_SUCESSFUL') {
    $response["error"] = false;
    $response["message"] = "OBJECT_REPORTED_AS_LOST";
    echoRespnse(201, $response);
  }
  else if ($res == 'LOST_REPORT_SUBMISSION_UNSUCESSFUL') {
    $response["error"] = true;
    $response["message"] = "OBJECT_LOST_REPORT_UNSUCESSFUL";
    echoRespnse(200, $response);
  }

  });

  $app->post('/reportFoundObject', function() use ($app) {
   $request = \Slim\Slim::getInstance()->request();

   $body = array();

   $object_id = $request->post('object_id');
   $tag_id = $request->post('tag_id');
   $last_seen_lat = $request->post('found_lat');
   $last_seen_lng = $request->post('found_lng');
   
   $db = new DbHandler();
   $res = $db->reportFoundObject($object_id, $tag_id, $found_lat, $found_lng);

   if ($res == 'FOUND_REPORT_SUBMISSION_SUCESSFUL') {
    $response["error"] = false;
    $response["message"] = "OBJECT_REPORTED_AS_FOUND";
    echoRespnse(201, $response);
  }
  else if ($res == 'FOUND_REPORT_SUBMISSION_UNSUCESSFUL') {
    $response["error"] = true;
    $response["message"] = "OBJECT_FOUND_REPORT_UNSUCESSFUL";
    echoRespnse(200, $response);
  }

  });

  $app->post('/sendFCM', function() use ($app) {
   $request = \Slim\Slim::getInstance()->request();

   $body = array();

   $reg_id = $request->post('reg_id');
   
   $db = new DbHandler();
   $res = $db->sendMessageThroughFCM($reg_id, "HI Manasa!");
   $response["message"] = $res;
    echoRespnse(201, $response);

  });

  function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
      $app = \Slim\Slim::getInstance();
      parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
      if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
        $error = true;
        $error_fields .= $field . ', ';
      }
    }

  }



  /**
  * User Login
  * url - /login
  * method - POST
  * params - email, password
  */

  /**
  * Validating email address
  */



  function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
  }

  $app->run();
  ?>