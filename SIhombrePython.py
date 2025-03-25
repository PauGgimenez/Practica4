# Archivo: todo_list.py

# Clase Tarea para gestionar las tareas individuales
class Tarea:
    def __init__(self, id, nombre, completada=False):
        self.id = id
        self.nombre = nombre
        self.completada = completada

    def __str__(self):
        return f"{self.id}. {self.nombre} [{'Completada' if self.completada else 'Pendiente'}]"


# Clase TodoList para gestionar todas las tareas
class TodoList:
    def __init__(self):
        self.tareas = []

    def agregar_tarea(self, nombre):
        id = len(self.tareas) + 1
        nueva_tarea = Tarea(id, nombre)
        self.tareas.append(nueva_tarea)
        print(f"Tarea '{nombre}' agregada.")

    def eliminar_tarea(self, id):
        tarea = next((t for t in self.tareas if t.id == id), None)
        if tarea:
            self.tareas.remove(tarea)
            print(f"Tarea '{tarea.nombre}' eliminada.")
        else:
            print(f"Tarea con ID {id} no encontrada.")

    def completar_tarea(self, id):
        tarea = next((t for t in self.tareas if t.id == id), None)
        if tarea:
            tarea.completada = True
            print(f"Tarea '{tarea.nombre}' completada.")
        else:
            print(f"Tarea con ID {id} no encontrada.")

    def mostrar_tareas(self):
        if not self.tareas:
            print("No hay tareas pendientes.")
            return
        print("Lista de Tareas:")
        for tarea in self.tareas:
            print(tarea)

    def mostrar_tareas_completadas(self):
        completadas = [t for t in self.tareas if t.completada]
        if not completadas:
            print("No hay tareas completadas.")
            return
        print("Tareas Completadas:")
        for tarea in completadas:
            print(tarea)

    def mostrar_tareas_pendientes(self):
        pendientes = [t for t in self.tareas if not t.completada]
        if not pendientes:
            print("No hay tareas pendientes.")
            return
        print("Tareas Pendientes:")
        for tarea in pendientes:
            print(tarea)

    def ordenar_tareas_por_nombre(self):
        self.tareas.sort(key=lambda t: t.nombre)
        print("Tareas ordenadas por nombre:")
        for tarea in self.tareas:
            print(tarea)

    def ordenar_tareas_por_estado(self):
        self.tareas.sort(key=lambda t: t.completada)
        print("Tareas ordenadas por estado (Completadas primero):")
        for tarea in self.tareas:
            print(tarea)


# Función principal para interactuar con la TodoList
def main():
    mi_lista = TodoList()

    mi_lista.agregar_tarea("Comprar leche")
    mi_lista.agregar_tarea("Estudiar Python")
    mi_lista.agregar_tarea("Ir al gimnasio")
    mi_lista.agregar_tarea("Hacer la compra")

    mi_lista.mostrar_tareas()

    mi_lista.completar_tarea(2)  # Completa la tarea 2
    mi_lista.mostrar_tareas_completadas()

    mi_lista.eliminar_tarea(3)  # Elimina la tarea 3
    mi_lista.mostrar_tareas()

    mi_lista.mostrar_tareas_pendientes()
    mi_lista.mostrar_tareas_completadas()

    mi_lista.ordenar_tareas_por_nombre()
    mi_lista.ordenar_tareas_por_estado()


# Ejecutar la función principal
if __name__ == "__main__":
    main()

# Ejemplo de manipulación de listas
numeros = [1, 2, 3, 4, 5]
dobles = [num * 2 for num in numeros]
print("Números originales:", numeros)
print("Números doblados:", dobles)

# Ordenación de tareas por nombre
tareas = [
    Tarea(1, "Comprar leche"),
    Tarea(2, "Estudiar Python"),
    Tarea(3, "Ir al gimnasio"),
    Tarea(4, "Hacer la compra")
]
tareas.sort(key=lambda t: t.nombre)
print("Tareas ordenadas por nombre:")
for tarea in tareas:
    print(tarea)
