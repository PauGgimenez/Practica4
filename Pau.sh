#!/bin/bash

# Función para mostrar la fecha y la hora
mostrar_fecha() {
    echo "Fecha y hora actual: $(date)"
}

# Función para listar archivos en el directorio actual
listar_archivos() {
    echo "Archivos en el directorio actual:"
    ls -lah
}

# Función para mostrar el uso del disco
uso_disco() {
    echo "Uso del disco:"
    df -h
}

# Función para hacer ping a Google
ping_google() {
    echo "Haciendo ping a Google..."
    ping -c 4 google.com
}

# Función para generar una contraseña aleatoria
generar_password() {
    echo "Contraseña aleatoria: $(openssl rand -base64 12)"
}

# Función para jugar a adivinar un número
jugar_adivinanza() {
    numero=$((RANDOM % 10 + 1))
    intentos=0
    echo "Adivina un número entre 1 y 10"
    while true; do
        read -p "Tu intento: " intento
        ((intentos++))
        if [[ "$intento" -eq "$numero" ]]; then
            echo "¡Correcto! Lo lograste en $intentos intentos."
            break
        elif [[ "$intento" -lt "$numero" ]]; then
            echo "Más alto..."
        else
            echo "Más bajo..."
        fi
    done
}

# Menú interactivo
while true; do
    echo "\n--- MENÚ ---"
    echo "1) Mostrar fecha y hora"
    echo "2) Listar archivos"
    echo "3) Ver uso del disco"
    echo "4) Hacer ping a Google"
    echo "5) Generar contraseña"
    echo "6) Jugar a adivinar un número"
    echo "7) Salir"
    read -p "Elige una opción: " opcion

    case $opcion in
        1) mostrar_fecha ;;
        2) listar_archivos ;;
        3) uso_disco ;;
        4) ping_google ;;
        5) generar_password ;;
        6) jugar_adivinanza ;;
        7) echo "¡Adiós!"; exit 0 ;;
        *) echo "Opción no válida" ;;
    esac

done