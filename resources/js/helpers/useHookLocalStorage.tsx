'use client'
import { useState } from "react";

/**
 * Hook useLocalStorage
 * 
 * Este hook permite a los componentes de React gestionar y persistir datos en el almacenamiento local del navegador.
 * Los datos persisten incluso después de recargar la página, lo que facilita mantener un estado entre sesiones.
 * 
 * @param {string} key - La clave única que se utiliza para identificar el dato almacenado.
 * @param {*} initialValue - El valor inicial que se utilizará si no se encuentra ningún dato almacenado bajo la clave.
 * @returns {Array} - Un arreglo con dos elementos: el valor almacenado y una función para actualizarlo.
 */
export function useHookLocalStorage(key:any, initialValue:any) {
	// State para almacenar nuestro valor
	// Se pasa una función de estado inicial a useState para que la lógica solo se ejecute una vez
	const [storedValue, setStoredValue] = useState(() => {
		try {
			// Obtener del almacenamiento local por clave
			const item = window.localStorage.getItem(key);
			// Analizar el JSON almacenado o devolver initialValue si no hay ninguno
			return item ? JSON.parse(item) : initialValue;
		} catch (error) {
			// Si ocurre un error, también se devuelve initialValue
			return initialValue;
		}
	});

	// Devolver una versión envuelta de la función setter de useState que ...
	// ... persiste el nuevo valor en el almacenamiento local.
	const setValue = (value:any) => {
		try {
			// Permitir que el valor sea una función para tener la misma API que useState
			const valueToStore =
				value instanceof Function ? value(storedValue) : value;
			// Guardar en el estado
			setStoredValue(valueToStore);
			// Guardar en el almacenamiento local
			window.localStorage.setItem(key, JSON.stringify(valueToStore));
		} catch (error) {
			// Una implementación más avanzada manejaría el caso de error de manera adecuada
			console.log(error);
		}
	};

	return [storedValue, setValue];
}