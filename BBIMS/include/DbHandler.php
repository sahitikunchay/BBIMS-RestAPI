<?php

/**
* Class to handle all db operations
* This class will have CRUD methods for database tables
*
* @author Ravi Tamada
* @link URL Tutorial link
*/
class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
    // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `users` table method ------------------ */

/**
 * Creating new user
 * @param String $name User full name
 * @param String $email User login email id
 * @param String $password User login password
 */


public function registerUser($id, $email, $name, $age, $contact_no, $password, $hostel, $department, $year) {
    require_once 'PassHash.php';
    $response = array();

    // First check if user already existed in db
    if (!$this->isUserExists($id)) {
        // Generating password hash
        $password_hash = PassHash::hash($password);

        // Generating API key
        //$api_key = $this->generateApiKey();

        // insert query
        $stmt = $this->conn->prepare("INSERT INTO user(id, email, name, age, contact_no, password, hostel, department, year) values(?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiisssi", $id, $email, $name, $age, $contact_no, $password_hash, $hostel, $department, $year);

        $result = $stmt->execute();

        $stmt->close();

        // Check for successful insertion
        if ($result) {
            // User successfully inserted
            return USER_CREATED_SUCCESSFULLY;
        } else {
            // Failed to create user
            return USER_CREATE_FAILED;
        }
    } else {
        // User with same email already existed in the db
        return USER_ALREADY_EXISTED;
    }

    return $response;
}

public function deregisterUser($id, $email, $password) {
    require_once 'PassHash.php';
    $response = array();

    // First check if user already existed in db
    if ($this->isUserExists($id)) {
        // Generating password hash
        $password_hash = PassHash::hash($password);
        if($this->checkLogin($email, $password)){



            $stmt2 = $this->conn->prepare("SELECT tag_id FROM tag WHERE user_id = ?");
            $stmt2->bind_param("s", $id);
            $stmt2->execute(); 
            $stmt2->store_result();
            $stmt2->bind_result($tag_id);
            while ($row = $stmt2->fetch()) {
                $this->deregisterTag($tag_id, $id);
            }
            $stmt2->close();

            $stmt = $this->conn->prepare("DELETE FROM user WHERE id = ?");
            $stmt->bind_param("s", $id);
            $result = $stmt->execute();
            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return 'USER_DEREGISTERED_SUCCESSFULLY';
            } else {
                // Failed to create user
                return 'USER_DEREGISTRATION_FAILED';
            }
        }   else {
                // Failed to create user
            return 'USER_CREDENTIALS_WRONG';
        }
    } else {
        // User with same email already existed in the db
        return 'USER_DOES_NOT_EXIST';
    }

    return $response;
}

private function isUserExists($id) {
    $stmt = $this->conn->prepare("SELECT email from user WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->store_result();
    $num_rows = $stmt->num_rows;
    $stmt->close();
    return $num_rows > 0;
}

public function getUserByEmail($email) {
    $stmt = $this->conn->prepare("SELECT name, contact_no FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    if ($stmt->execute()) {
        // $user = $stmt->get_result()->fetch_assoc();
        $stmt->bind_result($name, $contact_no);
        $stmt->fetch();
        $user = array();
        $user["name"] = $name;
        $user["contact_no"] = $contact_no;
        $stmt->close();
        return $user;
    } else {
        return NULL;
    }
}

public function checkLogin($email, $password) {
    // fetching user by email
    $stmt = $this->conn->prepare("SELECT password FROM user WHERE email = ?");

    $stmt->bind_param("s", $email);

    $stmt->execute();

    $stmt->bind_result($password_hash);

    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Found user with the email
        // Now verify the password

        $stmt->fetch();

        $stmt->close();

        if (PassHash::check_password($password_hash, $password)) {
            // User password is correct
            return TRUE;
        } else {
            // user password is incorrect
            return FALSE;
        }
    } else {
        $stmt->close();

        // user not existed with the email
        return FALSE;
    }
}

public function registerTag($tag_id, $user_id) {
    $response = array();

    // First check if user already existed in db 
    if($this->isUserExists($user_id)){
        if (!$this->isTagExists($tag_id)) {

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO tag(tag_id, user_id, status) values(?, ?, 1)");
            $stmt->bind_param("ss", $tag_id, $user_id);

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return 'TAG_CREATED_SUCCESSFULLY';
            } else {
                // Failed to create user
                return 'TAG_CREATE_FAILED';
            }
        } else {
            // User with same email already existed in the db
            return 'TAG_ALREADY_EXISTED';
        }
    } else {
            // User with same email already existed in the db
        return 'USER_DOES_NOT_EXIST';
    }
    return $response;
}

public function deregisterTag($tag_id, $user_id) {
    $response = array();

    // First check if user already existed in db 

    if ($this->isTagExists($tag_id)) {

            // insert query
        $stmt = $this->conn->prepare("DELETE FROM tag WHERE tag_id = ? AND user_id = ?");
        $stmt->bind_param("ss", $tag_id, $user_id);
        $result = $stmt->execute();
        $stmt->close();

        $stmt2 = $this->conn->prepare("DELETE FROM object WHERE tag_id = ?");
        $stmt2->bind_param("s", $tag_id);
        $result2 = $stmt2->execute();
        $stmt2->close();

            // Check for successful insertion
        if ($result && $result2) {
                // User successfully inserted
            return 'TAG_DEREGISTERED_SUCCESSFULLY';
        } else {
                // Failed to create user
            return 'TAG_DEREGISTRATION_FAILED';
        }
    } else {
            // User with same email already existed in the db
        return 'TAG_DOES_NOT_EXIST';
    }
    
    return $response;
}


private function isTagExists($tag_id) {
    $stmt = $this->conn->prepare("SELECT tag_id from tag WHERE tag_id = ?");
    $stmt->bind_param("s", $tag_id);
    $stmt->execute();
    $stmt->store_result();
    $num_rows = $stmt->num_rows;
    $stmt->close();
    return $num_rows > 0;
}

public function registerObject($object_id, $tag_id, $name, $description) {


    // First check if user already existed in db
    if($this->isTagExists($tag_id)){
        if (!$this->isObjectExists($object_id)) {

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO object(object_id, tag_id, name, description) values(?, ?, ?, ?)");
            $stmt->bind_param("ssss", $object_id, $tag_id, $name, $description);

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return 'OBJECT_CREATED_SUCCESSFULLY';
            } else {
                // Failed to create user
                return 'OBJECT_CREATE_FAILED';
            }
        } else {
            // User with same email already existed in the db
            return 'OBJECT_ALREADY_EXISTED';
        }
    } else {
            // User with same email already existed in the db
        return 'TAG_DOES_NOT_EXIST';
    }

    
}

public function deregisterObject($object_id, $tag_id) {


    // First check if user already existed in db
    if($this->isObjectExists($object_id)) {

            // insert query
        $stmt = $this->conn->prepare("DELETE FROM object WHERE object_id = ?");
        $stmt->bind_param("s", $object_id);

        $result = $stmt->execute();

        $stmt->close();

            // Check for successful insertion
        if ($result) {
                // User successfully inserted
            return 'OBJECT_DEREGISTERED_SUCCESSFULLY';
        } else {
                // Failed to create user
            return 'OBJECT_DEREGISTRATION_FAILED';
        }
    } else {
            // User with same email already existed in the db
        return 'OBJECT_DOES_NOT_EXIST';
    }

    
}

private function isObjectExists($object_id) {
    $stmt = $this->conn->prepare("SELECT name from object WHERE object_id = ?");
    $stmt->bind_param("s", $object_id);
    $stmt->execute();
    $stmt->store_result();
    $num_rows = $stmt->num_rows;
    $stmt->close();
    return $num_rows > 0;
}

public function getAllTags($user_id) {

    $stmt = $this->conn->prepare("SELECT tag_id, status FROM tag WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $tmp = array();
    $tag = array();

    $stmt->bind_result($tag_id, $status);
    while($stmt -> fetch()){
        $tag["tag_id"] = $tag_id;
        $tag["status"] = $status;
        array_push($tmp, $tag);
    }
    $stmt->close();
    return $tmp;
}

public function getAllObjects($user_id) {

    $stmt = $this->conn->prepare("SELECT tag_id FROM tag WHERE user_id = ? ");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->store_result();

    $stmt -> bind_result($tmpTag);
    $tag = array();
    $tmpObject = array();
    while($row = $stmt -> fetch()){
        array_push($tag, $tmpTag);
    }

    $stmt->close();

    $len = count($tag);
    $object = array();

    for($i=0; $i<$len; $i++){
        $stmt = $this->conn->prepare("SELECT * FROM object WHERE tag_id = ?");
        $stmt->bind_param("s", $tag[$i]);
        $stmt->execute();
        $stmt->store_result();
        
        $stmt -> bind_result($tmpObject["object_id"], $tmpObject["tag_id"], $tmpObject["name"], $tmpObject["description"]);
        while($stmt -> fetch()){
            array_push($object, $tmpObject);
        }
    }

    return $object;
    
}

public function reportLostObject($object_id, $tag_id, $last_seen_lat, $last_seen_lng) {

   // insert query
    $stmt = $this->conn->prepare("INSERT INTO lost_objects(object_id, tag_id, last_seen_lat, last_seen_lng) values(?, ?, ?, ?)");
    $stmt->bind_param("ssss", $object_id, $tag_id, $last_seen_lat, $last_seen_lng);
    $result = $stmt->execute();
    $stmt->close();

    $stmt = $this->conn->prepare("UPDATE tag SET status = 0 WHERE tag_id = ?");
    $stmt->bind_param("s", $tag_id);
    $result2 = $stmt->execute();
    $stmt->close();
    // Check for successful insertion
    if ($result && $result2) {
        // User successfully inserted
        return 'LOST_REPORT_SUBMISSION_SUCESSFUL';
    } else {
        // Failed to create user
        return 'LOST_REPORT_SUBMISSION_UNSUCESSFUL';
    }


    
}

function sendMessageThroughFCM($reg_id, $message) {
        //Google cloud messaging GCM-API url
        define("FIREBASE_API_KEY", "AAAAkZmC0Tg:APA91bHKpiK-0SINIERcthh-LDCQAphccX542PICWAfFZ7M2EXi-9mnRAy8q9jYpYFmegdOvUqrpBzSpeZ4LFr_tZT8OmH44XFII3vtiMjWnavTr1knMTS0Bu0L3QzjN3iWn7ut2mQaS3f8bCpEzxZDk7PRr1FN1TQ"); 
        $url = 'https://fcm.googleapis.com/fcm/send';
        define("FCM_URL", "https://fcm.googleapis.com/fcm/send");
        $fields = array(
            'to' => $reg_id ,
            'priority' => "high",
            'notification' => array( "title" => "Android Learning", "body" => $message),
            'data' => array("message" =>$message),
        );
 
        $headers = array(
         FCM_URL,
        'Content-Type: application/json',
        'Authorization: key=' . FIREBASE_API_KEY 
        );
        // Update your Google Cloud Messaging API Key

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);   
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);               
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
        
    }

}

?>
