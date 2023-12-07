<?php


include 'DBconnect.php';
$objDB = new DbConnect();
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":

        $receiver_id = $_GET['receiver_id'];
        $sender_id = $_GET['sender_id'];

        $sql = "SELECT 
        (SELECT users.profile_picture FROM users WHERE users.user_id = :receiver_id) AS profile_picture,
        message.message_customer_id, 
        message.sender_id, 
        message.receiver_id, 
        message.message_context, 
        message.created_at, 
        users.name AS sender_username 
    FROM 
        message 
    LEFT JOIN 
        users ON users.user_id = message.sender_id 
    WHERE 
        ( message.receiver_id = :receiver_id AND message.sender_id = :sender_id OR message.sender_id = :receiver_id AND message.receiver_id = :sender_id)";


        if (isset($sql)) {
            $stmt = $conn->prepare($sql);


            $stmt->bindParam(':receiver_id', $receiver_id);
            $stmt->bindParam(':sender_id', $sender_id);




            $stmt->execute();
            $notification = $stmt->fetchAll(PDO::FETCH_ASSOC);


            echo json_encode($notification);
        }


        break;

    case "POST":
        $message = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO message (sender_id, receiver_id, message_context, created_at) VALUES (:sender_id, :receiver_id, :message_context, :created_at)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d');
        $stmt->bindParam(':sender_id', $message->sender_id);
        $stmt->bindParam(':receiver_id', $message->receiver_id);
        $stmt->bindParam(':message_context', $message->message_context);
        $stmt->bindParam(':created_at', $created_at);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "User message successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "User message failed"
            ];
        }

        echo json_encode($response);
        break;


    case "DELETE":
        $sql = "DELETE FROM users WHERE id = :id";
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $path[2]);

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "message" => "User deleted successfully"
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "User deletion failed"
            ];
        }
}
