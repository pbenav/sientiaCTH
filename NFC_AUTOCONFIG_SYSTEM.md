# Sistema NFC con Auto-Configuración CTH

## Descripción General

El sistema NFC de CTH ahora incluye funcionalidad de auto-configuración que permite que las etiquetas NFC contengan tanto el identificador del centro de trabajo como la URL del servidor. Esto permite que la aplicación Flutter se configure automáticamente al leer una etiqueta NFC por primera vez.

## Arquitectura del Sistema

### 1. Generación de Payload NFC

Cuando se habilita NFC para un centro de trabajo, el sistema genera:

- **NFC Tag ID**: Identificador único del formato `CTH-{timestamp}-{hash}-{random}` (menos de 64 bytes)
- **NFC Payload**: JSON completo con información del servidor y centro de trabajo

### 2. Estructura del Payload NFC

```json
{
    "server_url": "https://tu-cth-server.com",
    "api_endpoint": "https://tu-cth-server.com/api/v1",
    "nfc_tag_id": "CTH-673C8A7F-A1B2C3D4-1234567890ABCDEF",
    "work_center_id": 1,
    "work_center_code": "WC001",
    "team_id": 1,
    "generated_at": "2025-11-07T01:45:31.000Z",
    "version": "1.0"
}
```

### 3. Flujo de Auto-Configuración

1. **Programación de Etiqueta**: El administrador copia el payload completo desde la webapp y lo programa en una etiqueta NFC física
2. **Lectura por Flutter**: La app Flutter lee la etiqueta NFC
3. **Detección de Payload**: La app detecta que es un payload JSON completo (no solo un ID)
4. **Auto-Configuración**: La app extrae la URL del servidor y se configura automáticamente
5. **Verificación**: La app verifica el centro de trabajo usando el endpoint configurado

## Implementación Técnica

### Base de Datos

```sql
-- Tabla work_centers actualizada
ALTER TABLE work_centers ADD COLUMN nfc_payload VARCHAR(500) NULL;
ALTER TABLE work_centers ADD INDEX work_centers_nfc_payload_index (nfc_payload);
```

### Modelo WorkCenter

```php
// Métodos principales
public function generateNFCPayload(string $nfcId): string
public function enableNFC(?string $description = null): string
public function getNFCPayloadData(): ?array
```

### API Endpoints

#### Verificación NFC Mejorada
```
POST /api/v1/config/verify-nfc
Content-Type: application/json

{
    "nfc_data": "{\"server_url\":\"...\",\"nfc_tag_id\":\"...\",...}"
}
```

Respuesta con auto-configuración:
```json
{
    "success": true,
    "data": {
        "work_center": {
            "id": 1,
            "name": "Oficina Principal",
            "code": "WC001",
            "team_id": 1
        },
        "verification": {
            "verified_at": "2025-11-07T01:45:31.000Z",
            "nfc_data": "...",
            "status": "verified"
        },
        "auto_configuration": {
            "server_configured": true,
            "server_url": "https://tu-cth-server.com",
            "api_endpoint": "https://tu-cth-server.com/api/v1"
        }
    },
    "message": "NFC verified and server auto-configuration data provided"
}
```

## Interfaz de Usuario

### Vista de Centros de Trabajo

La webapp ahora muestra:

1. **Estado NFC**: Visual claro si NFC está habilitado
2. **NFC ID**: Identificador corto para referencia
3. **Payload Completo**: JSON completo para programar en la etiqueta física
4. **Botones de Acción**:
   - Copiar NFC ID
   - Copiar Payload Completo
   - Regenerar NFC

### Proceso de Configuración

1. **Administrador Web**:
   - Accede a la gestión de centros de trabajo
   - Habilita NFC para un centro
   - Copia el payload completo generado
   - Programa una etiqueta NFC física con este payload

2. **Empleado con Flutter**:
   - Abre la app CTH Flutter
   - Lee la etiqueta NFC
   - La app se configura automáticamente
   - Puede fichar inmediatamente

## Beneficios del Sistema

### Para Administradores
- **Implementación Sencilla**: Una sola etiqueta NFC por centro de trabajo
- **Auto-Configuración**: Los empleados no necesitan configurar manualmente la app
- **Escalabilidad**: Funciona con múltiples instalaciones CTH
- **Seguridad**: IDs únicos y payloads con timestamp

### Para Empleados
- **Experiencia Fluida**: Leer etiqueta NFC configura automáticamente la app
- **Sin Configuración Manual**: No necesitan conocer URLs de servidor
- **Inmediato**: Pueden fichar inmediatamente después de leer la etiqueta

### Para Desarrolladores
- **Compatibilidad**: Soporta tanto IDs simples como payloads completos
- **Flexibilidad**: Puede funcionar con diferentes instalaciones CTH
- **Mantenibilidad**: Estructura clara y documentada

## Casos de Uso

### 1. Empresa con Múltiples Ubicaciones
- Cada ubicación tiene su servidor CTH
- Cada etiqueta NFC configura la app para su servidor específico
- Empleados pueden trabajar en diferentes ubicaciones sin reconfigurar

### 2. Implementación Gradual
- Comienza con etiquetas NFC simples (compatibilidad hacia atrás)
- Migra gradualmente a payloads completos
- Sistema funciona en modo híbrido

### 3. Instalaciones Distribuidas
- Diferentes departamentos con diferentes servidores CTH
- Una sola APK funciona para todos los departamentos
- Auto-configuración basada en ubicación física

## Consideraciones Técnicas

### Limitaciones NFC
- Payload máximo: 500 caracteres (bien dentro del límite NFC)
- Formato JSON compacto
- Compatible con etiquetas NFC estándar

### Seguridad
- IDs únicos con componentes criptográficos
- Timestamps para validación temporal
- Verificación server-side de todos los datos

### Mantenimiento
- Regeneración fácil de etiquetas
- Historial de generación con timestamps
- Índices de base de datos para búsquedas rápidas

## Migración desde Sistema Anterior

### Compatibilidad
- Sistema soporta tanto IDs antiguos como nuevos payloads
- API detecta automáticamente el tipo de datos NFC
- No requiere migración inmediata

### Proceso de Actualización
1. Desplegar nueva versión de webapp
2. Ejecutar migraciones de base de datos
3. Regenerar etiquetas NFC existentes (opcional)
4. Actualizar app Flutter para soportar auto-configuración

## Conclusión

El sistema NFC con auto-configuración simplifica significativamente la implementación y uso de CTH en entornos multi-servidor, proporcionando una experiencia fluida tanto para administradores como para usuarios finales, mientras mantiene la seguridad y escalabilidad del sistema.