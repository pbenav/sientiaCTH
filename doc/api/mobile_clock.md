# API: /api/v1/mobile/clock

## Descripción
Endpoint para registrar acciones de fichaje (entrada, salida, pausa, continuación de jornada, fichaje excepcional) desde la app móvil.

## Método
`POST /api/v1/mobile/clock`

## Parámetros de entrada (JSON)
- `user_code` (string, requerido): Código único del usuario.
- `work_center_code` (string, opcional): Código del centro de trabajo. Si no se envía, se infiere del equipo actual del usuario.
- `manual_work_center_code` (string, opcional): Alternativa manual para el centro de trabajo.
- `action` (string, opcional): Acción a realizar. Solo se debe enviar para pausas (`pause`) o continuación de jornada (`resume_workday`). Valores posibles:
  - `pause`: Iniciar pausa en la jornada.
  - `resume_workday`: Continuar la jornada tras una pausa.
- `pause_event_id` (int, opcional): Solo requerido para `resume_workday`. Identificador del evento de pausa a cerrar.
- `location` (objeto, opcional): Información de geolocalización.
  - `latitude` (float)
  - `longitude` (float)

## Ejemplo de payload para pausa:
```json
{
  "user_code": "U12345",
  "work_center_code": "WC01",
  "action": "pause"
}
```

## Ejemplo de payload para continuar jornada:
```json
{
  "user_code": "U12345",
  "work_center_code": "WC01",
  "action": "resume_workday",
  "pause_event_id": 789
}
```

## Ejemplo de payload para fichaje normal (entrada/salida):
```json
{
  "user_code": "U12345",
  "work_center_code": "WC01"
}
```

## Respuesta
- `success` (bool): Indica si la operación fue exitosa.
- `message` (string): Mensaje descriptivo.
- `data` (objeto): Información relevante del fichaje.
  - `action` (string): Acción realizada (`clock_in`, `clock_out`, `break_start`, `break_end`).
  - `timestamp` (string): Fecha y hora del servidor.
  - `work_center_code` (string)
  - `user_code` (string)
  - `today_stats` (objeto): Estado actual y horas trabajadas.
    - `total_entries` (int)
    - `total_exits` (int)
    - `worked_hours` (string)
    - `current_status` (string)
- `user` (objeto): Datos del usuario.
- `work_schedule` (objeto): Horario del usuario.
- `today_records` (array): Eventos del día.
- `server_time` (string): Fecha y hora actual del servidor.

## Notas importantes
- Para iniciar una pausa, solo se debe enviar `action: pause`.
- Para continuar la jornada tras una pausa, se debe enviar `action: resume_workday` y el `pause_event_id` correspondiente.
- Para fichaje normal (entrada/salida), NO enviar el campo `action`.
- El endpoint valida automáticamente el centro de trabajo y el equipo del usuario.
- Los errores se devuelven con `success: false` y un mensaje descriptivo.

---
*Actualizado: Noviembre 2025*
