<?php
// PayPal IPN Listener

// Lê os dados brutos da requisição do PayPal
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// Adiciona 'cmd' ao post para validação do PayPal
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode($value);
    $req .= "&$key=$value";
}

// Envia os dados de volta ao PayPal para validação
$ch = curl_init('https://www.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

$res = curl_exec($ch);
curl_close($ch);

// Verifica se o PayPal confirmou a validação
if (strcmp($res, "VERIFIED") == 0) {
    // Dados do pagamento recebidos
    $payer_email = $_POST['payer_email'];
    $payment_status = $_POST['payment_status'];

    // Verifica se o pagamento foi concluído
    if ($payment_status == "Completed") {
        // Envia o email com o link do produto
        $to = $payer_email;
        $subject = "Content the Lucy";
        $message = "Obrigado pela sua compra! Aqui está o link para o seu produto: googl.com";
        $headers = "From: no-reply@seusite.com";

        mail($to, $subject, $message, $headers);
    }
} else if (strcmp($res, "INVALID") == 0) {
    // O IPN é inválido, tome as ações apropriadas
}
?>
