# Guía de Contribución

¡Gracias por tu interés en contribuir a sientiaCTH! Este documento proporciona pautas para contribuir al proyecto.

## 📋 Código de Conducta

Al participar en este proyecto, aceptas mantener un ambiente respetuoso y colaborativo. Se espera que todos los contribuidores:

- Sean respetuosos con otros contribuidores
- Acepten críticas constructivas
- Se enfoquen en lo que es mejor para la comunidad
- Muestren empatía hacia otros miembros de la comunidad

## 🚀 ¿Cómo Puedo Contribuir?

### Reportar Bugs

Si encuentras un bug, por favor crea un issue con:

1. **Título descriptivo**: Resume el problema en pocas palabras
2. **Pasos para reproducir**: Describe exactamente cómo reproducir el error
3. **Comportamiento esperado**: Qué debería haber sucedido
4. **Comportamiento actual**: Qué sucedió en su lugar
5. **Información del sistema**: 
   - Versión de PHP
   - Versión de Laravel
   - Navegador (si aplica)
   - Sistema operativo

### Sugerir Mejoras

Para sugerir nuevas funcionalidades:

1. Revisa primero los issues existentes para evitar duplicados
2. Describe claramente la funcionalidad propuesta
3. Explica por qué sería útil para el proyecto
4. Proporciona ejemplos de uso si es posible

### Pull Requests

1. **Fork el repositorio** y crea una rama desde `main`
   ```bash
   git checkout -b feature/mi-nueva-funcionalidad
   ```

2. **Realiza tus cambios** siguiendo las guías de estilo

3. **Escribe tests** para el nuevo código cuando sea posible

4. **Asegúrate de que los tests pasen**:
   ```bash
   php artisan test
   ```

5. **Commit con mensajes descriptivos**:
   ```bash
   git commit -m "Add: descripción breve del cambio"
   ```
   
   Prefijos recomendados:
   - `Add:` para nuevas funcionalidades
   - `Fix:` para correcciones de bugs
   - `Update:` para actualizaciones de código existente
   - `Remove:` para eliminación de código
   - `Refactor:` para refactorización sin cambios funcionales
   - `Docs:` para cambios en documentación

6. **Push a tu fork**:
   ```bash
   git push origin feature/mi-nueva-funcionalidad
   ```

7. **Abre un Pull Request** desde tu rama hacia `main`

## 💻 Guías de Estilo

### PHP

- Sigue [PSR-12](https://www.php-fig.org/psr/psr-12/) para estilo de código
- Usa type hints siempre que sea posible
- Documenta funciones públicas con PHPDoc
- Nombra variables y métodos de forma descriptiva

```php
/**
 * Calculate total worked hours for a user in a date range.
 *
 * @param User $user
 * @param Carbon $startDate
 * @param Carbon $endDate
 * @return float
 */
public function calculateWorkedHours(User $user, Carbon $startDate, Carbon $endDate): float
{
    // Implementation
}
```

### JavaScript

- Usa ES6+ cuando sea posible
- Sigue las convenciones de Alpine.js para componentes del frontend
- Mantén la lógica del negocio en el backend (Livewire)

### Blade/Livewire

- Componentes pequeños y reutilizables
- Evita lógica compleja en las vistas
- Usa directivas de Blade apropiadamente

### Base de Datos

- Las migraciones deben ser reversibles (`up` y `down`)
- Usa nombres descriptivos para tablas y columnas
- Añade índices para columnas que se usen frecuentemente en WHERE/JOIN

## 🧪 Testing

- Escribe tests para nuevas funcionalidades
- Asegúrate de que todos los tests existentes pasen
- Apunta a una cobertura razonable de código

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests específicos
php artisan test --filter=NombreDelTest
```

## 📚 Documentación

Si tu cambio afecta la forma en que los usuarios interactúan con el sistema:

1. Actualiza los manuales relevantes en `public/docs/`
2. Actualiza tanto la versión en español como en inglés
3. Añade ejemplos de uso cuando sea apropiado
4. Actualiza el CHANGELOG si es un cambio significativo

## 🔍 Proceso de Revisión

1. Un mantenedor revisará tu PR
2. Puede solicitar cambios o aclaraciones
3. Una vez aprobado, tu código será fusionado
4. Tu contribución será reconocida en el proyecto

## ❓ ¿Preguntas?

Si tienes preguntas sobre cómo contribuir, puedes:

- Abrir un issue con la etiqueta `question`
- Iniciar una discusión en GitHub Discussions
- Contactar directamente al mantenedor

## 🙏 Agradecimientos

¡Toda contribución es valiosa! Ya sea reportando bugs, sugiriendo mejoras, mejorando la documentación o contribuyendo código, tu ayuda hace que sientiaCTH sea mejor para todos.

---

**¡Gracias por contribuir a sientiaCTH!** 🎉
