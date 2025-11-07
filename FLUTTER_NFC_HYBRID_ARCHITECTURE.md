# Flutter CTH - Arquitectura Híbrida con NFC

## 🏗️ **Arquitectura General**

```
┌─────────────────────────────────────────────────────────────┐
│                   Flutter Application                       │
├─────────────────────┬─────────────────────┬─────────────────┤
│    NFC Module       │   Clock Module      │   WebView Module │
│   (Native Dart)     │  (Native Dart)      │  (Hybrid)       │
└─────────────────────┴─────────────────────┴─────────────────┘
           │                      │                      │
           ▼                      ▼                      ▼
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│   NFC Reader    │  │   Clock API     │  │   Web Views     │
│   (Hardware)    │  │  (REST API)     │  │  (Laravel)      │
└─────────────────┘  └─────────────────┘  └─────────────────┘
```

## 📱 **Flujo de Trabajo NFC**

### **1. Proceso de Autenticación con NFC**

```dart
// 1. Lectura NFC al inicio de la aplicación
class NFCService {
  static Future<WorkCenter?> scanWorkCenter() async {
    if (!await NfcManager.instance.isAvailable()) {
      throw NFCNotAvailableException();
    }
    
    Completer<WorkCenter?> completer = Completer();
    
    NfcManager.instance.startSession(
      onDiscovered: (NfcTag tag) async {
        try {
          final ndef = Ndef.from(tag);
          if (ndef?.cachedMessage?.records.isNotEmpty == true) {
            final record = ndef!.cachedMessage!.records.first;
            final text = utf8.decode(record.payload.skip(3).toList());
            
            // Formato esperado: "CTH:OC-001:Oficina Central"
            if (text.startsWith('CTH:')) {
              final parts = text.split(':');
              if (parts.length >= 2) {
                final workCenter = WorkCenter(
                  code: parts[1],
                  name: parts.length > 2 ? parts[2] : parts[1],
                );
                completer.complete(workCenter);
                return;
              }
            }
          }
          completer.complete(null);
        } catch (e) {
          completer.completeError(e);
        }
      },
    );
    
    return completer.future;
  }
}
```

### **2. Modelos de Datos**

```dart
class WorkCenter {
  final String code;
  final String name;
  
  const WorkCenter({
    required this.code,
    required this.name,
  });
  
  Map<String, dynamic> toJson() => {
    'code': code,
    'name': name,
  };
}

class User {
  final String code;
  final String name;
  
  const User({
    required this.code,
    required this.name,
  });
}

class ClockStatus {
  final String nextAction; // 'entrada' | 'salida'
  final bool canClock;
  final TodayStats todayStats;
  final DateTime currentTime;
  
  const ClockStatus({
    required this.nextAction,
    required this.canClock,
    required this.todayStats,
    required this.currentTime,
  });
}

class TodayStats {
  final int entriesCount;
  final int exitsCount;
  final String workedHours;
  final String currentStatus;
  final ClockAction? lastAction;
  
  const TodayStats({
    required this.entriesCount,
    required this.exitsCount,
    required this.workedHours,
    required this.currentStatus,
    this.lastAction,
  });
}
```

## 🔌 **API Integration**

### **1. Clock Service**

```dart
class ClockService {
  static const String baseUrl = 'https://your-domain.com/api/v1/mobile';
  
  // Realizar fichaje
  static Future<ClockResponse> performClock({
    required String workCenterCode,
    required String userCode,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/clock'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'work_center_code': workCenterCode,
        'user_code': userCode,
      }),
    );
    
    if (response.statusCode == 200) {
      return ClockResponse.fromJson(jsonDecode(response.body));
    } else {
      throw ClockException('Error en fichaje: ${response.body}');
    }
  }
  
  // Obtener estado actual
  static Future<ClockStatus> getStatus({
    required String workCenterCode,
    required String userCode,
  }) async {
    final response = await http.get(
      Uri.parse('$baseUrl/status')
          .replace(queryParameters: {
        'work_center_code': workCenterCode,
        'user_code': userCode,
      }),
    );
    
    if (response.statusCode == 200) {
      return ClockStatus.fromJson(jsonDecode(response.body)['data']);
    } else {
      throw ClockException('Error obteniendo estado: ${response.body}');
    }
  }
  
  // Sincronizar datos offline
  static Future<SyncResponse> syncOfflineData({
    required String workCenterCode,
    required String userCode,
    required List<OfflineClockEvent> events,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/sync'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'work_center_code': workCenterCode,
        'user_code': userCode,
        'offline_events': events.map((e) => e.toJson()).toList(),
      }),
    );
    
    if (response.statusCode == 200) {
      return SyncResponse.fromJson(jsonDecode(response.body));
    } else {
      throw SyncException('Error en sincronización: ${response.body}');
    }
  }
}
```

### **2. WebView Integration**

```dart
class WebViewService {
  // Abrir WebView con autenticación automática
  static Future<void> openAuthenticatedWebView({
    required BuildContext context,
    required WorkCenter workCenter,
    required User user,
    required String path, // '/history', '/schedule', '/reports', etc.
  }) async {
    final url = Uri.parse('https://your-domain.com/mobile$path').replace(
      queryParameters: {
        'work_center_code': workCenter.code,
        'work_center_name': workCenter.name,
        'user_code': user.code,
        'auto_auth': 'true',
      },
    );
    
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => CTHWebView(
          url: url.toString(),
          title: _getPageTitle(path),
          workCenter: workCenter,
          user: user,
        ),
      ),
    );
  }
  
  static String _getPageTitle(String path) {
    switch (path) {
      case '/history': return 'Historial';
      case '/schedule': return 'Horarios';
      case '/profile': return 'Perfil';
      case '/reports': return 'Informes';
      default: return 'CTH Mobile';
    }
  }
}

class CTHWebView extends StatefulWidget {
  final String url;
  final String title;
  final WorkCenter workCenter;
  final User user;
  
  const CTHWebView({
    Key? key,
    required this.url,
    required this.title,
    required this.workCenter,
    required this.user,
  }) : super(key: key);
  
  @override
  _CTHWebViewState createState() => _CTHWebViewState();
}

class _CTHWebViewState extends State<CTHWebView> {
  late final WebViewController controller;
  
  @override
  void initState() {
    super.initState();
    
    controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageFinished: (String url) {
            // Inyectar datos de autenticación en localStorage
            controller.runJavaScript('''
              localStorage.setItem('cth_work_center_code', '${widget.workCenter.code}');
              localStorage.setItem('cth_work_center_name', '${widget.workCenter.name}');
              localStorage.setItem('cth_user_code', '${widget.user.code}');
              
              // Llamar función de autenticación si existe
              if (window.setWorkCenter) {
                window.setWorkCenter('${widget.workCenter.code}', '${widget.workCenter.name}');
              }
            ''');
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.url));
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.title),
        backgroundColor: Colors.blue[600],
        foregroundColor: Colors.white,
      ),
      body: WebViewWidget(controller: controller),
    );
  }
}
```

## 🏠 **Pantalla Principal**

```dart
class ClockScreen extends StatefulWidget {
  final WorkCenter workCenter;
  final User user;
  
  const ClockScreen({
    Key? key,
    required this.workCenter,
    required this.user,
  }) : super(key: key);
  
  @override
  _ClockScreenState createState() => _ClockScreenState();
}

class _ClockScreenState extends State<ClockScreen> {
  ClockStatus? clockStatus;
  bool isLoading = false;
  
  @override
  void initState() {
    super.initState();
    _loadStatus();
  }
  
  Future<void> _loadStatus() async {
    setState(() => isLoading = true);
    try {
      final status = await ClockService.getStatus(
        workCenterCode: widget.workCenter.code,
        userCode: widget.user.code,
      );
      setState(() => clockStatus = status);
    } catch (e) {
      _showError('Error cargando estado: $e');
    } finally {
      setState(() => isLoading = false);
    }
  }
  
  Future<void> _performClock() async {
    setState(() => isLoading = true);
    try {
      final response = await ClockService.performClock(
        workCenterCode: widget.workCenter.code,
        userCode: widget.user.code,
      );
      
      _showSuccess('${response.data.action} registrada correctamente');
      await _loadStatus(); // Recargar estado
      
    } catch (e) {
      _showError('Error en fichaje: $e');
    } finally {
      setState(() => isLoading = false);
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('CTH Fichaje'),
        backgroundColor: Colors.blue[600],
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadStatus,
          ),
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            // Work Center Info
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Row(
                  children: [
                    const Icon(Icons.business, color: Colors.blue),
                    const SizedBox(width: 12),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          widget.workCenter.name,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        Text(
                          widget.workCenter.code,
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            
            const SizedBox(height: 16),
            
            // User Info
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Row(
                  children: [
                    const Icon(Icons.person, color: Colors.green),
                    const SizedBox(width: 12),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          widget.user.name,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        Text(
                          'ID: ${widget.user.code}',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            
            const SizedBox(height: 24),
            
            // Clock Status
            if (clockStatus != null) ...[
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    children: [
                      Text(
                        'Estado Actual',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Colors.grey[800],
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        clockStatus!.todayStats.currentStatus.toUpperCase(),
                        style: TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: clockStatus!.todayStats.currentStatus == 'trabajando' 
                              ? Colors.green 
                              : Colors.orange,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Horas trabajadas hoy: ${clockStatus!.todayStats.workedHours}',
                        style: const TextStyle(fontSize: 16),
                      ),
                    ],
                  ),
                ),
              ),
              
              const SizedBox(height: 24),
              
              // Clock Button
              SizedBox(
                width: double.infinity,
                height: 60,
                child: ElevatedButton(
                  onPressed: isLoading ? null : _performClock,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: clockStatus!.nextAction == 'entrada' 
                        ? Colors.green 
                        : Colors.red,
                    foregroundColor: Colors.white,
                  ),
                  child: isLoading
                      ? const CircularProgressIndicator(color: Colors.white)
                      : Text(
                          clockStatus!.nextAction == 'entrada' 
                              ? 'FICHAR ENTRADA' 
                              : 'FICHAR SALIDA',
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                ),
              ),
            ],
            
            const Spacer(),
            
            // WebView Navigation
            Column(
              children: [
                const Text(
                  'Más funciones',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    _buildWebViewButton(
                      icon: Icons.history,
                      label: 'Historial',
                      path: '/history',
                    ),
                    _buildWebViewButton(
                      icon: Icons.schedule,
                      label: 'Horarios',
                      path: '/schedule',
                    ),
                    _buildWebViewButton(
                      icon: Icons.assessment,
                      label: 'Informes',
                      path: '/reports',
                    ),
                    _buildWebViewButton(
                      icon: Icons.person,
                      label: 'Perfil',
                      path: '/profile',
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
  
  Widget _buildWebViewButton({
    required IconData icon,
    required String label,
    required String path,
  }) {
    return Column(
      children: [
        IconButton(
          onPressed: () => WebViewService.openAuthenticatedWebView(
            context: context,
            workCenter: widget.workCenter,
            user: widget.user,
            path: path,
          ),
          icon: Icon(icon, size: 32),
          style: IconButton.styleFrom(
            backgroundColor: Colors.blue[50],
            padding: const EdgeInsets.all(16),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: const TextStyle(fontSize: 12),
        ),
      ],
    );
  }
  
  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }
  
  void _showSuccess(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.green,
      ),
    );
  }
}
```

## 🏁 **Pantalla de Inicio con NFC**

```dart
class NFCStartScreen extends StatefulWidget {
  @override
  _NFCStartScreenState createState() => _NFCStartScreenState();
}

class _NFCStartScreenState extends State<NFCStartScreen> {
  bool isScanning = false;
  String statusMessage = 'Acerca tu dispositivo a la etiqueta NFC';
  
  @override
  void initState() {
    super.initState();
    _checkNFCAvailability();
  }
  
  Future<void> _checkNFCAvailability() async {
    final isAvailable = await NfcManager.instance.isAvailable();
    if (!isAvailable) {
      setState(() {
        statusMessage = 'NFC no disponible en este dispositivo';
      });
    }
  }
  
  Future<void> _startNFCScan() async {
    setState(() {
      isScanning = true;
      statusMessage = 'Acerca tu dispositivo a la etiqueta NFC...';
    });
    
    try {
      final workCenter = await NFCService.scanWorkCenter();
      
      if (workCenter != null) {
        await NfcManager.instance.stopSession();
        
        // Navegar a pantalla de login con código de centro
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => UserLoginScreen(workCenter: workCenter),
          ),
        );
      } else {
        setState(() {
          statusMessage = 'Etiqueta NFC no válida. Inténtalo de nuevo.';
          isScanning = false;
        });
      }
    } catch (e) {
      setState(() {
        statusMessage = 'Error: $e';
        isScanning = false;
      });
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Colors.blue[600]!, Colors.blue[800]!],
          ),
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Logo
                Icon(
                  Icons.nfc,
                  size: 100,
                  color: Colors.white,
                ),
                
                const SizedBox(height: 24),
                
                Text(
                  'CTH Mobile',
                  style: TextStyle(
                    fontSize: 32,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                
                const SizedBox(height: 8),
                
                Text(
                  'Sistema de Control de Tiempo y Horarios',
                  style: TextStyle(
                    fontSize: 16,
                    color: Colors.white.withOpacity(0.9),
                  ),
                  textAlign: TextAlign.center,
                ),
                
                const SizedBox(height: 48),
                
                // NFC Status
                Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Column(
                    children: [
                      Icon(
                        isScanning ? Icons.nfc : Icons.tap_and_play,
                        size: 48,
                        color: isScanning ? Colors.blue : Colors.grey[600],
                      ),
                      
                      const SizedBox(height: 16),
                      
                      Text(
                        statusMessage,
                        style: TextStyle(
                          fontSize: 16,
                          color: Colors.grey[800],
                        ),
                        textAlign: TextAlign.center,
                      ),
                      
                      const SizedBox(height: 24),
                      
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: isScanning ? null : _startNFCScan,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.blue[600],
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                          child: isScanning
                              ? const CircularProgressIndicator(color: Colors.white)
                              : const Text(
                                  'Escanear NFC',
                                  style: TextStyle(fontSize: 16),
                                ),
                        ),
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(height: 32),
                
                // Manual entry option
                TextButton(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => ManualEntryScreen(),
                      ),
                    );
                  },
                  child: Text(
                    'Introducir código manualmente',
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.9),
                      decoration: TextDecoration.underline,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
```

## 📦 **Dependencias Requeridas**

```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # HTTP requests
  http: ^1.1.0
  
  # NFC functionality
  nfc_manager: ^3.3.0
  
  # WebView
  webview_flutter: ^4.4.2
  
  # State management
  provider: ^6.1.1
  
  # Local storage
  shared_preferences: ^2.2.2
  
  # JSON serialization
  json_annotation: ^4.8.1

dev_dependencies:
  # JSON code generation
  json_serializable: ^6.7.1
  build_runner: ^2.4.7
```

## 🔧 **Configuración Android**

```xml
<!-- android/app/src/main/AndroidManifest.xml -->
<uses-permission android:name="android.permission.NFC" />
<uses-feature android:name="android.hardware.nfc" android:required="true" />

<application>
    <activity>
        <intent-filter>
            <action android:name="android.nfc.action.NDEF_DISCOVERED" />
            <category android:name="android.intent.category.DEFAULT" />
            <data android:mimeType="text/plain" />
        </intent-filter>
    </activity>
</application>
```

## ⚡ **Configuración iOS**

```xml
<!-- ios/Runner/Info.plist -->
<key>NFCReaderUsageDescription</key>
<string>Esta aplicación necesita acceso a NFC para leer las etiquetas de los centros de trabajo</string>

<key>com.apple.developer.nfc.readersession.formats</key>
<array>
    <string>NDEF</string>
</array>
```

## 🧪 **Testing y Validación**

### **Flujo Completo de Testing:**

1. **NFC Reading**: Probar lectura de etiquetas con formato CTH:CODE:NAME
2. **API Integration**: Validar endpoints `/clock`, `/status`, `/sync`
3. **WebView Auth**: Verificar paso de credenciales a WebView
4. **Offline Support**: Probar funcionalidad sin conexión
5. **Error Handling**: Validar manejo de errores NFC y de red

Esta arquitectura proporciona una solución completa que combina:
- ✅ **Lectura NFC nativa** para códigos de centro de trabajo
- ✅ **API REST** para fichajes rápidos y estado
- ✅ **WebView híbrido** para funcionalidades complejas
- ✅ **Autenticación automática** entre componentes
- ✅ **Soporte offline** preparado para el futuro

El resultado es una aplicación móvil profesional que optimiza la experiencia del usuario usando lo mejor de cada tecnología.