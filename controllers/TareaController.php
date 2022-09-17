<?php

namespace Controllers;

use Model\Proyecto;
use Model\Tarea;



class TareaController
{

    public static function index()
    {

        $proyectoUrl = $_GET['url'];

        if (!$proyectoUrl) {
            header('Location: /dashboard');
        }

        $proyecto = Proyecto::where('url', $proyectoUrl);

        isAuth();
        if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
            header('Location: /404');
        }

        $tareas = Tarea::belongsTo('proyectoId', $proyecto->id);

        echo json_encode(["tareas" => $tareas]);
    }

    public static function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            isAuth();
            $proyecto = Proyecto::where('url', $_POST['proyectoId']);

            if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un Error al agregar la tarea'
                ];
                echo json_encode($respuesta);
                return;
            }



            //Todo bien, instanciar y crear la tarea
            $propietario = Proyecto::where('url', $_POST['proyectoId']);
            $valores = [
                'nombre' => $_POST['nombre'],
                'proyectoId' => $propietario->id
            ];

            $tarea = new Tarea($valores);
            $resultado = $tarea->guardar();

            if ($resultado) {
                $respuesta = [
                    'tipo' => 'exito',
                    'id' => $resultado['id'],
                    'mensaje' => 'Tarea agregada correctamente',
                    'proyectoId' => $proyecto->id
                ];
                echo json_encode($respuesta);
            }
        }
    }

    public static function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            isAuth();

            $proyecto = Proyecto::where('url', $_POST['proyectoId']);

            if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un Error al agregar la tarea'
                ];
                echo json_encode($respuesta);
                return;
            }

            //Todo bien, instanciar y actualizar la tarea

            $tarea = new Tarea($_POST);
            $tarea->proyectoId = $proyecto->id;

            $resultado = $tarea->guardar();

            if ($resultado) {
                $respuesta = [
                    'tipo' => 'exito',
                    'id' => $tarea->id,
                    'proyectoId' => $proyecto->id
                ];
                echo json_encode($respuesta);
            }
        }
    }

    public static function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            isAuth();

            $proyecto = Proyecto::where('url', $_POST['proyectoId']);

            if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un Error al agregar la tarea'
                ];
                echo json_encode($respuesta);
                return;
            }

            //Todo bien, instanciar y eliminar la tarea

            $tarea = new Tarea($_POST);

            $resultado = $tarea->eliminar();

            if ($resultado) {
                $respuesta = [
                    'resultado' => 'exito',
                    'mensaje' => 'Eliminado correctamente',
                ];
                echo json_encode($respuesta);
            }
        }
    }
}
