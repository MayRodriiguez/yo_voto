<?php
ini_set('session.cookie_path', '/yo_voto/');
ini_set('session.cookie_httponly', 1);
session_name('YOVOTO_SESSION');
session_start();

$_SESSION['user'] = [
    'id'              => 1,
    'nombres'         => 'jose luis',
    'apellidos'       => 'perez',
    'carnet'          => '12345678',
    'rol'             => 'usuario',
    'habilitado_voto' => 1,
    'ya_voto'         => 0,
];

header("Location: /yo_voto/mi-perfil");
exit();

//<!--esto hace iniciar sesion, para poder hacer las pruebas de la aplicacion sin necesidad de pasar por el login-->