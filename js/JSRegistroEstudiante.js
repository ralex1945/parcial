document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById('formRegistro');
    const campoNombre = document.getElementById('nombre');
    const campoApellido = document.getElementById('apellido');
    const campoCarnet = document.getElementById('carnet');
    const mensaje = document.getElementById('mensaje');
    const contenedorClave = document.getElementById('contenedorClave');
    const claveGenerada = document.getElementById('claveGenerada');
    const botonGuardar = document.getElementById('btnGuardar');

    const soloLetras = /^[\p{L}\p{M}]+(?: +[\p{L}\p{M}]+)*$/u;
    const validarNombre = (campo) => {
        const valor = campo.value.trim();
        campo.setCustomValidity(valor && !soloLetras.test(valor)
            ? 'Escribe solo letras. Puedes separar nombres con espacios.'
            : '');
    };

    [campoNombre, campoApellido].forEach((campo) => {
        campo.addEventListener('input', () => {
            validarNombre(campo);
            contenedorClave.hidden = true;
            claveGenerada.value = '';
            mensaje.hidden = true;
        });
        campo.addEventListener('blur', () => validarNombre(campo));
    });

    const mostrarMensaje = (texto, tipo) => {
        mensaje.textContent = texto;
        mensaje.className = `alert alert-${tipo} py-1 px-2 small mb-2`;
        mensaje.hidden = false;
    };

    campoCarnet.addEventListener('input', () => {
        campoCarnet.value = campoCarnet.value.toUpperCase();
        contenedorClave.hidden = true;
        claveGenerada.value = '';
        mensaje.hidden = true;
    });

    document.getElementById('usuario').addEventListener('input', () => {
        contenedorClave.hidden = true;
        claveGenerada.value = '';
        mensaje.hidden = true;
    });

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        validarNombre(campoNombre);
        validarNombre(campoApellido);
        if (!formulario.reportValidity()) {
            return;
        }

        mensaje.hidden = true;
        botonGuardar.disabled = true;
        botonGuardar.textContent = 'Guardando...';

        try {
            const respuesta = await fetch('DAORegistroEstudiante.php', {
                method: 'POST',
                body: new FormData(formulario),
                headers: { Accept: 'application/json' },
            });
            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.ok) {
                mostrarMensaje(datos.mensaje || 'No se pudo completar el registro.', 'danger');
                return;
            }

            formulario.reset();
            claveGenerada.value = datos.clave;
            contenedorClave.hidden = false;
            mostrarMensaje('Estudiante registrado.', 'success');
        } catch (error) {
            mostrarMensaje('No hubo conexión con el servidor. Revisa que Apache y MySQL estén iniciados.', 'danger');
        } finally {
            botonGuardar.disabled = false;
            botonGuardar.textContent = 'Registrar estudiante';
        }
    });

});
