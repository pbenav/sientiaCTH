# 🧪 Testing de API Móvil CTH con Insomnia

## 🔧 Configuración Inicial de Insomnia

### **1. Crear Nuevo Request**
1. Abrir Insomnia
2. Crear nueva colección: `CTH Mobile API`
3. Nuevo request: `Mobile Clock Action`

### **2. Configuración del Request**

**Método**: `POST`

**URL**: `http://localhost:8000/api/v1/mobile/clock`
*(Ajustar el dominio según tu configuración)*

**Headers**:
```
Content-Type: application/json
Accept: application/json
```

## 📝 Casos de Prueba

### **Test 1: Clock In (Entrada)**
```json
{
  "work_center_code": "CTH001",
  "user_secret_code": "1234",
  "location": {
    "latitude": 40.4168,
    "longitude": -3.7038
  }
}
```

**Respuesta esperada (200 OK)**:
```json
{
  "success": true,
  "action_taken": "clock_in",
  "message": "Successfully clocked in",
  "user": {
    "id": 1,
    "name": "Usuario",
    "family_name1": "Apellido",
    "current_status": "working",
    "work_center": {
      "id": 1,
      "name": "Centro Principal",
      "code": "CTH001"
    }
  },
  "work_schedule": {...},
  "today_records": [...],
  "server_time": "2024-11-06T14:30:00Z"
}
```

### **Test 2: Código de Centro Inválido**
```json
{
  "work_center_code": "INVALID",
  "user_secret_code": "1234"
}
```

**Respuesta esperada (404 Not Found)**:
```json
{
  "success": false,
  "error": "invalid_work_center",
  "message": "Work center not found"
}
```

### **Test 3: Código de Usuario Inválido**
```json
{
  "work_center_code": "CTH001",
  "user_secret_code": "9999"
}
```

**Respuesta esperada (401 Unauthorized)**:
```json
{
  "success": false,
  "error": "invalid_credentials",
  "message": "Invalid user credentials or unauthorized for this work center"
}
```

### **Test 4: Request Malformado**
```json
{
  "work_center_code": "",
  "user_secret_code": "1234567890123456789"
}
```

**Respuesta esperada (422 Validation Error)**:
```json
{
  "success": false,
  "error": "validation_error",
  "message": "Invalid request data",
  "errors": {
    "work_center_code": ["The work center code field is required."],
    "user_secret_code": ["The user secret code may not be greater than 10 characters."]
  }
}
```

## 🔄 Flujo de Pruebas Completo

### **Secuencia 1: Día de Trabajo Completo**

1. **Entrada** (Clock In)
```json
{
  "work_center_code": "CTH001",
  "user_secret_code": "1234"
}
```
*Resultado esperado: action_taken = "clock_in"*

2. **Pausa** (Break Start) - Ejecutar mismo request
```json
{
  "work_center_code": "CTH001",
  "user_secret_code": "1234"
}
```
*Resultado esperado: action_taken = "break_start"*

3. **Volver de Pausa** (Break End) - Ejecutar mismo request
```json
{
  "work_center_code": "CTH001",
  "user_secret_code": "1234"
}
```
*Resultado esperado: action_taken = "break_end"*

4. **Salida** (Clock Out) - Ejecutar mismo request
```json
{
  "work_center_code": "CTH001",
  "user_secret_code": "1234"
}
```
*Resultado esperado: action_taken = "clock_out"*

## 🛠️ Configuración de Environment Variables en Insomnia

### **1. Crear Environment**
```json
{
  "base_url": "http://localhost:8000",
  "api_version": "v1",
  "test_work_center": "CTH001",
  "test_user_code": "1234",
  "madrid_lat": 40.4168,
  "madrid_lng": -3.7038
}
```

### **2. Request con Variables**
**URL**: `{{ _.base_url }}/api/{{ _.api_version }}/mobile/clock`

**Body**:
```json
{
  "work_center_code": "{{ _.test_work_center }}",
  "user_secret_code": "{{ _.test_user_code }}",
  "location": {
    "latitude": {{ _.madrid_lat }},
    "longitude": {{ _.madrid_lng }}
  }
}
```

## 📊 Verificaciones de Testing

### **Checklist de Validaciones**

#### ✅ **Estructura de Respuesta**
- [ ] Campo `success` presente y tipo boolean
- [ ] Campo `action_taken` con valor válido
- [ ] Campo `message` con descripción clara
- [ ] Objeto `user` completo con datos correctos
- [ ] Array `today_records` con formato correcto
- [ ] `server_time` en formato ISO 8601

#### ✅ **Lógica de Negocio**
- [ ] Primera llamada genera `clock_in`
- [ ] Segunda llamada genera `break_start` o `clock_out`
- [ ] Tercera llamada genera `break_end`
- [ ] Cuarta llamada genera `clock_out`
- [ ] Estados de usuario se actualizan correctamente

#### ✅ **Manejo de Errores**
- [ ] Códigos HTTP correctos (200, 401, 404, 422, 500)
- [ ] Mensajes de error descriptivos
- [ ] Estructura de error consistente
- [ ] Validación de campos requeridos

#### ✅ **Performance**
- [ ] Respuesta < 2 segundos
- [ ] Headers de respuesta apropiados
- [ ] Tamaño de respuesta razonable

## 🔍 Debugging y Troubleshooting

### **Logs del Servidor**
```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Filtrar logs de API móvil
tail -f storage/logs/laravel.log | grep "Mobile clock API"
```

### **Problemas Comunes**

#### **1. Error 404 - Ruta no encontrada**
- Verificar que Laravel está ejecutándose: `php artisan serve`
- Comprobar URL exacta: `/api/v1/mobile/clock`
- Revisar cache de rutas: `php artisan route:clear`

#### **2. Error 500 - Error interno**
- Revisar logs: `storage/logs/laravel.log`
- Verificar configuración de base de datos
- Comprobar que existen usuarios y centros de trabajo

#### **3. Códigos no válidos**
- Verificar en DB que existe el centro: `SELECT * FROM work_centers WHERE code = 'CTH001'`
- Verificar código secreto: `SELECT * FROM users WHERE secret_code = '1234'`
- Comprobar relación usuario-equipo

## 🎯 Datos de Prueba Sugeridos

### **Script para Crear Datos de Test**
```sql
-- Insertar centro de trabajo de prueba
INSERT INTO work_centers (name, code, team_id, created_at, updated_at) 
VALUES ('Centro Test', 'CTH001', 1, NOW(), NOW());

-- Actualizar código secreto de usuario
UPDATE users SET secret_code = '1234' WHERE id = 1;

-- Verificar configuración
SELECT u.name, u.secret_code, wc.code as work_center_code, t.name as team_name
FROM users u
JOIN team_user tu ON u.id = tu.user_id
JOIN teams t ON tu.team_id = t.id
JOIN work_centers wc ON t.id = wc.team_id
WHERE u.secret_code = '1234';
```

## 📋 Plantilla de Reporte de Testing

```markdown
## Test Results - CTH Mobile API

**Fecha**: [FECHA]
**Tester**: [NOMBRE]
**Environment**: [LOCAL/STAGING/PROD]

### Casos Exitosos ✅
- [ ] Clock In
- [ ] Break Start  
- [ ] Break End
- [ ] Clock Out

### Casos de Error ✅
- [ ] Invalid Work Center
- [ ] Invalid User Code
- [ ] Validation Errors

### Performance
- Tiempo promedio respuesta: ____ ms
- Requests totales: ____
- Errores: ____

### Observaciones
[Notas adicionales]
```

---

**¡Listo para testing!** 🚀

Con esta configuración podrás probar completamente la API móvil y verificar que toda la lógica de fichaje funciona correctamente antes de desarrollar la app Flutter.