<?php

require_once 'config.php';
$site_app_id = SITE_APP_ID;

// берем основные данные о заказе
$amount = 2; // стоимость
$amount = (float)bcmul($amount, 1, 2);
$data = [
  'amount'        => $amount,
  'currency'      => 'RUB',
  'order_id'      => 255,
  'cashback'      => [
    'pay_time'        => time(),
    'amount_percent'  => 30
  ],
  'ts'            => time()
];

// добавляем параметры merchant_data и merchant_sign
$merchant_data = base64_encode(json_encode($data));
$data['merchant_data'] = $merchant_data;
$data['merchant_sign'] = sha1($merchant_data . MERCHANT_PRIVATE_KEY);

//собираем все параметры целиком в единый объект
$params = [
    'amount'      => $amount,
    'data'        => json_encode($data),
    'description' => 'Оплата заказа №'.$data['order_id'],
    'action'      => VK_ACTION_PAY_TO_SERVICE,
    'merchant_id' => MERCHANT_ID,
];

// генерируем подпись из всех параметров кроме action, добавляем в $params
$sign = '';
foreach ($params as $key => $value) {
  if ($key != 'action') {
    $sign .= ($key.'='.$value);
  }
}
$sign .= SITE_CLIENT_SECRET;
$params['sign'] = md5($sign);

// кодируем результат в JSON
$params = json_encode($params);

echo <<<HTML
<!doctype html>
<html>
<head>
  <link  rel="stylesheet" type="text/css" href="./css/style.css"/>
  <script src="https://vk.com/js/api/openapi.js?154"></script>
</head>
<body>
  <a id="pay" class="vkpay btn">Оплатить через</a>
</body>
<script>
  VK.init({
    apiId: {$site_app_id}
  });
  document.getElementById("pay").addEventListener("click", openPayform);

  function openPayform() {
    VK.App.open("vkpay", {$params});
  }
</script>
</html>
HTML;
