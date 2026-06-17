<?php

function sendFonnteMessage($target, $message)
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'target' => $target,
            'message' => $message,
        ],
        CURLOPT_HTTPHEADER => [
            "Authorization: " . FONNTE_TOKEN
        ],
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return [
            'success' => false,
            'error' => $error
        ];
    }

    $decoded = json_decode($response, true);

    return [
        'success' => isset($decoded['status']) && $decoded['status'] == true,
        'response' => $response
    ];
}