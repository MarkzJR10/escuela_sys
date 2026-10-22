# Guía de Llenado de Calificaciones: Perfiles Maestro y Administrador

Este documento describe el flujo operativo, las reglas de negocio y las directrices técnicas para la captura, edición y control de calificaciones en el sistema escolar, considerando las funciones específicas de los perfiles **Maestro / Profesor** y **Administrador**.

---

## 1. Arquitectura y Reglas Generales del Módulo

* **Escala de Calificación:** Escala numérica continua de **0.0 a 10.0** (permite decimales con paso de `0.1`, por ejemplo: `7.5`, `8.0`, `9.8`, `10.0`).
* **Criterio de Aprobación:** Calificación mínima aprobatoria: **6.0**.
* **Estructura Académica:** Evaluaciones divididas en **3 Trimestres** (Trimestre 1, Trimestre 2, Trimestre 3).
* **Impacto en Cascada:**
  * Al registrarse o actualizarse una calificación, se actualiza automáticamente el promedio del trimestre, el promedio anual por materia y el promedio general del estudiante.
  * Los resultados alimentan directamente las **Boletas Trimestrales/Anuales**, el **Kardex Académico**, el **Cuadro de Honor Institucional** y la visualización en el **Portal de Padres**.

---

## 2. Procedimiento para el Perfil Administrador

El perfil **Administrador** funge como autoridad rectora de las políticas y tiempos de captura escolar.

### A. Apertura y Cierre de Periodos Trimestrales
1. Ingrese al menú lateral: **Periodos** (`/periodos`).
2. Localice las tarjetas correspondientes a:
   * **Trimestre 1**
   * **Trimestre 2**
   * **Trimestre 3**
3. Active o desactive el interruptor (*switch*):
   * **Abierto (Verde):** Permite a los maestros capturar y editar calificaciones de dicho trimestre.
   * **Cerrado (Rojo):** Bloquea los campos de captura para los maestros, evitando modificaciones posteriores a la fecha límite oficial.

### B. Captura con Permiso Especial (Bypass Administrativo)
* El administrador tiene facultad para registrar o modificar calificaciones en **cualquier momento**, inclusive si el periodo trimestral se encuentra **Cerrado**.
* Útil para: actas extemporáneas, resoluciones de comité académico o corrección de incidencias reportadas fuera de tiempo.

### C. Configuración y Emisión de Documentos
* **Asignación Docente:** Mantiene al día la asignación de materias y grupos a cada profesor en `/maestro_materia`.
* **Emisión de Boletas y Kardex:** Emisión de boletas oficiales en PDF y Kardex global de trayectoria estudiantil en `/boletas`.
* **Control de Visibilidad a Padres:** En `/configuraciones/visibilidad-portal-padres`, define si las calificaciones y boletas ya están liberadas para consulta pública de tutores.

---

## 3. Procedimiento para el Perfil Maestro / Profesor

El perfil **Maestro** es el encargado de la evaluación formativa y el vaciado periódico de notas.

### A. Verificación Previa
* Verifique que el trimestre que desea registrar se encuentre **Abierto**. Si el periodo está cerrado, las casillas aparecerán atenuadas y deshabilitadas con el aviso `Periodo_Cerrado`. En ese caso, deberá solicitar la apertura temporal a la Dirección/Administración.

### B. Flujo de Captura Masiva por Grupo y Materia
1. Diríjase a **Calificaciones > Captura de Calificaciones** (`/calificaciones/captura`).
2. Seleccione los filtros de búsqueda en la cabecera:
   * **Grado y Grupo:** (Ejemplo: *1° A*, *2° B*).
   * **Materia:** (Ejemplo: *Matemáticas*, *Español*).
   * Haga clic en **Cargar Alumnos** (o espere la recarga automática).
3. Se desplegará la nómina de estudiantes matriculados en dicho grupo con las columnas:
   * **Alumno:** Nombre, apellidos y matrícula.
   * **T1:** Nota del Trimestre 1.
   * **T2:** Nota del Trimestre 2.
   * **T3:** Nota del Trimestre 3.
   * **Última Actualización:** Fecha y hora del último guardado registrado.
4. Ingrese los valores correspondientes:
   * Valores numéricos entre `0.0` y `10.0`.
   * Puede ingresar las notas de un solo trimestre o de varios a la vez (si están habilitados).
   * Si un estudiante aún no cuenta con evaluación justificada, deje la celda en blanco.
5. Al finalizar la captura de la lista, presione el botón verde **Guardar Calificaciones**.
   * El sistema ejecutará una transacción segura (`bulkStore`) que garantiza que no se pierdan datos ni existan duplicados.

### C. Consulta y Edición Individual
* En la vista general de **Calificaciones** (`/calificaciones`), el maestro puede filtrar por alumno o materia para consultar el historial de notas otorgadas y validar los promedios asignados.

---

## 4. Matriz Comparativa de Facultades

| Acción / Función | Perfil Maestro | Perfil Administrador |
| :--- | :---: | :---: |
| Consultar listado de calificaciones | Sí | Sí |
| Capturar notas en periodos abiertos | Sí | Sí |
| Capturar o modificar notas en periodos cerrados | No | Sí (Bypass) |
| Abrir o cerrar trimestres de evaluación | No | Sí |
| Emitir boletas oficiales y kardex en PDF | Consulta | Emisión y control |
| Publicar calificaciones al Portal de Padres | No | Sí |
| Eliminar registros de calificación | No | Sí |

---

## 5. Preguntas Frecuentes y Solución de Incidencias

* **¿Por qué me aparece el campo de calificación deshabilitado (gris)?**  
  El trimestre correspondiente se encuentra en estado **Cerrado**. Solicite al administrador escolar la apertura temporal del periodo desde el módulo de Control de Periodos.
* **¿Qué sucede si coloco una calificación menor a 0 o mayor a 10?**  
  El sistema valida los valores tanto en el navegador como en el servidor (`min: 0`, `max: 10`). La solicitud será rechazada y se solicitará un valor dentro del rango válido.
* **¿Puedo guardar calificaciones de solo unos cuantos alumnos del grupo?**  
  Sí. Solo las casillas con valores numéricos válidos serán procesadas y guardadas; las casillas en blanco mantendrán su estado previo o permanecerán vacías sin afectar a los demás alumnos.
