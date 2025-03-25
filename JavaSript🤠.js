// Archivo: todoList.js

class TodoList {
    constructor() {
      this.tareas = [];
    }
  
    // Agregar una tarea
    agregarTarea(nombre) {
      const nuevaTarea = {
        id: this.tareas.length + 1,
        nombre,
        completada: false,
      };
      this.tareas.push(nuevaTarea);
      console.log(`Tarea '${nombre}' agregada.`);
    }
  
    // Eliminar una tarea por ID
    eliminarTarea(id) {
      const tareaIndex = this.tareas.findIndex(tarea => tarea.id === id);
      if (tareaIndex !== -1) {
        const tareaEliminada = this.tareas.splice(tareaIndex, 1);
        console.log(`Tarea '${tareaEliminada[0].nombre}' eliminada.`);
      } else {
        console.log(`Tarea con ID ${id} no encontrada.`);
      }
    }
  
    // Marcar una tarea como completada
    completarTarea(id) {
      const tarea = this.tareas.find(tarea => tarea.id === id);
      if (tarea) {
        tarea.completada = true;
        console.log(`Tarea '${tarea.nombre}' completada.`);
      } else {
        console.log(`Tarea con ID ${id} no encontrada.`);
      }
    }
  
    // Mostrar todas las tareas
    mostrarTareas() {
      if (this.tareas.length === 0) {
        console.log("No hay tareas pendientes.");
        return;
      }
      console.log("Lista de Tareas:");
      this.tareas.forEach(tarea => {
        console.log(`${tarea.id}. ${tarea.nombre} [${tarea.completada ? "Completada" : "Pendiente"}]`);
      });
    }
  
    // Filtrar tareas completadas
    mostrarTareasCompletadas() {
      const completadas = this.tareas.filter(tarea => tarea.completada);
      if (completadas.length === 0) {
        console.log("No hay tareas completadas.");
        return;
      }
      console.log("Tareas Completadas:");
      completadas.forEach(tarea => {
        console.log(`${tarea.id}. ${tarea.nombre}`);
      });
    }
  
    // Filtrar tareas pendientes
    mostrarTareasPendientes() {
      const pendientes = this.tareas.filter(tarea => !tarea.completada);
      if (pendientes.length === 0) {
        console.log("No hay tareas pendientes.");
        return;
      }
      console.log("Tareas Pendientes:");
      pendientes.forEach(tarea => {
        console.log(`${tarea.id}. ${tarea.nombre}`);
      });
    }
  }
  
  // Función para interactuar con el TodoList
  function main() {
    const miLista = new TodoList();
  
    miLista.agregarTarea("Comprar leche");
    miLista.agregarTarea("Estudiar JavaScript");
    miLista.agregarTarea("Ir al gimnasio");
    miLista.agregarTarea("Hacer la compra");
  
    miLista.mostrarTareas();
  
    miLista.completarTarea(2); // Completa la tarea 2
    miLista.mostrarTareasCompletadas();
  
    miLista.eliminarTarea(3); // Elimina la tarea 3
    miLista.mostrarTareas();
  
    miLista.mostrarTareasPendientes();
    miLista.mostrarTareasCompletadas();
  }
  
  // Ejecutar la función principal
  main();
  
  // Ejemplo de manipulación de arrays
  const numeros = [1, 2, 3, 4, 5];
  const dobles = numeros.map(num => num * 2);
  console.log("Números originales:", numeros);
  console.log("Números doblados:", dobles);
  
  // Ordenar tareas
  const tareasOrdenadas = [
    { id: 1, nombre: "Comprar leche", completada: false },
    { id: 2, nombre: "Estudiar JavaScript", completada: true },
    { id: 3, nombre: "Ir al gimnasio", completada: false },
    { id: 4, nombre: "Hacer la compra", completada: true },
  ];
  
  // Ordenar tareas por nombre
  const ordenadasPorNombre = tareasOrdenadas.sort((a, b) => a.nombre.localeCompare(b.nombre));
  console.log("Tareas ordenadas por nombre:");
  console.log(ordenadasPorNombre);
  
  // Ordenar tareas por estado (completada o pendiente)
  const ordenadasPorEstado = tareasOrdenadas.sort((a, b) => a.completada - b.completada);
  console.log("Tareas ordenadas por estado:");
  console.log(ordenadasPorEstado);
  
  // Ejemplo de objetos con métodos
  const usuario = {
    nombre: "Juan",
    edad: 25,
    saludar: function() {
      console.log(`Hola, soy ${this.nombre} y tengo ${this.edad} años.`);
    },
  };
  
  usuario.saludar();
  
  // Uso de async/await con Promesas
  function obtenerDatos() {
    return new Promise((resolve, reject) => {
      setTimeout(() => {
        resolve("Datos obtenidos con éxito");
      }, 2000);
    });
  }
  
  async function ejecutar() {
    console.log("Esperando datos...");
    const resultado = await obtenerDatos();
    console.log(resultado);
  }
  
  ejecutar();
  
  // Eventos DOM
  document.addEventListener("DOMContentLoaded", () => {
    const boton = document.createElement("button");
    boton.innerHTML = "Mostrar mensaje";
    document.body.appendChild(boton);
  
    boton.addEventListener("click", () => {
      alert("¡Hola, mundo!");
    });
  });
  