(function () {
  obtenerTarea();

  let tareas = [];
  let filtradas = [];

  //Boton para mostrar el modal
  const boton = document.querySelector("#agregar-tarea");
  boton.addEventListener("click", function () {
    mostraFormulario();
  });

  //Filtro de busqueda
  const filtros = document.querySelectorAll('#filtros input[type="radio"]');
  filtros.forEach((radio) => {
    radio.addEventListener("input", filtrarTareas);
  });

  function filtrarTareas(e) {
    const filtro = e.target.value;

    if (filtros !== "") {
      filtradas = tareas.filter((tarea) => tarea.estado === filtro);
    } else {
      filtradas = [];
    }
    mostrarTarea();
  }

  /**********************  API ****************************/
  async function obtenerTarea() {
    try {
      const Proyectourl = obtenerProyecto();
      const url = `/api/tareas?url=${Proyectourl}`;

      const respuesta = await fetch(url);
      const resultado = await respuesta.json();
      tareas = resultado.tareas;
      mostrarTarea();
    } catch (error) {
      console.log(error);
    }
  }
  /**********************  API ****************************/

  function mostrarTarea() {
    limpiarTareas();

    totalPendinte();
    totalCompletadas();

    const arrayTareas = filtradas.length ? filtradas : tareas;

    const contenedor = document.querySelector("#listado-tareas");

    if (arrayTareas.length === 0) {
      const contenedorTarea = document.createElement("LI");
      contenedorTarea.classList.add("no-tareas");
      contenedorTarea.textContent = "No Hay Tarea";
      contenedor.appendChild(contenedorTarea);
      return;
    }

    arrayTareas.forEach((tarea) => {
      const contenedorTarea = document.createElement("LI");
      contenedorTarea.dataset.tareaId = tarea.id;
      contenedorTarea.classList.add("tareas");

      const nombreTarea = document.createElement("P");
      nombreTarea.textContent = tarea.nombre;
      nombreTarea.ondblclick = function () {
        mostraFormulario(true, { ...tarea });
      };

      const opcionesDiv = document.createElement("DIV");
      opcionesDiv.classList.add("opciones");

      const btnEstadoTarea = document.createElement("BUTTON");
      opcionesDiv.classList.add("estado-tarea");

      btnEstadoTarea.classList.add(
        `${tarea.estado === "0" ? "pendiente" : "completa"}`
      );

      btnEstadoTarea.dataset.estadoTarea = tarea.estado;

      btnEstadoTarea.textContent =
        tarea.estado === "0" ? "Pendiente" : "Completa";

      btnEstadoTarea.ondblclick = function () {
        cambiarEstadoTarea({ ...tarea });
      };

      const btnEliminarTarea = document.createElement("BUTTON");
      btnEliminarTarea.classList.add("eliminar-tarea");
      btnEliminarTarea.dataset.idTarea = tarea.id;
      btnEliminarTarea.textContent = "Eliminar";
      btnEliminarTarea.ondblclick = function () {
        confirmarEliminarTarea({ ...tarea });
      };

      opcionesDiv.appendChild(btnEstadoTarea);
      opcionesDiv.appendChild(btnEliminarTarea);

      contenedorTarea.appendChild(nombreTarea);
      contenedorTarea.appendChild(opcionesDiv);

      const listadoTareas = document.querySelector("#listado-tareas");
      listadoTareas.appendChild(contenedorTarea);
    });
  }

  function totalPendinte() {
    const totalPendinte = tareas.filter((tarea) => tarea.estado === "0");
    const pendientesRadio = document.querySelector("#pendientes");

    if (totalPendinte.length === 0) {
      pendientesRadio.disabled = true;
    } else {
      pendientesRadio.disabled = false;
    }
  }

  function totalCompletadas(params) {
    const totalCompletadas = tareas.filter((tarea) => tarea.estado === "1");
    const completadaRadio = document.querySelector("#completadas");

    if (totalCompletadas.length === 0) {
      completadaRadio.disabled = true;
    } else {
      completadaRadio.disabled = false;
    }
  }

  function mostraFormulario(editar = false, tarea = {}) {
    const modal = document.createElement("DIV");
    const body = document.querySelector("BODY");
    body.classList.toggle("overflowy-h");

    modal.classList.add("modal");
    modal.innerHTML = `
        <form class="formulario nueva-tarea">
          <legend>${editar ? "Editar Tarea" : "Agrega una nueva tarea"}</legend>
          <div class="campo">
            <label>Tarea</label>
            <input
              type="text"
              name="tarea"
              placeholder="${
                editar ? "Edita la tarea" : "Agregar una nueva tarea"
              }"
              id="tarea"
              ${editar ? `value = ${tarea.nombre}` : ""}
            />
          </div>
  
          <div class="opciones">
            <input
              type="submit"
              class="submit-nueva-tarea"
              value="${editar ? "Actualizar Tarea" : "Agregar Tarea"}"
            />
            <button type="button" class="cerrar-modal">
              Cancelar
            </button>
          </div>
      </form>
      `;

    setTimeout(() => {
      const formulario = document.querySelector(".formulario");
      formulario.classList.add("animar");
    }, 0);

    modal.addEventListener("click", function (e) {
      e.preventDefault();

      if (e.target.classList.contains("cerrar-modal")) {
        const body = document.querySelector("BODY");
        body.classList.toggle("overflowy-h");

        const formulario = document.querySelector(".formulario");
        formulario.classList.add("cerrar");
        setTimeout(() => {
          modal.remove();
        }, 500);
      }

      if (e.target.classList.contains("submit-nueva-tarea")) {
        const nombreTarea = document.querySelector("#tarea").value.trim();

        if (nombreTarea === "") {
          //Mostrar una alerta de error
          mostrarAlerta(
            "El nombre de la tarea es obligatorio",
            "error",
            document.querySelector(".formulario legend")
          );
          return;
        }

        if (editar) {
          tarea.nombre = nombreTarea;
          actualizarTarea(tarea);
        } else {
          agregarTarea(nombreTarea);
        }
      }
    });

    //Consultar el servidor para agreagar una nueva tarea al proyecto
    /*********************      API    *****************************/
    async function agregarTarea(tarea) {
      //Construir la peticion
      const datos = new FormData();
      datos.append("nombre", tarea);
      datos.append("proyectoId", obtenerProyecto());

      try {
        const url = "http://localhost:5500/api/tareas";
        const respuesta = await fetch(url, {
          method: "POST",
          body: datos,
        });
        const resultado = await respuesta.json();

        //Agregar el objeto de tarea de en la global del objeto
        const tareaObj = {
          id: String(resultado.id),
          nombre: tarea,
          estado: "0",
          proyectoId: resultado.proyectoId,
        };

        tareas = [...tareas, tareaObj];
        mostrarTarea();

        mostrarAlerta(
          resultado.mensaje,
          resultado.tipo,
          document.querySelector(".formulario legend")
        );
      } catch (error) {
        console.log(error);
      }
    }
    /*********************      API    *****************************/

    document.querySelector(".dashboard").appendChild(modal);
  }

  //Mostrar un mensaje en la inteface
  function mostrarAlerta(MensajeTexto, tipo, referencia) {
    //Eliminar una alerta previa
    const alertaPrevia = document.querySelector(".alerta");
    if (alertaPrevia) {
      return;
    }

    //crear una alerta
    const alerta = document.createElement("DIV");
    alerta.classList.add("alerta", tipo);
    alerta.textContent = MensajeTexto;

    //Tomar el elemento del siguiente hijo proximo al primero
    lugar = referencia.nextElementSibling;

    referencia.parentElement.insertBefore(alerta, lugar);

    //eliminar tarea
    setTimeout(() => {
      alerta.remove();
    }, 3000);
  }

  function cambiarEstadoTarea(tarea) {
    const nuevoEstado = tarea.estado === "1" ? "0" : "1";
    tarea.estado = nuevoEstado;
    actualizarTarea(tarea);
  }

  async function actualizarTarea(tarea) {
    const { estado, id, nombre } = tarea;

    const datos = new FormData();
    datos.append("id", id);
    datos.append("nombre", nombre);
    datos.append("estado", estado);
    datos.append("proyectoId", obtenerProyecto());

    try {
      const url = "http://localhost:5500/api/tareas/actualizar";

      const respuesta = await fetch(url, {
        method: "POST",
        body: datos,
      });

      const resultado = await respuesta.json();

      if (resultado.tipo === "exito") {
        Swal.fire("Actulizado Correctamente", "Tarea actualizada", "success");

        const modal = document.querySelector(".modal");
        if (modal) {
          modal.remove();
        }

        tareas = tareas.map((tareaMemoria) => {
          if (tareaMemoria.id === id) {
            tareaMemoria.estado = estado;
            tareaMemoria.nombre = nombre;
          }

          return tareaMemoria;
        });

        mostrarTarea();
      }
    } catch (error) {
      console.log(error);
    }
  }

  function confirmarEliminarTarea(tarea) {
    Swal.fire({
      title: "Eliminar Tarea?",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No",
    }).then((result) => {
      if (result.isConfirmed) {
        eliminarTarea(tarea);
      }
    });
  }

  /*********************      API    *****************************/
  async function eliminarTarea(tarea) {
    const datos = new FormData();

    const { estado, id, nombre } = tarea;

    datos.append("id", id);
    datos.append("nombre", nombre);
    datos.append("estado", estado);
    datos.append("proyectoId", obtenerProyecto());
    try {
      const url = "http://localhost:5500/api/tareas/eliminar";
      const respuesta = await fetch(url, {
        method: "POST",
        body: datos,
      });

      const resultado = await respuesta.json();
      if (resultado.resultado === "exito") {
        mostrarAlerta(
          resultado.mensaje,
          resultado.resultado,
          document.querySelector(".contenedor-nueva-tarea")
        );

        tareas = tareas.filter((tareaMemoria) => tareaMemoria.id !== tarea.id);
        mostrarTarea();
      }
    } catch (error) {}
  }
  /*********************      API    *****************************/

  function obtenerProyecto() {
    //Obtener la url con JS
    const proyectoParams = new URLSearchParams(window.location.search);
    const proyecto = Object.fromEntries(proyectoParams.entries());
    return proyecto.url;
  }

  function limpiarTareas() {
    const listadoTareas = document.querySelector("#listado-tareas");

    while (listadoTareas.firstChild) {
      listadoTareas.removeChild(listadoTareas.firstChild);
    }
  }
})();
