# 🧪 Datos de Prueba para API Móvil CTH

## 📋 Datos Disponibles para Testing

### **Centros de Trabajo**
```json
{
  "work_centers": [
    {
      "id": 1,
      "name": "Ayuntamiento",
      "code": "OC-001",
      "team_id": 1
    },
    {
      "id": 2, 
      "name": "Sede Secundaria",
      "code": "SS-002",
      "team_id": 1
    }
  ]
}
```

### **Usuarios de Prueba Sugeridos**
```json
{
  "test_users": [
    {
      "id": 4,
      "name": "Pablo",
      "user_code": "1232222",
      "recommended_for": "Desarrollo y testing principal"
    },
    {
      "id": 1,
      "name": "Administrador", 
      "user_code": "958368934",
      "recommended_for": "Testing de permisos admin"
    },
    {
      "id": 41,
      "name": "Eduardo Marino",
      "user_code": "123456789",
      "recommended_for": "Testing con código simple"
    },
    {
      "id": 20,
      "name": "Inspector",
      "user_code": "12322220",
      "recommended_for": "Testing de roles especiales"
    }
  ]
}
```

## 🎯 Combinaciones de Testing Recomendadas

### **Test Set 1: Usuario Principal (Pablo)**
```json
{
  "work_center_code": "OC-001",
  "user_secret_code": "1232222",
  "location": {
    "latitude": 40.4168,
    "longitude": -3.7038
  }
}
```

### **Test Set 2: Centro Secundario**
```json
{
  "work_center_code": "SS-002",
  "user_secret_code": "123456789",
  "location": {
    "latitude": 40.4168,
    "longitude": -3.7038
  }
}
```

### **Test Set 3: Sin Geolocalización**
```json
{
  "work_center_code": "OC-001",
  "user_secret_code": "958368934"
}
```

## ❌ Casos de Error para Testing

### **Error 404: Centro No Encontrado**
```json
{
  "work_center_code": "INVALID-CODE",
  "user_secret_code": "1232222"
}
```

### **Error 401: Usuario No Válido**
```json
{
  "work_center_code": "OC-001", 
  "user_secret_code": "9999999999"
}
```

### **Error 422: Validación**
```json
{
  "work_center_code": "",
  "user_secret_code": "codigo_muy_muy_largo_que_supera_limite"
}
```

### **Error 422: Geolocalización Inválida**
```json
{
  "work_center_code": "OC-001",
  "user_secret_code": "1232222",
  "location": {
    "latitude": 999,
    "longitude": -999
  }
}
```

## 📱 Environment de Insomnia

### **Environment Variables**
```json
{
  "base_url": "http://localhost:8000",
  "api_version": "v1",
  
  "_comment_work_centers": "Centros de trabajo disponibles",
  "wc_ayuntamiento": "OC-001",
  "wc_sede_secundaria": "SS-002",
  
  "_comment_users": "Códigos de usuarios de prueba", 
  "user_pablo": "1232222",
  "user_admin": "958368934",
  "user_eduardo": "123456789",
  "user_inspector": "12322220",
  
  "_comment_invalid": "Datos inválidos para testing de errores",
  "invalid_center": "INVALID-CODE",
  "invalid_user": "9999999999",
  
  "_comment_location": "Coordenadas de Madrid",
  "madrid_lat": 40.4168,
  "madrid_lng": -3.7038
}
```

### **Request Templates con Variables**

#### **Template 1: Test Exitoso**
```json
{
  "work_center_code": "{{ _.wc_ayuntamiento }}",
  "user_secret_code": "{{ _.user_pablo }}",
  "location": {
    "latitude": {{ _.madrid_lat }},
    "longitude": {{ _.madrid_lng }}
  }
}
```

#### **Template 2: Test de Error - Centro Inválido**
```json
{
  "work_center_code": "{{ _.invalid_center }}",
  "user_secret_code": "{{ _.user_pablo }}"
}
```

#### **Template 3: Test de Error - Usuario Inválido**
```json
{
  "work_center_code": "{{ _.wc_ayuntamiento }}",
  "user_secret_code": "{{ _.invalid_user }}"
}
```

## 🔄 Flujo de Testing Paso a Paso

### **Secuencia Completa de Fichaje**

1. **INICIO** - Usuario no ha fichado hoy
   ```bash
   Request: Pablo + OC-001
   Expected: action_taken = "clock_in"
   Status: "working"
   ```

2. **PAUSA** - Usuario está trabajando
   ```bash
   Request: Pablo + OC-001  
   Expected: action_taken = "break_start"
   Status: "on_break"
   ```

3. **VOLVER** - Usuario está en pausa
   ```bash
   Request: Pablo + OC-001
   Expected: action_taken = "break_end" 
   Status: "working"
   ```

4. **SALIDA** - Usuario está trabajando
   ```bash
   Request: Pablo + OC-001
   Expected: action_taken = "clock_out"
   Status: "clocked_out"
   ```

### **Verificación de Respuestas**

#### **Campos Obligatorios en Response**
- ✅ `success`: boolean
- ✅ `action_taken`: string (clock_in|break_start|break_end|clock_out)
- ✅ `message`: string descriptivo
- ✅ `user.id`: number
- ✅ `user.name`: string
- ✅ `user.current_status`: string (working|on_break|clocked_out)
- ✅ `user.work_center.code`: string
- ✅ `today_records`: array
- ✅ `server_time`: ISO string

#### **Campos Opcionales**
- 🔄 `work_schedule`: object (puede ser null)
- 🔄 `user.family_name1`: string (puede estar vacío)

## 🛠️ Preparación del Sistema

### **Comando para Limpiar Estado (Si Necesario)**
```bash
# Eliminar eventos de hoy para el usuario Pablo (ID: 4)
cd /home/pablo/cth && php artisan tinker --execute="
\App\Models\Event::where('user_id', 4)
    ->whereDate('start', today())
    ->delete();
echo 'Estado de Pablo limpiado para testing';
"
```

### **Verificar Estado Actual del Usuario**
```bash
# Ver último estado de Pablo
cd /home/pablo/cth && php artisan tinker --execute="
\$user = \App\Models\User::find(4);
\$today = \Carbon\Carbon::today();
\$events = \App\Models\Event::where('user_id', 4)
    ->whereDate('start', \$today)
    ->with('eventType')
    ->orderBy('start')
    ->get();
    
echo 'Eventos de hoy para Pablo:' . PHP_EOL;
\$events->each(function(\$e) {
    echo \"- {\$e->eventType->name}: {\$e->start} - {\$e->end} (Open: {\$e->is_open})\" . PHP_EOL;
});
"
```

---

## 🚀 ¡Listo para Testear!

**Pasos rápidos:**
1. Levantar servidor: `php artisan serve`
2. Abrir Insomnia
3. Usar datos: `OC-001` + `1232222` (Pablo)
4. Hacer 4 llamadas seguidas para ver el flujo completo

**¡El API está lista para probar!** 🎯