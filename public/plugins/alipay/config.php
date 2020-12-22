<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2016102500759883",

		//商户私钥
		'merchant_private_key' => "MIIEpgIBAAKCAQEAoxvJ7WBf61ukI/goOoGdnrWEX4+nG2hvL6aQZWL/lyCBKFt5ypKwzXEuKbpD/3kiMEY02LWLJbcSQ1a1Vyvmi1kUuyue4iTdU48cQ96Wre2xbl1Kk8nhf1pOgJ0oQByx5J/stBqiy49jM4i5PXissfyPTYULBV5rpWyE3FMDRhOhE2IGpRd273rGvobw2mAYd7OBGP3eJa560VXUYH2fo4uYLHJYAOzgdffL7K6Vy3XUKeYtc6mtV8eQWVHGz9IJY2Lz48tmNmXOGNbPB93l5aAfc/SB7hhe1iHLSy5ykNI0O9KVrFann1sC1QowU1XwyHs/oSJWd0jV4tWm8fJT7wIDAQABAoIBAQCSPzZc+s7GbxLLlZQscU9soICiVmGVN5yJ8yUBYzScwu5Ly0l2kvSoIFUt4O9bP7eh5TE5Jf4vwOhFLJfII3rBcwwdjs0VI8y3QVgsmoYsSTWQKWHXBWqFD+nXdBJE+fWfcq2AB2n0bwqZiHvfXDYsGLI85Y+uLq0CVD0EeAI1b67W2NYRtaZDTrV8E3WpwhJLWWW5OaE1/96sWBww/nMyrmHhwEOhqr/DhVlWbSNEuqQMyS7R6T7d09F7KcybEsfflhD2hWSBi9V5RTBVIg+VjmWr1V4s9zUUwP3+MCFE5jXDkEEcrhpR9AIBhgXTo3IZrSbPXJ29/r9/YTPKEn6hAoGBAOf5lmldjGjhyBsISidVA0pux8sr4/+VkCGQSSrUgX9cMu7gZB2JhG8bW/+GH0WqzOvmhtSEl5fkwcrDhfBQAcN5LkCIAQBDQmghUFTfiiIBa07ZPCkg/OSQEdljXbgzk/nGyAslAuNn5rT6dOvLgtmogVlqSWPXL+w9AttrgN0HAoGBALQAVA7IZxQdGTZBc5OOKfas4q3fXzuKzrvnLdJzOCrVhSFu1DgbAPWpUWktObd5cmLMVlS/Lt5XyElQfsnwm4UQ3/sJ7erJKmzjljk3oC9O8SkwiZ5QHtLBadxsLPwb4Std+AFhULii+z6JyUdzcGXlMSoYi7W8SDfoi58Hy//ZAoGBAKMocjlzucE/JK6WuwlFCwZ8OnxVR4zJllF1GXFNfDOnFo/bNa9svMAk/yUPIcmvY2h8gNLS24jTNda2hOtMaqEhB34N6p5TsE5rOAQqIg7e7qnLszu+XwEnr9Y1xII9jNO+k477HjfyKVubWUdLoaITmb7ZOftGLAe9tde8mitbAoGBAK5q4wiwB7HlwFhic6u3RdJRFBWHLLB1gH1zNWOHYhWfcLFEwz7aa8OfndNcj2DJvZ0eg0j7OF8akGj6JuFm1EvMXjzTEkc/Rmzc2uP2krFEvUo8Th2pZTSzVfDQoYZpPXbYR0iPE7jVtL6UpQUnRvJ1c35m6nnfR9tk6mXfGD2BAoGBALpmwSq1cFrJAmY4aq07RxHSkG43iPZXo0hT/uLsYV/yUS5FpmSjJXKjxrzKmcN3dbpDqd3TLouRxtQtdQP7DHT6DbqYnXMGs1uJoUheSQUs5bWVZ6ERPWTVA2lf9BnXmLUV4+6GILpFFAoMPyjKafIkIIIaF+PVS8FuXg1SusXO",
		
		//异步通知地址
		'notify_url' => "http://shop.welite.top/home/order/notify",
		
		//同步跳转
		'return_url' => "http://shop.welite.top/home/order/callback",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmJDRODEB9ItpyUu0aV/ZgpkQM7TkgSI6u7u36Qr+2ZYX6qFT6poIFjm4C8rXTiBxdyhLPPmMTSusiFcZXYdz7yFEC5LWLd4mSiqG5to0Yrndi/mT5363SeqoOpfi26EA9M3nkcYrwimGgYyqbW5D/SyNYYNfU/Rx5PrcEyo0TWSEa25pMqoG/nnEs/F/01B+G5e2/B4zM3/F6rzd5KlCZoxWo2iyHs6KLtv2F7kyT0Ye+Y/LHYTQdz8LaIdHzBQ2IikcpXtrkpTUDKs+ziJXMm9QeADb+TlJln9QC5PhIi1mLRjP/BYlyV3jWi2SaeM7EEXryE2IVpRpYlpJs2aytwIDAQAB",
);