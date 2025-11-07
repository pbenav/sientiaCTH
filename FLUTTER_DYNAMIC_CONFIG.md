# 🔧 Configuración Dinámica de Servidor - Flutter CTH Mobile

## 📖 Resumen

La aplicación Flutter CTH Mobile incluye un sistema de configuración dinámica que permite adaptar la app a diferentes instalaciones del servidor CTH sin necesidad de recompilar la aplicación.

## 🎯 Funcionalidad

### **Configuración Automática del Servidor**
- La app puede configurarse para conectar con cualquier servidor CTH
- Solo requiere la URL base del servidor (ej: `https://cth.miempresa.com`)
- Obtiene automáticamente todos los endpoints y configuraciones necesarias

### **Verificación NFC Dinámica**
- Lista de centros de trabajo con etiquetas NFC configuradas
- Validación en tiempo real de etiquetas NFC escaneadas
- Sincronización automática con la configuración web

## 🛠️ Implementación en Flutter

### **1. Configuración Inicial**

```dart
// lib/services/config_service.dart
class ConfigService {
  static const String CONFIG_KEY = 'server_config';
  static const String SERVER_URL_KEY = 'server_url';
  
  static Future<void> configureServer(String baseUrl) async {
    try {
      // Limpiar URL (quitar slash final si existe)
      final cleanUrl = baseUrl.replaceAll(RegExp(r'/$'), '');
      
      // Obtener configuración del servidor
      final response = await http.get(
        Uri.parse('$cleanUrl/api/v1/config/server')
      );
      
      if (response.statusCode == 200) {
        final config = json.decode(response.body);
        
        // Guardar configuración localmente
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(CONFIG_KEY, json.encode(config));
        await prefs.setString(SERVER_URL_KEY, cleanUrl);
        
        print('✅ Servidor configurado: ${config['data']['server_info']['name']}');
      } else {
        throw Exception('Error al conectar con el servidor CTH');
      }
    } catch (e) {
      throw Exception('No se pudo configurar el servidor: $e');
    }
  }
  
  static Future<Map<String, dynamic>?> getConfig() async {
    final prefs = await SharedPreferences.getInstance();
    final configString = prefs.getString(CONFIG_KEY);
    
    if (configString != null) {
      return json.decode(configString);
    }
    return null;
  }
  
  static Future<String?> getServerUrl() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(SERVER_URL_KEY);
  }
}
```

### **2. Pantalla de Configuración Inicial**

```dart
// lib/screens/setup_screen.dart
class SetupScreen extends StatefulWidget {
  @override
  _SetupScreenState createState() => _SetupScreenState();
}

class _SetupScreenState extends State<SetupScreen> {
  final TextEditingController _urlController = TextEditingController();
  bool _isLoading = false;
  String? _errorMessage;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Configurar Servidor CTH'),
      ),
      body: Padding(
        padding: EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Card(
              child: Padding(
                padding: EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'URL del Servidor CTH',
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    SizedBox(height: 8),
                    TextField(
                      controller: _urlController,
                      decoration: InputDecoration(
                        hintText: 'https://cth.miempresa.com',
                        prefixIcon: Icon(Icons.language),
                        border: OutlineInputBorder(),
                      ),
                      keyboardType: TextInputType.url,
                    ),
                    SizedBox(height: 8),
                    Text(
                      'Introduce la URL base de tu servidor CTH. La app se configurará automáticamente.',
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ],
                ),
              ),
            ),
            
            SizedBox(height: 16),
            
            if (_errorMessage != null)
              Card(
                color: Colors.red.shade50,
                child: Padding(
                  padding: EdgeInsets.all(16.0),
                  child: Row(
                    children: [
                      Icon(Icons.error, color: Colors.red),
                      SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          _errorMessage!,
                          style: TextStyle(color: Colors.red.shade700),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            
            SizedBox(height: 16),
            
            ElevatedButton(
              onPressed: _isLoading ? null : _configureServer,
              child: _isLoading
                  ? Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        ),
                        SizedBox(width: 8),
                        Text('Configurando...'),
                      ],
                    )
                  : Text('Configurar Servidor'),
            ),
          ],
        ),
      ),
    );
  }

  void _configureServer() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final url = _urlController.text.trim();
      
      if (url.isEmpty) {
        throw Exception('Por favor introduce la URL del servidor');
      }
      
      if (!url.startsWith('http://') && !url.startsWith('https://')) {
        throw Exception('La URL debe comenzar con http:// o https://');
      }

      await ConfigService.configureServer(url);
      
      // Navegar a la pantalla principal
      Navigator.of(context).pushReplacementNamed('/nfc-start');
      
    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }
}
```

### **3. Servicio de API Dinámico**

```dart
// lib/services/api_service.dart
class ApiService {
  static String? _baseUrl;
  static Map<String, dynamic>? _endpoints;

  static Future<void> initialize() async {
    final config = await ConfigService.getConfig();
    if (config != null) {
      _baseUrl = await ConfigService.getServerUrl();
      _endpoints = config['data']['endpoints'];
    }
  }

  static String getEndpoint(String path) {
    if (_endpoints == null || _baseUrl == null) {
      throw Exception('API no configurada. Ejecuta initialize() primero.');
    }
    
    // Buscar endpoint específico
    final parts = path.split('.');
    dynamic current = _endpoints;
    
    for (String part in parts) {
      current = current[part];
      if (current == null) break;
    }
    
    return current ?? '$_baseUrl/api/v1/$path';
  }

  // Métodos específicos para endpoints comunes
  static String get clockInUrl => getEndpoint('clock.clock_in');
  static String get clockOutUrl => getEndpoint('clock.clock_out');
  static String get nfcVerifyUrl => getEndpoint('nfc.verify_tag');
  static String get workCentersUrl => getEndpoint('nfc.work_centers');
}
```

### **4. Verificación NFC con Configuración Dinámica**

```dart
// lib/services/nfc_service.dart
class NFCService {
  static Future<Map<String, dynamic>?> verifyNFCTag(String tagId) async {
    try {
      await ApiService.initialize();
      
      final response = await http.post(
        Uri.parse(ApiService.nfcVerifyUrl),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'nfc_tag_id': tagId}),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['data'];
      } else if (response.statusCode == 404) {
        final error = json.decode(response.body);
        throw NFCTagNotConfiguredException(error['error']);
      } else {
        throw Exception('Error verificando etiqueta NFC');
      }
    } catch (e) {
      throw Exception('Error de conexión: $e');
    }
  }
  
  static Future<List<Map<String, dynamic>>> getConfiguredWorkCenters() async {
    try {
      await ApiService.initialize();
      
      final response = await http.get(
        Uri.parse(ApiService.workCentersUrl),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return List<Map<String, dynamic>>.from(data['data']);
      } else {
        throw Exception('Error obteniendo centros de trabajo');
      }
    } catch (e) {
      throw Exception('Error de conexión: $e');
    }
  }
}

class NFCTagNotConfiguredException implements Exception {
  final String message;
  NFCTagNotConfiguredException(this.message);
  
  @override
  String toString() => message;
}
```

## 🎨 UI/UX - Flujo de Configuración

### **Flujo Recomendado:**

1. **Primera Apertura**: Mostrar pantalla de configuración de servidor
2. **URL Válida**: Probar conexión y obtener configuración automáticamente
3. **Configuración Exitosa**: Navegar a pantalla principal (NFC scan)
4. **Reconfiguración**: Opción en ajustes para cambiar servidor

### **Pantalla de Estado de Conexión:**

```dart
// Widget informativo sobre el servidor configurado
class ServerStatusWidget extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return FutureBuilder<Map<String, dynamic>?>(
      future: ConfigService.getConfig(),
      builder: (context, snapshot) {
        if (!snapshot.hasData) return SizedBox.shrink();
        
        final serverInfo = snapshot.data!['data']['server_info'];
        return Card(
          child: ListTile(
            leading: Icon(Icons.cloud_done, color: Colors.green),
            title: Text(serverInfo['name']),
            subtitle: Text('Conectado - Versión ${serverInfo['version']}'),
            trailing: IconButton(
              icon: Icon(Icons.settings),
              onPressed: () => _showServerSettings(context),
            ),
          ),
        );
      },
    );
  }
}
```

## 📋 Endpoints de Configuración Disponibles

### **Configuración del Servidor**
```
GET /api/v1/config/server
```
Retorna toda la configuración necesaria para la app Flutter.

### **Test de Conectividad**
```
GET /api/v1/config/ping
```
Endpoint simple para verificar conectividad.

### **Centros de Trabajo con NFC**
```
GET /api/v1/config/work-centers/nfc
```
Lista de centros de trabajo que tienen etiquetas NFC configuradas.

### **Verificación de Etiqueta NFC**
```
POST /api/v1/config/nfc/verify
Content-Type: application/json

{
  "nfc_tag_id": "04:A3:22:B2:C4:15:80"
}
```

## 🔒 Consideraciones de Seguridad

- **URLs HTTPS**: Recomendar siempre conexiones seguras en producción
- **Validación de Certificados**: Verificar certificados SSL en la app
- **Timeout de Configuración**: Implementar timeouts para evitar esperas indefinidas
- **Caché Local**: Guardar configuración localmente para funcionamiento offline

## 📝 Notas de Implementación

1. **Configuración por Defecto**: Si no hay configuración, mostrar pantalla de setup
2. **Reconfiguración**: Permitir cambiar servidor desde ajustes
3. **Validación de URL**: Verificar formato y conectividad antes de guardar
4. **Feedback Visual**: Mostrar estado de conexión claramente
5. **Manejo de Errores**: Mensajes informativos para problemas comunes

Esta implementación permite que una sola compilación de la app Flutter funcione con múltiples instalaciones de CTH, mejorando significativamente la flexibilidad y facilidad de despliegue.