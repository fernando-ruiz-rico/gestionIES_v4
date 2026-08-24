// API de profesores para comunicación con el backend

const ProfesoresAPI = {
    // Listar profesores de un departamento
    async listar(idDepartamento) {
        try {
            const response = await fetch(`../backend/api/profesores/listar.php?idDepartamento=${idDepartamento}`, {
                method: 'GET',
                credentials: 'include'
            });

            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error al cargar profesores' };
            }

            return { success: true, data: data.data };
        } catch (error) {
            console.error('Error en listar profesores:', error);
            return { success: false, error: 'Error de conexión con el servidor' };
        }
    },

    // Obtener un profesor por ID
    async obtener(id) {
        try {
            const response = await fetch(`../backend/api/profesores/obtener.php?id=${id}`, {
                method: 'GET',
                credentials: 'include'
            });

            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error al cargar profesor' };
            }

            return { success: true, data: data.data };
        } catch (error) {
            console.error('Error en obtener profesor:', error);
            return { success: false, error: 'Error de conexión con el servidor' };
        }
    },

    // Guardar profesor (crear o actualizar)
    async guardar(formData) {
        try {
            const response = await fetch('../backend/api/profesores/guardar.php', {
                method: 'POST',
                credentials: 'include',
                body: formData
            });

            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error al guardar profesor' };
            }

            return { success: true, data: data.data };
        } catch (error) {
            console.error('Error en guardar profesor:', error);
            return { success: false, error: 'Error de conexión con el servidor' };
        }
    },

    // Eliminar profesor
    async eliminar(id) {
        try {
            const formData = new FormData();
            formData.append('id', id);

            const response = await fetch('../backend/api/profesores/eliminar.php', {
                method: 'POST',
                credentials: 'include',
                body: formData
            });

            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error al eliminar profesor' };
            }

            return { success: true, data: data.data };
        } catch (error) {
            console.error('Error en eliminar profesor:', error);
            return { success: false, error: 'Error de conexión con el servidor' };
        }
    },

    // Actualizar jefe de departamento
    async actualizarJefe(idProfesor, idDepartamento) {
        try {
            const formData = new FormData();
            formData.append('idProfesor', idProfesor);
            formData.append('idDepartamento', idDepartamento);

            const response = await fetch('../backend/api/profesores/actualizar_jefe.php', {
                method: 'POST',
                credentials: 'include',
                body: formData
            });

            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error al actualizar jefe de departamento' };
            }

            return { success: true, data: data.data };
        } catch (error) {
            console.error('Error en actualizar jefe:', error);
            return { success: false, error: 'Error de conexión con el servidor' };
        }
    },

    // Activar/desactivar profesor
    async actualizarActivo(idProfesor) {
        try {
            const formData = new FormData();
            formData.append('idProfesor', idProfesor);

            const response = await fetch('../backend/api/profesores/actualizar_activo.php', {
                method: 'POST',
                credentials: 'include',
                body: formData
            });

            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error al actualizar estado del profesor' };
            }

            return { success: true, data: data.data };
        } catch (error) {
            console.error('Error en actualizar activo:', error);
            return { success: false, error: 'Error de conexión con el servidor' };
        }
    },

    // Ordenar profesores
    async ordenar(orden) {
        try {
            const formData = new FormData();
            formData.append('orden', orden);

            const response = await fetch('../backend/api/profesores/ordenar.php', {
                method: 'POST',
                credentials: 'include',
                body: formData
            });

            const data = await response.json();
            if (!data.success) {
                return { success: false, error: data.error || 'Error al ordenar profesores' };
            }

            return { success: true, data: data.data };
        } catch (error) {
            console.error('Error en ordenar profesores:', error);
            return { success: false, error: 'Error de conexión con el servidor' };
        }
    }
};
