<?php

namespace App\Services;


class WhatsAppService
{
    public function send($phone, $message)
    {
        ///curl -X POST http://72.61.98.190:5000/send-message \ -F "phone=201001659186" \ -F "message=Hello"
        /// 
        $url = "http://72.61.98.190:5000/send-message";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('phone' => $phone, 'message' => $message),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}