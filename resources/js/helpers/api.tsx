
import axios from "axios";
import { deleteCookie, getCookie } from "cookies-next";

async function fetchAPI(url: string, params: any = {}, method: string = 'GET', code: boolean = false, isFile: boolean = false) {
    const token = getCookie("token");

    const headers: any = { 
        "Authorization": `Bearer ${token}`
    };

    let data;
    if (params instanceof FormData) {
        // Si es FormData, no agregamos Content-Type
        data = params;
    } else {
        headers["Content-Type"] = "application/json";
        data = params;
    }

    try {
        const res = await axios({
            method,
            url,
            headers,
            responseType: isFile ? 'blob' : 'json', // Usa 'blob' solo si estás descargando un archivo
            ...(method !== "GET" && { data })
        });

        if (code) {
            res.data.code = res.status;
        }

        return res.data;

    } catch (error: any) {
        if (error.response) {
            const { status, data } = error.response;            
            if (status === 401) {
                deleteCookie("token");
                deleteCookie("user");
                deleteCookie("isAuth");

                if (typeof window !== "undefined") {
                    window.location.href = "/auth/login?unauthorized=true";
                }
                return;
            }else if (status === 429) {
                console.log("Demasiadas solicitudes. Intente nuevamente en unos minutos.");          
                

            }
            const errorMsg = data.error || data.message || "Error desconocido";
            throw new Error(errorMsg);
        } else {
            throw new Error("Error de red o servidor no disponible");            
        }
    }
}

export default fetchAPI;