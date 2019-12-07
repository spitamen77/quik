<?php
/**
 * Created by PhpStorm.
 * Author: Abdujalilov Dilshod
 * Telegram: https://t.me/coloterra
 * Web: http://code.uz
 * Content: "Simplex CMS"
 * Site: http://simplex.uz
 * Date: 11.04.2019 19:49
 */

namespace App\Http\Controllers\Api;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use phpseclib\Net\SFTP;

class Queque
{
    public $token;
    public static function send_sms($phone,$message)
    {
        $_client = new \GuzzleHttp\Client([
            'cookies' => TRUE,
            'verify' => FALSE,
            'base_uri' => 'https://notify.eskiz.uz', // env('NOTIFY_URL')
            'defaults' => [
                'headers' => ['content-type' => 'application/x-www-form-urlencoded']
            ]
        ]);
        // Authenticate to API
        $data = array(
            'email' => 'rnn0891@gmail.com', // env('NOTIFY_USER')
            'password' => 'NEqhOrcb4yDSpPQsK0nhfQ1wetSyk1FYIUezAXVm' // env('NOTIFY_PASS')
        );
        $res = $_client->request('POST', '/api/auth/login', ['form_params' => $data]); // shu qatorda serikda muammo bor

        $data = json_decode($res->getBody());
        if(isset($data->data->token))
        $token = $data->data->token;

        $data = array(
            'message' => $message,
            'mobile_phone' => $phone
        );
        $res = $_client->request('POST', '/api/message/sms/send', [
            'form_params' => $data,
            'headers' => [
                'Authorization' => 'Bearer '.$token
            ]
        ]);

        return $res->getBody();
    }

    public static function send_email($to, $subject, $message){
        $mail = new PHPMailer(true);
        $mail->SMTPDebug =0;                                 // Enable verbose debug output
        $mail->isSMTP();// Set mailer to use SMTP

        $mail->Host = 'smtp.googlemail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'bitsimplex.net@gmail.com';                 // SMTP username
        $mail->Password = 'b1t$!mplex';                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        try {
            $mail->setFrom('info@bitsimplex.net', 'MyCity.uz');
            $mail->addAddress($to);
            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = mb_convert_encoding($subject,"UTF-8", "auto");
            $mail->Body = $message;
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $mail->send();
        }catch(Exception $e){

        }
    }

    public static function getRadius($lat, $lng, $radius) {
        $earthRadius = 6371;
        $maxLat = $lat + rad2deg($radius / $earthRadius);
        $minLat = $lat - rad2deg($radius / $earthRadius);
        $maxLng = $lng + rad2deg($radius / $earthRadius/cos(deg2rad($lat)));
        $minLng = $lng - rad2deg($radius / $earthRadius/cos(deg2rad($lat)));

        return array(
            'minLat' => $minLat,
            'maxLat' => $maxLat,
            'minLng' => $minLng,
            'maxLng' => $maxLng,
        );
    }

    public static function distance($lat1, $lon1, $lat2, $lon2, $unit) {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        }
        else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }

    public static function upload()
    {
        $host = 'my-city.uz';
        $username = 'efco';
        $password = 'simplex-2019';
//        $command = 'php version';

        $sftp = new SFTP($host);
        if (!$sftp->login($username, $password)) {
            return response()->json([
                'code' => 1,
                'message' => 'Login Failed'
            ]);
        }

        $sftp->chdir('..');
        $sftp->chdir('..');
        $sftp->chdir('var'); // open directory 'test'
        $sftp->chdir('www'); // open directory 'test'
        $sftp->chdir('my-city.uz'); // open directory 'test'
        $sftp->chdir('storage'); // open directory 'test'
        return $sftp;
        //        $sftp->delete('1557987565.jpg', false);
    }

}
