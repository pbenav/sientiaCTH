# Testing API Móvil CTH

## 📋 **Endpoints Disponibles**

### **1. POST /api/v1/mobile/clock** - Realizar Fichaje
```bash
curl -X POST "http://localhost:8000/api/v1/mobile/clock" \
  -H "Content-Type: application/json" \
  -d '{
    "work_center_code": "OC-001",
    "user_code": "1232222"
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "action_taken": "entrada",
  "message": "Entrada registrada correctamente",
  "user": {
    "id": 1,
    "name": "Pablo",
    "code": "1232222"
  },
  "work_center": {
    "name": "Oficina Central",
    "code": "OC-001"
  },
  "next_action": "salida",
  "today_records": [...],
  "server_time": "2025-11-06T..."
}
```

### **2. POST /api/v1/mobile/status** - Obtener Estado
```bash
curl -X POST "http://localhost:8000/api/v1/mobile/status" \
  -H "Content-Type: application/json" \
  -d '{"work_center_code": "X", "user_code": "Y"}'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Pablo",
      "code": "1232222"
    },
    "work_center": {
      "id": 1,
      "name": "Oficina Central",
      "code": "OC-001"
    },
    "next_action": "entrada",
    "can_clock": true,
    "today_stats": {
      "entries_count": 0,
      "exits_count": 0,
      "worked_hours": "0:00",
      "current_status": "fuera",
      "last_action": null
    }
  }
}
```

### **3. POST /api/v1/mobile/sync** - Sincronización Offline
```bash
curl -X POST "http://localhost:8000/api/v1/mobile/sync" \
  -H "Content-Type: application/json" \
  -d '{
    "work_center_code": "OC-001",
    "user_code": "1232222",
    "offline_events": [
      {
        "action": "entrada",
        "datetime": "2025-11-06T08:00:00Z"
      },
      {
        "action": "salida", 
        "datetime": "2025-11-06T16:00:00Z"
      }
    ]
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Sincronización completada: 2/2 eventos procesados",
  "data": {
    "total_events": 2,
    "successful_syncs": 2,
    "failed_syncs": 0,
    "sync_results": [...],
    "sync_timestamp": "2025-11-06T..."
  }
}
```

## 🌐 **URLs WebView para Testing**

### **Autenticación Directa con Parámetros:**
```
http://localhost:8000/mobile/auth?work_center_code=OC-001&work_center_name=Oficina%20Central
```

### **Navegación Directa (después de auth):**
```
http://localhost:8000/mobile/home
http://localhost:8000/mobile/history
http://localhost:8000/mobile/schedule
http://localhost:8000/mobile/profile
http://localhost:8000/mobile/reports
```

## 🧪 **Casos de Testing NFC**

### **Formato de Etiquetas NFC:**
```
Texto NDEF: "CTH:OC-001:Oficina Central"
Texto NDEF: "CTH:LAB-002:Laboratorio"
Texto NDEF: "CTH:ALM-003:Almacén"
```

### **Testing Flutter NFC Integration:**

1. **Preparar etiqueta NFC** con formato CTH:CODE:NAME
2. **Scan NFC** desde aplicación Flutter
3. **Validar parsing** del código y nombre
4. **Test API calls** con credenciales obtenidas
5. **Test WebView** con paso automático de credenciales

## ✅ **Checklist de Validación**

- [ ] API `/clock` funciona con datos reales
- [ ] API `/status` retorna estado correcto
- [ ] API `/sync` procesa eventos offline
- [ ] WebView recibe credenciales automáticamente
- [ ] Autenticación móvil funciona correctamente
- [ ] Navegación entre vistas WebView es fluida
- [ ] NFC parsing funciona con formato CTH
- [ ] Manejo de errores es robusto
- [ ] Performance es aceptable
- [ ] Interfaz móvil es responsive

## 🚀 **Próximos Pasos**

1. **Implementar datos reales** en lugar de mock data
2. **Crear etiquetas NFC físicas** para testing
3. **Optimizar queries** para rendimiento móvil
4. **Añadir cache local** para mejor UX
5. **Implementar notificaciones push**
6. **Testing en dispositivos reales**