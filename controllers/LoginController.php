<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;;

class LoginController
{

    public static function login(Router $router)
    {
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarLogin();

            if (empty($alertas)) {
                // Verificar si el usuario existe
                $usuario = Usuario::where('email', $usuario->email);

                if (!$usuario || !$usuario->confirmado) {
                    Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
                } else {
                    //El usuario existe
                    if ( password_verify($_POST['password'] , $usuario->password) ) {

                        //Iniciar la seccion
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        //Redireccionar 
                        header('Location: /dashboard');
                    } else {
                        Usuario::setAlerta('error', 'Password Incorrecto');
                    }
                }

            }

        }

        $alertas = Usuario::getAlertas();
        //Render a la vista
        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesion',
            'alertas' => $alertas
        ]);
    }

    public static function logout()
    {
        session_start();
        $_SESSION = [];
        header('Location: /');

    }

    public static function crear(Router $router)
    {

        $usuario = new Usuario();
        $alertas = Usuario::getAlertas();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            if (!empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);
                if ($existeUsuario) {
                    Usuario::setAlerta('error', 'El usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                }
            } else {
                //Hashear el password
                $usuario->hashPassword();

                //Eliminar Password2
                unset($usuario->password2);

                //Generar el token
                $usuario->crearToken();

                //Crear un nuevo usuario
                $resultado = $usuario->guardar();

                // Enviar el email
                $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                $email->enviarConfirmacion();

                if ($resultado) {
                    header('Location: /mensaje');
                }
            }
        }

        //Render a la vista
        $router->render('auth/crear', [
            'titulo' => 'Crear tu cuenta en UpTask',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router)
    {

        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if (empty($alertas)) {
                //Buscar el usuario
                $usuario = Usuario::where('email', $usuario->email);

                if ($usuario && $usuario->confirmado === "1") {

                    //Generar un nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);

                    //Actualizar el usuario
                    $usuario->guardar();

                    //Enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    //Imprimir la alerta
                    Usuario::setAlerta('exito', 'Hemos enviados las instrucciones a tu email');
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
                }
            }
        }

        $alertas = Usuario::getAlertas();


        $router->render('auth/olive', [
            'titulo' => 'Olvide el PassWord',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router)
    {

        $token = s($_GET['token']);
        $mostrar = true;

        if (!$token) {
            header('Location: /');
        }
        //Identificar el usuario con este token
        $usuario = Usuario::where('token', $token);

        if (empty($usuario)) {
            //No se encontro usuario con ese token
            Usuario::setAlerta('error', 'Token no valido');
            $mostrar = false;
        } 

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Agregar el nuevo password
            $usuario->sincronizar($_POST);
            unset($usuario->password2);

            //Validar el password
            $alertas = $usuario->validarPassword();

            if (empty($alertas)) {
                //Hashear el password
                $usuario->hashPassword();

                //Eliminar el token
                $usuario->token = null;

                //Guardar el usuario BD
                $usuario->guardar();

                //Redireccionar
                header('Location: /');
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer el PassWord',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }

    public static function mensaje(Router $router)
    {
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta Creada Exitosamente'
        ]);
    }

    public static function confirmar(Router $router)
    {

        $token = s($_GET['token']) ?? null;

        if (!$token) {
            header('Location: /');
        }

        //Encontrar al usuario con el token

        $usuario = Usuario::where('token', $token);

        $alertas = [];

        if (empty($usuario)) {
            //No se encontro usuario con ese token
            Usuario::setAlerta('error', 'Token no valido');
            $alertas = Usuario::getAlertas();
        } else {
            //Encontramos un Usuario
            $usuario->confirmado = 1;
            $usuario->token = null;
            unset($usuario->password2);

            //Guardar en la base de datos
            $usuario->guardar();

            //Exito de confirmacion
            Usuario::setAlerta('exito', 'Cuenta comprobada correctamente');
            $alertas = Usuario::getAlertas();
        }

        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu cuenta UpTask',
            'alertas' => $alertas
        ]);
    }
}
