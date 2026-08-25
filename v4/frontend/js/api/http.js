// Helper HTTP compartido de los clientes de API de js/api/.
//
// Todos los endpoints devuelven el mismo sobre de JSON:
//     éxito   -> { "success": true,  "data": ..., "message": "..." }
//     fallo   -> { "success": false, "error": "..." }
//
// En la app conviven dos estilos de consumo, y Http cubre ambos:
//
//   "envelope"  — siempre resuelve; el llamador comprueba result.success
//                 (o recibe el JSON crudo, como con `await res.json()`).
//   "throw"     — lanza Error si !success; resuelve con data.data.
//
//   Http.get(url) / Http.post(url, cuerpo) / Http.del(url)
//       Siempre resuelven con el sobre que envía el servidor. Ante fallo de
//       red o respuesta no-JSON: { success: false, error: 'Error de
//       conexión con el servidor' }. El `cuerpo` de post() puede ser un
//       objeto plano (se envía como JSON) o FormData (multipart).
//
//   Http.getOk(url, fallback) / Http.postOk(url, cuerpo, fallback) /
//   Http.delOk(url, fallback)
//       Igual que los anteriores, pero lanzan Error(data.error || fallback)
//       si !success y, en su caso, resuelven con data.data.
//
//   `credentials` es 'same-origin' por defecto; los módulos que lo usan
//   lo pasan como segundo argumento ('include').
const Http = {
    /** GET — siempre resuelve con el sobre (ver cabecera del módulo). */
    get(url, credentials = 'same-origin') {
        return Http._request(url, { method: 'GET', credentials });
    },

    /** POST — cuerpo: objeto (JSON) o FormData. Siempre resuelve con el sobre. */
    post(url, body, credentials = 'same-origin') {
        return Http._request(url, { method: 'POST', body, credentials });
    },

    /** DELETE — siempre resuelve con el sobre. */
    del(url, credentials = 'same-origin') {
        return Http._request(url, { method: 'DELETE', credentials });
    },

    /** GET — lanza Error si !success; resuelve con data.data. */
    async getOk(url, fallback = 'Error de conexión', credentials = 'same-origin') {
        const data = await Http.get(url, credentials);
        if (!data.success) {
            throw new Error(data.error || fallback);
        }
        return data.data;
    },

    /** POST — lanza Error si !success; resuelve con data.data. */
    async postOk(url, body, fallback = 'Error de conexión', credentials = 'same-origin') {
        const data = await Http.post(url, body, credentials);
        if (!data.success) {
            throw new Error(data.error || fallback);
        }
        return data.data;
    },

    /** DELETE — lanza Error si !success; resuelve con data.data. */
    async delOk(url, fallback = 'Error de conexión', credentials = 'same-origin') {
        const data = await Http.del(url, credentials);
        if (!data.success) {
            throw new Error(data.error || fallback);
        }
        return data.data;
    },

    // --- Interno: el fetch lo hace una sola vez para todo el frontend ---
    async _request(url, { method = 'GET', body = null, credentials = 'same-origin' } = {}) {
        const init = {
            method,
            credentials,
            headers: { 'Accept': 'application/json' }
        };
        if (body !== null && body !== undefined) {
            if (body instanceof FormData) {
                // El propio navegador pone el Content-Type y el boundary
                init.body = body;
            } else {
                init.headers['Content-Type'] = 'application/json';
                init.body = JSON.stringify(body);
            }
        }
        try {
            const response = await fetch(url, init);
            return await response.json();
        } catch (error) {
            // Fallo de red o respuesta que no es JSON válido.
            return { success: false, error: 'Error de conexión con el servidor' };
        }
    }
};
