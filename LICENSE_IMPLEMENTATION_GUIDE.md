# Guía de Implementación de Licencia MIT en sientiaCTH

Este documento describe los pasos completados para cambiar sientiaCTH de una licencia propietaria a la **Licencia MIT**, convirtiéndolo en software libre y gratuito.

## 📋 Cambios Realizados

### 1. Archivo LICENSE ✅

**Ubicación**: `/LICENSE`

Se ha reemplazado completamente el contenido del archivo LICENSE con el texto estándar de la Licencia MIT:

```
MIT License

Copyright (c) 2022-2026 pbenav

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software")...
```

**Acción requerida**: ✅ Completado

---

### 2. README.md ✅

**Ubicación**: `/README.md`

**Cambios realizados**:

1. **Badge de licencia actualizado**:
   ```markdown
   [![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
   ```

2. **Nueva sección de Licencia** añadida al final del documento con:
   - Explicación clara de que es software libre y gratuito
   - Lista de permisos (usar, modificar, distribuir, sublicenciar)
   - Referencia al archivo LICENSE
   - Copyright actualizado: © 2022-2026 pbenav

**Acción requerida**: ✅ Completado

---

### 3. composer.json ✅

**Ubicación**: `/composer.json`

El campo license ya estaba configurado correctamente:
```json
"license": "MIT"
```

**Acción requerida**: ✅ Ya estaba correcto

---

### 4. package.json ✅

**Ubicación**: `/package.json`

**Cambio realizado**:
```json
{
    "private": true,
    "license": "MIT",
    "scripts": {
        ...
    }
}
```

**Acción requerida**: ✅ Completado

---

### 5. Encabezados de Código (Docblocks) ✅

**Ubicación**: `/DOCBLOCK_TEMPLATE.php`

Se ha creado una plantilla estándar para docblocks PHP que debe ser añadida a todos los archivos críticos:

```php
<?php

/**
 * sientiaCTH - Control de Tiempo y Horarios
 * 
 * Este archivo es parte de sientiaCTH, una plataforma integral de gestión
 * de tiempo y control horario empresarial.
 * 
 * @package     sientiaCTH
 * @author      pbenav
 * @copyright   2022-2026 pbenav
 * @license     MIT License
 * @link        https://github.com/pbenav/sientiaCTH
 * @since       Version 1.0.0
 */
```

**Archivos donde aplicar** (prioritarios):
- `app/Services/SmartClockInService.php`
- `app/Services/EventService.php`
- `app/Http/Controllers/**/*.php`
- `app/Models/**/*.php`
- Cualquier archivo nuevo que se cree

**Acción requerida**: ⚠️ Aplicar gradualmente a archivos existentes

---

### 6. Manual del Desarrollador ✅

**Ubicación**: 
- `/public/docs/es/DEVELOPER_MANUAL.md`
- `/public/docs/en/DEVELOPER_MANUAL.md`

**Cambios realizados**:

1. **Nueva sección añadida** al inicio del manual (Sección 1):
   - "Licencia y Términos de Uso" / "License and Terms of Use"
   - Explicación completa de la licencia MIT
   - Permisos, condiciones y limitaciones
   - Plantilla de docblock para desarrolladores
   - Referencia al archivo LICENSE

2. **Renumeración de secciones**: Las secciones existentes se renumeraron de 1-10 a 2-11

**Acción requerida**: ✅ Completado

---

## 🎯 Resumen de Estado

| Componente | Estado | Descripción |
|------------|--------|-------------|
| LICENSE | ✅ | Completamente actualizado a MIT |
| README.md | ✅ | Badge y sección de licencia añadida |
| composer.json | ✅ | Ya tenía license: MIT |
| package.json | ✅ | Campo license añadido |
| Docblock Template | ✅ | Plantilla creada |
| Manual ES | ✅ | Sección de licencia añadida |
| Manual EN | ✅ | Sección de licencia añadida |
| Aplicar docblocks | ⏳ | Pendiente (gradual) |

---

## 📝 Próximos Pasos Opcionales

### 1. Añadir Docblocks a Archivos Existentes

Para aplicar el docblock estándar a archivos existentes, puedes usar este script bash:

```bash
#!/bin/bash
# Añade el docblock MIT a archivos PHP que no lo tengan

DOCBLOCK='<?php

/**
 * sientiaCTH - Control de Tiempo y Horarios
 * 
 * @package     sientiaCTH
 * @author      pbenav
 * @copyright   2022-2026 pbenav
 * @license     MIT License
 * @link        https://github.com/pbenav/sientiaCTH
 * @since       Version 1.0.0
 */

'

# Buscar archivos PHP sin el docblock de licencia
find app/Services -name "*.php" -type f | while read file; do
    if ! grep -q "@license.*MIT" "$file"; then
        echo "Añadiendo docblock a $file"
        # Crear backup
        cp "$file" "$file.bak"
        # Añadir docblock después del <?php
        sed -i '1s/^.*$/'"$DOCBLOCK"'/' "$file"
    fi
done
```

### 2. Actualizar CONTRIBUTING.md

Si tienes un archivo `CONTRIBUTING.md`, asegúrate de mencionar que todas las contribuciones deben seguir la licencia MIT.

### 3. Comunicación

Considera anunciar el cambio de licencia en:
- GitHub Releases (nueva release v1.0.0 con nota de cambio de licencia)
- README del proyecto
- Redes sociales o blog del proyecto

---

## ✅ Verificación Final

Para verificar que todo está correcto:

```bash
# 1. Verificar que LICENSE existe y contiene MIT
cat LICENSE | grep "MIT License"

# 2. Verificar composer.json
cat composer.json | grep '"license": "MIT"'

# 3. Verificar package.json
cat package.json | grep '"license": "MIT"'

# 4. Verificar README badge
cat README.md | grep 'badge/license-MIT'

# 5. Verificar manuales
grep -r "MIT License" public/docs/*/DEVELOPER_MANUAL.md
```

Todos los comandos deberían devolver resultados positivos.

---

## 📞 Contacto

Para preguntas sobre la licencia o contribuciones:
- GitHub: https://github.com/pbenav/sientiaCTH
- Issues: https://github.com/pbenav/sientiaCTH/issues

---

*Documento creado el 3 de enero de 2026*
*© 2022-2026 pbenav - sientiaCTH es software libre bajo licencia MIT*
