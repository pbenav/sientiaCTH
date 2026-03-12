# Política de Seguridad

## Versiones Soportadas

Las siguientes versiones de sientiaCTH reciben actualizaciones de seguridad:

| Versión | Soportada          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reportar una Vulnerabilidad

La seguridad de sientiaCTH es una prioridad. Si descubres una vulnerabilidad de seguridad, por favor ayúdanos a proteger a los usuarios siguiendo estos pasos:

### 🔒 NO hagas lo siguiente:

- ❌ No abras un issue público sobre la vulnerabilidad
- ❌ No publiques la vulnerabilidad en redes sociales o foros
- ❌ No explotes la vulnerabilidad en sistemas de producción

### ✅ Por favor, haz lo siguiente:

1. **Contacta de forma privada** enviando un email a: **[pbenavides@sientia.com]**
   
2. **Incluye la siguiente información**:
   - Descripción detallada de la vulnerabilidad
   - Pasos para reproducirla
   - Versión afectada de sientiaCTH
   - Impacto potencial
   - Cualquier mitigación temporal que sugieras
   - Tu información de contacto (para dar seguimiento)

3. **Espera nuestra respuesta**: Nos comprometemos a:
   - Confirmar la recepción en **48 horas**
   - Validar el reporte en **5 días hábiles**
   - Mantener informado sobre el progreso
   - Dar crédito público si lo deseas (una vez solucionado)

## Proceso de Manejo

1. **Confirmación**: Confirmaremos la recepción del reporte
2. **Investigación**: Validaremos y evaluaremos el impacto
3. **Solución**: Desarrollaremos y probaremos un parche
4. **Notificación**: Informaremos a usuarios afectados si es necesario
5. **Publicación**: Lanzaremos la actualización de seguridad
6. **Divulgación**: Publicaremos detalles después de que los usuarios hayan tenido tiempo de actualizar

## Recompensas

Aunque sientiaCTH es un proyecto de código abierto sin fines de lucro, reconocemos públicamente las contribuciones de seguridad:

- Mención en las notas de la versión
- Crédito en el archivo SECURITY.md
- Agradecimiento especial en la documentación del proyecto

## Prácticas de Seguridad Implementadas

sientiaCTH implementa múltiples capas de seguridad:

### Autenticación y Autorización
- Laravel Sanctum para API tokens seguros
- Soporte 2FA opcional (Laravel Fortify)
- Sistema de permisos granular con 60+ permisos
- Validación contextual por equipo

### Protección de Datos
- Hash seguro de contraseñas (bcrypt)
- Cifrado de datos sensibles en base de datos
- Sanitización HTML con HTMLPurifier
- Protección CSRF en todos los formularios
- Validación de entrada en servidor y cliente

### Seguridad de API
- Rate limiting configurable
- Autenticación por token
- Validación estricta de parámetros
- Respuestas de error sin información sensible

### Infraestructura
- Auditoría completa de acciones críticas
- Logs de seguridad
- Timeouts de sesión configurables
- Protección contra ataques de fuerza bruta

### Base de Datos
- Consultas preparadas (Eloquent ORM)
- Prevención de inyección SQL
- Migraciones idempotentes
- Backups automáticos recomendados

## Configuración Recomendada para Producción

```env
# Asegúrate de configurar estos valores en producción
APP_ENV=production
APP_DEBUG=false
APP_KEY=[genera-una-clave-segura]

# Usa HTTPS
APP_URL=https://tu-dominio.com

# Configura límites de tasa
THROTTLE_LIMIT=60

# Habilita logging
LOG_LEVEL=warning
```

## Actualizaciones de Seguridad

Las actualizaciones de seguridad se publican con la máxima prioridad:

- **Críticas**: Parche en 24-48 horas
- **Altas**: Parche en 7 días
- **Medias**: Incluidas en la siguiente versión menor
- **Bajas**: Incluidas cuando sea conveniente

Mantén tu instalación actualizada ejecutando:

```bash
composer update
php artisan migrate
```

## Buenas Prácticas para Usuarios

1. **Mantén actualizado**: Instala actualizaciones de seguridad inmediatamente
2. **Usa HTTPS**: Nunca ejecutes sientiaCTH sobre HTTP en producción
3. **Contraseñas fuertes**: Requiere contraseñas seguras para todos los usuarios
4. **Backups regulares**: Realiza copias de seguridad de la base de datos
5. **Limita accesos**: Usa el sistema de permisos granular
6. **Audita logs**: Revisa regularmente los logs de seguridad
7. **Firewall**: Configura un firewall adecuado
8. **2FA**: Habilita autenticación de dos factores cuando sea posible

## Historial de Seguridad

### Versión 1.0.0 (Enero 2026)
- Lanzamiento inicial con todas las medidas de seguridad implementadas
- Auditoría completa de seguridad realizada
- Sin vulnerabilidades conocidas

---

**Última actualización**: 3 de Enero de 2026

Para más información sobre seguridad, contacta: **[tu-email-de-seguridad]**
