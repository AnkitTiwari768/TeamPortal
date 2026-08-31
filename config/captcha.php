<?php

return [
    'default'   => [
        'length'    => 5,
        'width'     => 180,
        'height'    => 50,
        'quality'   => 90,
        'math'      => false, //Enable Math Captcha
        'expire'    => 180,   //Stateless/API captcha expiration
        'sensitive' => true,
        'bgImage' => false,
        'bgColor' => '#ecf2f4',
        'lines' => 0,
        'fontColors' => ['#2c3e50', '#c0392b', '#16a085', '#c0392b', '#8e44ad', '#303f9f', '#f57c00', '#795548'],
    ],
    // ...
];