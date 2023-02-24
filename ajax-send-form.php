<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'database-connect.php';

$input = array();

foreach ($_POST as $post_key => $post_value) {
    $input[$post_key] = sanitize_string($post_value);
}

function sanitize_string(string $var): string
{
    $var = strip_tags($var);
    $var = htmlentities($var);

    return stripslashes($var);
}

if(isset($input['brand']) && isset($input['model']) && isset($input['spare']) &&
   isset($input['name']) && isset($input['phone'])
) {
    $database_result = send_to_database($input);
    $mail_result = send_message($input);

    $response = [
        'databaseResult' => $database_result,
        'mailResult' => $mail_result,
    ];

    echo json_encode($response);
}

function send_to_database(array $input): string
{
    global $db;

    $insert_stmt = $db->prepare("INSERT INTO cars ( `brand`, `model`, `spare`, `name`, `phone` ) "
        . " VALUES ( :brand, :model, :spare, :name, :phone )");

    $insert_stmt->execute($input);


    $info = $insert_stmt->errorInfo();
    if ($info[0] == '00000') {
        $result = 'success';
    } else {
        $result = 'error';
    }
    return $result;
}

function send_message(array $input): string
{
    $to = '=?UTF-8?B?' . base64_encode('Иван') . '?= <ivan06042000@gmail.com>';

    $subject = '=?UTF-8?B?' . base64_encode('Ремонт машины') . '?=';

    $message = "
                <html lang='ru'>
                    <head>
                        <title>Ремонт Вашей Машины</title>
                    </head>
                    <body>
                        <h3>Благодарим, что доверились именно нам!<br>Ремонт будет сделан в лучшем виде!</h3>
                        <div>
                            <p>Ваша информация по ремонту:</p>
                            <ul>
                                <li>{$input['brand']}</li>
                                <li>{$input['model']}</li>
                                <li>{$input['spare']}</li>
                                <li>{$input['name']}</li>
                                <li>{$input['phone']}</li>
                            </ul>
                        </div>
                    </body>
                </html>
                ";

    $message = base64_encode($message);

    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'To: <ivan0604200@gmail.com>' . "\r\n";
    $headers .= 'From: <Admin@localhost>' . "\r\n";
    $headers .= 'X-Mailer: POST' . "\r\n";
    $headers .= 'X-Priority: 1' . "\r\n";
    $headers .= 'Content-Transfer-Encoding: base64' . "\r\n";

    $success = mail($to, $subject, $message, $headers);

    if (!$success) {
        $result = 'error';
    } else {
        $result = 'success';
    }
    return $result;
}