<?php
require_once __DIR__ . '/EventoDAO.php';

class EventoController
{
    private EventoDAO $dao;

    public function __construct(PDO $pdo)
    {
        $this->dao = new EventoDAO($pdo);
    }

    public function validar(array $input, ?int $id = null): array
    {
        $codigo = $id !== null
            ? trim($input['codigo'] ?? '')
            : $this->dao->generarCodigo();

        $datos = [
            'codigo' => $codigo,
            'nombre' => trim($input['nombre'] ?? ''),
            'descripcion' => trim($input['descripcion'] ?? ''),
            'lugar' => trim($input['lugar'] ?? ''),
            'fecha' => trim($input['fecha'] ?? ''),
            'hora' => trim($input['hora'] ?? ''),
            'cupo_disponible' => trim((string)($input['cupo_disponible'] ?? '')),
        ];

        $errores = [];

        if ($id !== null && ($datos['codigo'] === '' || mb_strlen($datos['codigo']) > 15)) {
            $errores[] = 'El código no es válido.';
        }

        if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 100) {
            $errores[] = 'El nombre es obligatorio (máximo 100 caracteres).';
        }

        if ($datos['lugar'] === '' || mb_strlen($datos['lugar']) > 100) {
            $errores[] = 'El lugar es obligatorio (máximo 100 caracteres).';
        }

        $fechaObj = DateTime::createFromFormat('Y-m-d', $datos['fecha']);
        if (!$fechaObj || $fechaObj->format('Y-m-d') !== $datos['fecha']) {
            $errores[] = 'La fecha no es válida.';
        }

        $horaObj = DateTime::createFromFormat('H:i', substr($datos['hora'], 0, 5));
        if (!$horaObj) {
            $errores[] = 'La hora no es válida.';
        } else {
            $datos['hora'] = $horaObj->format('H:i:s');
        }

        if ($datos['cupo_disponible'] === '' || !ctype_digit($datos['cupo_disponible'])) {
            $errores[] = 'El cupo disponible debe ser un número entero mayor o igual a 0.';
        } else {
            $datos['cupo_disponible'] = (int)$datos['cupo_disponible'];
        }

        return [$errores, $datos];
    }

    public function procesar(): array
    {
        $mensaje = '';
        $errores = [];
        $eventoEditar = null;
        $form = [
            'id_evento' => '',
            'codigo' => '',
            'nombre' => '',
            'descripcion' => '',
            'lugar' => '',
            'fecha' => '',
            'hora' => '',
            'cupo_disponible' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? 'guardar';

            if ($accion === 'eliminar') {
                $id = (int)($_POST['id_evento'] ?? 0);
                if ($id > 0) {
                    try {
                        $this->dao->eliminar($id);
                        $mensaje = 'Evento eliminado.';
                    } catch (PDOException $e) {
                        $errores[] = 'No se puede eliminar el evento porque tiene inscripciones asociadas.';
                    }
                }
            } else {
                $id = (int)($_POST['id_evento'] ?? 0);
                $idEditar = $id > 0 ? $id : null;
                [$errores, $datos] = $this->validar($_POST, $idEditar);

                if (empty($errores)) {
                    try {
                        if ($idEditar !== null) {
                            $this->dao->actualizar($idEditar, $datos);
                            $mensaje = 'Evento actualizado.';
                        } else {
                            $this->dao->crear($datos);
                            $mensaje = 'Evento registrado.';
                        }
                    } catch (PDOException $e) {
                        $errores[] = 'Error al guardar en la base de datos.';
                    }
                } else {
                    $form = array_merge($form, $datos, [
                        'id_evento' => $idEditar ?? '',
                        'hora' => substr((string)($_POST['hora'] ?? ''), 0, 5),
                    ]);
                }
            }
        } elseif (isset($_GET['editar'])) {
            $id = (int)$_GET['editar'];
            $eventoEditar = $this->dao->obtenerPorId($id);
            if ($eventoEditar) {
                $form = $eventoEditar;
                $form['hora'] = substr($form['hora'], 0, 5);
            }
        }

        return [
            'mensaje' => $mensaje,
            'errores' => $errores,
            'form' => $form,
            'siguienteCodigo' => $this->dao->generarCodigo(),
            'eventos' => $this->dao->listar(),
        ];
    }
}
