# 📱 Prompt Detallado para App Android de Fichaje CTH en Flutter

## 🎯 Objetivo del Proyecto
Desarrollar una aplicación Android nativa usando Flutter para el sistema de fichaje (clock-in/clock-out) del sistema CTH. La app debe permitir a los trabajadores registrar su entrada, pausas y salida de manera rápida y sencilla usando códigos QR o entrada manual.

## 📋 Especificaciones Técnicas

### **Plataforma y Tecnologías**
- **Framework**: Flutter 3.16+ (Dart 3.2+)
- **Plataforma objetivo**: Android (API nivel 21+)
- **Arquitectura**: Clean Architecture con Provider/Riverpod
- **HTTP Client**: Dio para comunicación API
- **Base de datos local**: Hive/SQLite para caché
- **Escaneo QR**: qr_code_scanner package
- **Geolocalización**: geolocator package

### **Dependencias Principales**
```yaml
dependencies:
  flutter: sdk: flutter
  dio: ^5.3.2
  provider: ^6.1.1
  qr_code_scanner: ^3.0.1
  geolocator: ^10.1.0
  shared_preferences: ^2.2.2
  hive: ^2.2.3
  hive_flutter: ^1.1.0
  permission_handler: ^11.1.0
  connectivity_plus: ^5.0.2
  flutter_secure_storage: ^9.0.0
  intl: ^0.18.1
```

## 🏗️ Arquitectura de la Aplicación

### **Estructura de Carpetas**
```
lib/
├── main.dart
├── core/
│   ├── constants/
│   │   ├── api_constants.dart
│   │   ├── app_constants.dart
│   │   └── storage_keys.dart
│   ├── errors/
│   │   ├── exceptions.dart
│   │   └── failures.dart
│   ├── network/
│   │   ├── api_client.dart
│   │   └── network_info.dart
│   └── utils/
│       ├── validators.dart
│       └── date_helper.dart
├── data/
│   ├── datasources/
│   │   ├── local/
│   │   │   └── local_storage_datasource.dart
│   │   └── remote/
│   │       └── clock_api_datasource.dart
│   ├── models/
│   │   ├── user_model.dart
│   │   ├── work_schedule_model.dart
│   │   ├── time_record_model.dart
│   │   └── api_response_model.dart
│   └── repositories/
│       └── clock_repository_impl.dart
├── domain/
│   ├── entities/
│   │   ├── user.dart
│   │   ├── work_schedule.dart
│   │   └── time_record.dart
│   ├── repositories/
│   │   └── clock_repository.dart
│   └── usecases/
│       ├── clock_in_usecase.dart
│       ├── get_user_status_usecase.dart
│       └── save_credentials_usecase.dart
├── presentation/
│   ├── providers/
│   │   ├── auth_provider.dart
│   │   ├── clock_provider.dart
│   │   └── settings_provider.dart
│   ├── screens/
│   │   ├── splash_screen.dart
│   │   ├── setup_screen.dart
│   │   ├── home_screen.dart
│   │   ├── qr_scanner_screen.dart
│   │   └── history_screen.dart
│   ├── widgets/
│   │   ├── clock_button.dart
│   │   ├── status_card.dart
│   │   ├── time_record_tile.dart
│   │   └── loading_overlay.dart
│   └── theme/
│       └── app_theme.dart
└── services/
    ├── location_service.dart
    ├── qr_service.dart
    └── notification_service.dart
```

## 🔗 Integración con API CTH

### **Endpoint Principal**
- **URL**: `https://tu-dominio.com/api/v1/mobile/clock`
- **Método**: POST
- **Content-Type**: application/json

### **Request Format**
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

### **Response Format**
```json
{
  "success": true,
  "action_taken": "clock_in|break_start|break_end|clock_out",
  "message": "Successfully clocked in",
  "user": {
    "id": 1,
    "name": "Juan",
    "family_name1": "Pérez",
    "current_status": "working|on_break|clocked_out",
    "work_center": {
      "id": 1,
      "name": "Centro Principal",
      "code": "CTH001"
    }
  },
  "work_schedule": {
    "monday": {
      "slots": [
        {"start": "08:00", "end": "12:00"},
        {"start": "13:00", "end": "17:00"}
      ]
    }
  },
  "today_records": [
    {
      "id": 123,
      "type": "Jornada laboral",
      "start": "2024-11-06T08:00:00Z",
      "end": null,
      "observations": null,
      "is_closed": false
    }
  ],
  "server_time": "2024-11-06T08:30:00Z"
}
```

## 🎨 Diseño y UX

### **Paleta de Colores**
- **Primary**: #2563EB (Azul CTH)
- **Secondary**: #10B981 (Verde éxito)
- **Warning**: #F59E0B (Naranja)
- **Error**: #EF4444 (Rojo)
- **Background**: #F8FAFC
- **Surface**: #FFFFFF
- **Text Primary**: #1F2937
- **Text Secondary**: #6B7280

### **Pantallas Principales**

#### **1. Pantalla de Configuración Inicial**
```dart
// Campos requeridos
- Campo: Código de Centro de Trabajo
- Campo: Código Secreto Personal
- Toggle: Recordar credenciales
- Botón: Configurar y Probar Conexión
- Opción: Escanear QR para configuración automática
```

#### **2. Pantalla Principal (Home)**
```dart
// Elementos UI
- Header: Nombre usuario + Centro de trabajo
- Status Card: Estado actual (Trabajando/En pausa/Fichado)
- Botón Principal: Acción principal (Fichar/Pausar/Salir)
- Horario del día: Mostrar slots de trabajo programados
- Últimos registros: Lista de fichajes del día
- FAB: Escanear QR para fichaje rápido
```

#### **3. Pantalla de Historial**
```dart
// Elementos UI
- Selector de fecha
- Lista de registros con tipos de evento
- Indicadores visuales de estado
- Duración total trabajada
- Pausas tomadas
```

## 💻 Implementación Detallada

### **1. API Client Service**
```dart
class ClockApiService {
  final Dio _dio;
  static const String baseUrl = 'https://your-domain.com/api/v1';
  
  Future<ApiResponse> performClockAction({
    required String workCenterCode,
    required String userSecretCode,
    Position? location,
  }) async {
    try {
      final response = await _dio.post(
        '$baseUrl/mobile/clock',
        data: {
          'work_center_code': workCenterCode,
          'user_secret_code': userSecretCode,
          if (location != null) 'location': {
            'latitude': location.latitude,
            'longitude': location.longitude,
          },
        },
      );
      
      return ApiResponse.fromJson(response.data);
    } catch (e) {
      throw ClockException('Error en fichaje: ${e.toString()}');
    }
  }
}
```

### **2. State Management con Provider**
```dart
class ClockProvider extends ChangeNotifier {
  ClockStatus _status = ClockStatus.loggedOut;
  User? _user;
  List<TimeRecord> _todayRecords = [];
  bool _isLoading = false;
  
  Future<void> performClockAction() async {
    setLoading(true);
    try {
      final location = await _locationService.getCurrentLocation();
      final response = await _apiService.performClockAction(
        workCenterCode: _credentials.workCenterCode,
        userSecretCode: _credentials.userSecretCode,
        location: location,
      );
      
      _updateStateFromResponse(response);
      await _saveToLocalStorage(response);
      
    } catch (e) {
      _handleError(e);
    } finally {
      setLoading(false);
    }
  }
}
```

### **3. QR Code Integration**
```dart
class QRScannerScreen extends StatefulWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Escanear QR')),
      body: QRView(
        key: qrKey,
        onQRViewCreated: _onQRViewCreated,
        overlay: QrScannerOverlayShape(
          borderColor: Colors.blue,
          borderRadius: 10,
          borderLength: 30,
          borderWidth: 10,
          cutOutSize: 250,
        ),
      ),
    );
  }
  
  void _onQRViewCreated(QRViewController controller) {
    controller.scannedDataStream.listen((scanData) {
      // Parse QR data: "CTH001:1234" format
      final parts = scanData.code?.split(':');
      if (parts?.length == 2) {
        Navigator.pop(context, {
          'work_center_code': parts[0],
          'user_secret_code': parts[1],
        });
      }
    });
  }
}
```

### **4. Local Storage & Offline Support**
```dart
class LocalStorageService {
  static const String _credentialsKey = 'user_credentials';
  static const String _lastStatusKey = 'last_status';
  
  Future<void> saveCredentials(UserCredentials credentials) async {
    await _secureStorage.write(
      key: _credentialsKey,
      value: jsonEncode(credentials.toJson()),
    );
  }
  
  Future<void> cacheLastStatus(ClockResponse response) async {
    await _preferences.setString(
      _lastStatusKey,
      jsonEncode(response.toJson()),
    );
  }
}
```

## 🔐 Funcionalidades de Seguridad

### **Encriptación (Implementación Futura)**
```dart
class EncryptionService {
  // AES encryption for sensitive data
  static String encryptCode(String code, String key) {
    // Implementar AES-256 encryption
  }
  
  // JWT token management
  static String generateSessionToken(Map<String, dynamic> payload) {
    // Implementar JWT generation
  }
}
```

### **Validaciones de Seguridad**
- Validación de certificados SSL
- Timeout de 30 segundos para requests
- Retry automático con backoff exponencial
- Validación de integridad de datos
- Limpieza automática de credenciales tras inactividad

## 🧪 Testing Strategy

### **Unit Tests**
```dart
// Test para API service
testWidgets('ClockApiService should return success response', (tester) async {
  // Mock HTTP client
  // Test successful clock-in response
  // Verify response parsing
});

// Test para Provider state management  
test('ClockProvider should update status after successful clock action', () {
  // Test state transitions
  // Verify notifyListeners calls
});
```

### **Integration Tests**
- Flujo completo de fichaje
- Manejo de errores de red
- Persistencia de datos offline
- Funcionalidad QR scanner

## 📱 Características Específicas Android

### **Permisos Requeridos (android/app/src/main/AndroidManifest.xml)**
```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.CAMERA" />
<uses-permission android:name="android.permission.WAKE_LOCK" />
```

### **Optimizaciones**
- ProGuard habilitado para ofuscación
- Splash screen nativo
- App shortcuts para fichaje rápido
- Soporte para modo oscuro
- Localización en español

## 🚀 Deployment & Distribution

### **Build Configuration**
```bash
# Debug build
flutter build apk --debug

# Release build  
flutter build apk --release --obfuscate --split-debug-info=debug-info/

# App Bundle para Play Store
flutter build appbundle --release --obfuscate --split-debug-info=debug-info/
```

### **Configuración de Signing**
```properties
# android/key.properties
storePassword=your_store_password
keyPassword=your_key_password
keyAlias=cth_app_key
storeFile=path_to_keystore.jks
```

## 📊 Monitorización y Analytics

### **Logging**
```dart
class AppLogger {
  static void logClockAction(String action, bool success) {
    print('Clock Action: $action, Success: $success, Time: ${DateTime.now()}');
  }
  
  static void logError(String error, StackTrace? stackTrace) {
    print('Error: $error\nStackTrace: $stackTrace');
  }
}
```

### **Performance Monitoring**
- Tiempo de respuesta de API
- Tasa de éxito de fichajes
- Errores de conectividad
- Uso de geolocalización

## 🎯 Características Avanzadas (Futuras)

1. **Notificaciones Push**: Recordatorios de fichaje
2. **Widget Android**: Fichaje desde home screen
3. **Modo Offline**: Fichaje sin conexión con sincronización posterior
4. **Biometría**: Autenticación con huella/face ID
5. **Dashboard Personal**: Estadísticas de fichajes
6. **Multi-idioma**: Soporte para inglés/catalán
7. **Dark Mode**: Tema oscuro completo
8. **Backup/Restore**: Configuraciones en la nube

---

## 🛠️ Instrucciones de Implementación

1. **Setup inicial**: `flutter create cth_clock_app`
2. **Añadir dependencias**: Copiar pubspec.yaml
3. **Configurar arquitectura**: Crear estructura de carpetas
4. **Implementar API client**: Conectar con endpoint CTH
5. **Desarrollar UI**: Pantallas principales
6. **Testing**: Unit e integration tests
7. **Build & Deploy**: APK/Bundle release

La aplicación debe ser **intuitiva, rápida y confiable**, enfocándose en la experiencia del usuario para operaciones de fichaje cotidianas. El diseño debe ser **limpio y profesional**, representando adecuadamente la marca CTH.