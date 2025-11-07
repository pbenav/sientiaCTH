# 📱 Instrucciones de Compilación - CTH Flutter App

## 🚀 **Creación del Proyecto Flutter**

### **1. Crear nuevo proyecto Flutter**
```bash
# Crear proyecto
flutter create cth_mobile
cd cth_mobile

# Verificar instalación Flutter
flutter doctor
```

### **2. Configurar dependencias**
Editar `pubspec.yaml`:

```yaml
name: cth_mobile
description: CTH - Sistema de Control de Tiempo y Horarios
version: 1.0.0+1

environment:
  sdk: '>=3.0.0 <4.0.0'

dependencies:
  flutter:
    sdk: flutter
  
  # HTTP requests para API
  http: ^1.1.0
  
  # NFC functionality
  nfc_manager: ^3.3.0
  
  # WebView para vistas complejas
  webview_flutter: ^4.4.2
  
  # State management
  provider: ^6.1.1
  
  # Local storage
  shared_preferences: ^2.2.2
  
  # JSON serialization
  json_annotation: ^4.8.1
  
  # UI helpers
  cupertino_icons: ^1.0.2

dev_dependencies:
  flutter_test:
    sdk: flutter
  flutter_lints: ^3.0.0
  
  # JSON code generation
  json_serializable: ^6.7.1
  build_runner: ^2.4.7

flutter:
  uses-material-design: true
  
  # Assets (logos, iconos, etc.)
  assets:
    - assets/images/
    - assets/icons/
  
  # Fuentes personalizadas si las necesitas
  # fonts:
  #   - family: CustomFont
  #     fonts:
  #       - asset: fonts/CustomFont-Regular.ttf
```

### **3. Instalar dependencias**
```bash
flutter pub get
```

## ⚙️ **Configuración Android**

### **1. Permisos NFC**
Editar `android/app/src/main/AndroidManifest.xml`:

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">
    
    <!-- Permisos NFC -->
    <uses-permission android:name="android.permission.NFC" />
    <uses-permission android:name="android.permission.INTERNET" />
    
    <!-- Feature NFC requerido -->
    <uses-feature 
        android:name="android.hardware.nfc" 
        android:required="true" />
    
    <application
        android:label="CTH Mobile"
        android:name="${applicationName}"
        android:icon="@mipmap/ic_launcher">
        
        <activity
            android:name=".MainActivity"
            android:exported="true"
            android:launchMode="singleTop"
            android:theme="@style/LaunchTheme"
            android:configChanges="orientation|keyboardHidden|keyboard|screenSize|smallestScreenSize|locale|layoutDirection|fontScale|screenLayout|density|uiMode"
            android:hardwareAccelerated="true"
            android:windowSoftInputMode="adjustResize">
            
            <!-- Intent filter principal -->
            <intent-filter android:autoVerify="true">
                <action android:name="android.intent.action.MAIN"/>
                <category android:name="android.intent.category.LAUNCHER"/>
            </intent-filter>
            
            <!-- Intent filter para NFC -->
            <intent-filter>
                <action android:name="android.nfc.action.NDEF_DISCOVERED" />
                <category android:name="android.intent.category.DEFAULT" />
                <data android:mimeType="text/plain" />
            </intent-filter>
            
            <!-- Intent filter para tags NFC genéricos -->
            <intent-filter>
                <action android:name="android.nfc.action.TAG_DISCOVERED" />
                <category android:name="android.intent.category.DEFAULT" />
            </intent-filter>
        </activity>
        
        <!-- Meta-data para generar baseline profile -->
        <meta-data
          android:name="io.flutter.embedding.android.NormalTheme"
          android:resource="@style/NormalTheme"
          />
    </application>
</manifest>
```

### **2. Configuración de red (opcional para desarrollo)**
Crear/editar `android/app/src/main/res/xml/network_security_config.xml`:

```xml
<?xml version="1.0" encoding="utf-8"?>
<network-security-config>
    <!-- Para desarrollo local -->
    <domain-config cleartextTrafficPermitted="true">
        <domain includeSubdomains="true">localhost</domain>
        <domain includeSubdomains="true">10.0.2.2</domain>
        <domain includeSubdomains="true">192.168.1.0/24</domain>
    </domain-config>
</network-security-config>
```

Y referenciarla en AndroidManifest.xml:
```xml
<application
    android:networkSecurityConfig="@xml/network_security_config"
    ...>
```

### **3. Configuración Gradle**
Verificar `android/app/build.gradle`:

```gradle
android {
    namespace "com.cth.mobile"
    compileSdkVersion 34
    ndkVersion flutter.ndkVersion

    compileOptions {
        sourceCompatibility JavaVersion.VERSION_1_8
        targetCompatibility JavaVersion.VERSION_1_8
    }

    defaultConfig {
        applicationId "com.cth.mobile"
        minSdkVersion 21  // NFC requiere API 21+
        targetSdkVersion 34
        versionCode flutterVersionCode.toInteger()
        versionName flutterVersionName
    }

    buildTypes {
        release {
            signingConfig signingConfigs.debug
        }
    }
}
```

## 🍎 **Configuración iOS**

### **1. Permisos y capacidades**
Editar `ios/Runner/Info.plist`:

```xml
<dict>
    <!-- Otros valores existentes... -->
    
    <!-- Descripción del uso de NFC -->
    <key>NFCReaderUsageDescription</key>
    <string>Esta aplicación necesita acceso a NFC para leer las etiquetas de los centros de trabajo CTH</string>
    
    <!-- Formatos NFC soportados -->
    <key>com.apple.developer.nfc.readersession.formats</key>
    <array>
        <string>NDEF</string>
        <string>TAG</string>
    </array>
    
    <!-- Permitir HTTP para desarrollo -->
    <key>NSAppTransportSecurity</key>
    <dict>
        <key>NSAllowsArbitraryLoads</key>
        <true/>
        <key>NSExceptionDomains</key>
        <dict>
            <key>localhost</key>
            <dict>
                <key>NSExceptionAllowsInsecureHTTPLoads</key>
                <true/>
            </dict>
        </dict>
    </dict>
</dict>
```

### **2. Capacidades NFC en Xcode**
1. Abrir `ios/Runner.xcworkspace` en Xcode
2. Seleccionar el target "Runner"
3. Ir a "Signing & Capabilities"
4. Añadir capability "Near Field Communication Tag Reading"
5. Verificar que el Team y Bundle ID estén configurados

### **3. Entitlements**
Crear/verificar `ios/Runner/Runner.entitlements`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>com.apple.developer.nfc.readersession.formats</key>
    <array>
        <string>NDEF</string>
        <string>TAG</string>
    </array>
</dict>
</plist>
```

## 📁 **Estructura de Archivos**

### **Crear estructura recomendada:**
```bash
lib/
├── main.dart
├── models/
│   ├── work_center.dart
│   ├── user.dart
│   ├── clock_status.dart
│   └── api_response.dart
├── services/
│   ├── nfc_service.dart
│   ├── clock_service.dart
│   ├── webview_service.dart
│   └── storage_service.dart
├── screens/
│   ├── nfc_start_screen.dart
│   ├── user_login_screen.dart
│   ├── clock_screen.dart
│   ├── webview_screen.dart
│   └── manual_entry_screen.dart
├── widgets/
│   ├── loading_widget.dart
│   ├── error_widget.dart
│   └── clock_button.dart
└── utils/
    ├── constants.dart
    ├── exceptions.dart
    └── helpers.dart
```

## 🔨 **Comandos de Compilación**

### **1. Desarrollo y Testing**
```bash
# Ejecutar en modo debug (desarrollo)
flutter run

# Ejecutar en dispositivo específico
flutter devices  # Listar dispositivos
flutter run -d <device-id>

# Hot reload durante desarrollo
# Presionar 'r' en la terminal o Ctrl+S en el editor

# Hot restart
# Presionar 'R' en la terminal o Ctrl+Shift+S
```

### **2. Compilación para Testing**
```bash
# Compilar APK de debug para Android
flutter build apk --debug

# Compilar APK de release para Android
flutter build apk --release

# Compilar AAB (recomendado para Play Store)
flutter build appbundle --release

# Compilar para iOS (requiere macOS y Xcode)
flutter build ios --release
```

### **3. Instalación en Dispositivos**
```bash
# Instalar APK debug en dispositivo Android conectado
flutter install

# Instalar APK específico
adb install build/app/outputs/flutter-apk/app-debug.apk

# Para iOS, usar Xcode:
# Abrir ios/Runner.xcworkspace y hacer Build & Run
```

## 🧪 **Testing y Debugging**

### **1. Testing básico**
```bash
# Ejecutar tests unitarios
flutter test

# Ejecutar con cobertura
flutter test --coverage

# Testing en dispositivos reales
flutter drive --target=test_driver/app.dart
```

### **2. Debugging NFC**
```bash
# Verificar que NFC funciona
flutter run --verbose

# Log específico para NFC (en código Dart)
print('NFC disponible: ${await NfcManager.instance.isAvailable()}');

# Debugging en Android
adb logcat | grep -i nfc
```

### **3. Testing API connections**
```bash
# Verificar conectividad con el servidor
# Desde el dispositivo/emulador, probar:
curl -X GET "http://YOUR_SERVER_IP:8000/api/v1/mobile/status?work_center_code=OC-001&user_code=1232222"
```

## 📦 **Build para Producción**

### **1. Preparar release Android**
```bash
# Generar keystore para signing (solo primera vez)
keytool -genkey -v -keystore ~/cth-release-key.jks -keyalg RSA -keysize 2048 -validity 10000 -alias cth

# Configurar signing en android/app/build.gradle
# Añadir antes de android block:
```

```gradle
def keystoreProperties = new Properties()
def keystorePropertiesFile = rootProject.file('key.properties')
if (keystorePropertiesFile.exists()) {
    keystoreProperties.load(new FileInputStream(keystorePropertiesFile))
}

android {
    signingConfigs {
        release {
            keyAlias keystoreProperties['keyAlias']
            keyPassword keystoreProperties['keyPassword']
            storeFile keystoreProperties['storeFile'] ? file(keystoreProperties['storeFile']) : null
            storePassword keystoreProperties['storePassword']
        }
    }
    buildTypes {
        release {
            signingConfig signingConfigs.release
        }
    }
}
```

### **2. Crear key.properties**
```bash
# En android/key.properties
storePassword=your_keystore_password
keyPassword=your_key_password
keyAlias=cth
storeFile=../cth-release-key.jks
```

### **3. Build final**
```bash
# Android AAB para Play Store
flutter build appbundle --release --build-name=1.0.0 --build-number=1

# Android APK para distribución directa
flutter build apk --release --build-name=1.0.0 --build-number=1

# iOS para App Store (en macOS)
flutter build ios --release
# Luego usar Xcode para subir a App Store Connect
```

## 🔧 **Variables de Entorno**

### **Crear configuración por ambiente:**

`lib/utils/config.dart`:
```dart
class Config {
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://localhost:8000/api/v1/mobile',
  );
  
  static const String webBaseUrl = String.fromEnvironment(
    'WEB_BASE_URL', 
    defaultValue: 'http://localhost:8000/mobile',
  );
  
  static const bool isProduction = bool.fromEnvironment('PRODUCTION');
}
```

### **Ejecutar con variables:**
```bash
# Desarrollo
flutter run --dart-define=API_BASE_URL=http://192.168.1.100:8000/api/v1/mobile

# Producción
flutter build apk --release \
  --dart-define=API_BASE_URL=https://cth.yourcompany.com/api/v1/mobile \
  --dart-define=WEB_BASE_URL=https://cth.yourcompany.com/mobile \
  --dart-define=PRODUCTION=true
```

## ✅ **Checklist de Compilación**

- [ ] ✅ Dependencias instaladas (`flutter pub get`)
- [ ] ✅ Permisos NFC configurados (Android + iOS)
- [ ] ✅ Network security config para desarrollo
- [ ] ✅ Estructura de archivos creada
- [ ] ✅ Variables de entorno configuradas
- [ ] ✅ Keystore para release (Android)
- [ ] ✅ Signing configurado (iOS)
- [ ] ✅ Testing en dispositivo real con NFC
- [ ] ✅ Conectividad API verificada
- [ ] ✅ Build release exitoso

## 🚨 **Troubleshooting Común**

### **NFC no funciona:**
```bash
# Verificar permisos
flutter run --verbose | grep -i nfc

# En Android: verificar en AndroidManifest.xml
# En iOS: verificar Info.plist y entitlements
```

### **API no conecta:**
```bash
# Verificar network security config
# Usar IP real del servidor, no localhost en dispositivo físico
# Ejemplo: 192.168.1.100:8000 en lugar de localhost:8000
```

### **Build falla:**
```bash
# Limpiar cache
flutter clean
flutter pub get

# Para Android
cd android && ./gradlew clean

# Para iOS  
cd ios && rm -rf Pods/ && pod install
```

Con estas instrucciones tendrás la aplicación Flutter compilada y funcionando con capacidades NFC completas. ¿Necesitas que detalle algún paso específico?