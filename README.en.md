# CTH - Time and Schedule Control

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/pbenav/cth/releases)
[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple.svg)](https://php.net)

[🇪🇸 Versión en Español](README.md)

CTH is a comprehensive open-source solution for time tracking and business productivity management, consisting of a powerful web platform (Laravel + Livewire) and a cross-platform mobile application (Flutter).

## ✨ Key Features

### 🎯 Time Management
- **SmartClockIn**: Intelligent system that automatically detects the next action (clock in, pause, clock out)
- **Flexible Schedules**: Support for shifts, grace periods, and time slot validation
- **Exceptional Clock-ins**: Secure tokens for clock-ins outside regular hours
- **Pause System**: Complete management of breaks within the workday

### 👥 Business Management
- **Multi-team**: Multi-tenant architecture to manage multiple departments or companies
- **Permission System**: 60+ granular permissions with 4 predefined roles
- **Work Centers**: Management of multiple locations with geolocation
- **Holiday Management**: Automatic calendar with official holidays API

### 📊 Reports and Analytics
- **Professional Reports**: Export to PDF (Browsershot), Excel, and CSV
- **Interactive Dashboard**: Real-time statistics with Chart.js
- **Visual KPIs**: Performance indicators and worked vs. scheduled hours
- **Complete History**: Advanced filtering of clock-ins and events

### 📱 Mobile Application
- **Cross-platform Flutter**: Native apps for Android and iOS
- **NFC Clock-in**: Support for NFC tags at work centers
- **Complete RESTful API**: 15+ endpoints with Sanctum authentication
- **Offline Sync**: Works without connection

### 🌍 Internationalization
- **Multi-language**: Full support for Spanish and English
- **Bilingual Documentation**: Technical manuals in ES/EN
- **Regional Localization**: Adapted date, time, and currency formats

## 📚 Documentation

Detailed documentation is available in the `public/docs` directory and is automatically filtered in the application according to the user's language preference:

### Español 🇪🇸
- [User Manual](public/docs/es/USER_MANUAL.md) - Complete guide for end users
- [Developer Manual](public/docs/es/DEVELOPER_MANUAL.md) - Technical documentation for developers
- [API Reference](public/docs/es/REFERENCIA_API.md) - Mobile API endpoints
- [Changelog](public/docs/es/CHANGELOG.md) - Complete project history

### English 🇺🇸
- [User Manual](public/docs/en/USER_MANUAL.md) - Complete guide for end users
- [Developer Manual](public/docs/en/DEVELOPER_MANUAL.md) - Technical documentation for developers
- [API Reference](public/docs/en/API_REFERENCE.md) - Mobile API endpoints
- [Changelog](public/docs/en/CHANGELOG.md) - Complete project history

### Technical Documentation 🔧
- [Permission System Analysis](public/docs/technical/PERMISSION_SYSTEM_ANALYSIS.md) - Deep dive into permissions architecture
- [Permission System Guide](public/docs/technical/PERMISSION_SYSTEM_GUIDE.md) - Developer guide for permissions
- [Performance Optimization](public/docs/technical/PERFORMANCE_OPTIMIZATION.md) - Performance improvements and benchmarks

---

## 🛠️ Installation and Setup

### System Requirements
- **PHP** ^8.1 (recommended 8.2+)
- **Composer** 2.x
- **Node.js** 18.x or higher
- **npm** or **yarn**
- **MySQL** 8.0+ / **MariaDB** 10.5+
- **PHP Extensions**: PDO, Mbstring, OpenSSL, Tokenizer, XML, Ctype, JSON, BCMath, GD

### Optional Requirements
- **Node.js** with **Puppeteer** for high-quality PDF generation
- **Redis** for cache and queues (improves performance)
- **Supervisor** for queue workers in production

### Installation Steps

1. **Clone the repository**:
   ```bash
   git clone https://github.com/pbenav/cth.git
   cd cth
   ```

2. **Install server dependencies (Backend)**:
   ```bash
   composer install
   ```

3. **Install frontend dependencies**:
   ```bash
   npm install && npm run build
   ```

4. **Environment Configuration**:
   Copy the example file and configure your database credentials:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   
   Edit `.env` and set your database connection:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cth
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Database and Initialization**:
   The system includes a consolidated migration that automatically creates the global administrator (`admin@cth.local` / `admin123`) and the "Welcome" team:
   ```bash
   php artisan migrate
   ```
   
   **⚠️ Important**: Change the default admin password immediately after first login!

6. **Run the development server**:
   ```bash
   php artisan serve
   ```
   
   Access the application at `http://localhost:8000`

7. **Optional - Queue Workers** (for background jobs):
   ```bash
   php artisan queue:work
   ```

---

## 📱 Mobile Application (Flutter)

The mobile application source code is located in the `cth_mobile/` folder. To generate the installation package:

1. Navigate to the folder: `cd cth_mobile`
2. Install dependencies: `flutter pub get`
3. Build the package (APK): `flutter build apk`

The generated APK will be located at `build/app/outputs/flutter-apk/app-release.apk`

For iOS:
```bash
flutter build ios
```

---

## 🔒 Security

CTH implements multiple layers of security:

- **Robust Authentication**: Laravel Sanctum with optional 2FA support
- **CSRF Protection**: On all forms
- **HTML Sanitization**: HTMLPurifier for user content
- **Rate Limiting**: Protection against brute force attacks
- **Complete Audit**: Logging of critical actions
- **Secure Tokens**: For exceptional clock-ins and API

### Reporting Vulnerabilities

If you discover a security vulnerability, please **DO NOT** open a public issue. Contact the project maintainer directly for responsible disclosure.

---

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the project
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add: amazing new feature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Contribution Guidelines

- Follow existing code style (PSR-12 for PHP)
- Write tests for new features
- Update documentation as needed
- Ensure all tests pass before submitting PR

---

## 👨‍💻 Author

**Pablo Benavides** ([@pbenav](https://github.com/pbenav))

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework that powers this project
- [Livewire](https://laravel-livewire.com) - For reactive components without JavaScript
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS framework
- [FullCalendar](https://fullcalendar.io) - Interactive calendar library
- [Flutter](https://flutter.dev) - For the cross-platform mobile application
- Open source community for their amazing tools

---

## 📊 Project Status

- ✅ **Stable version**: 1.0.0
- ✅ **Production**: System tested in real environments
- ✅ **Active maintenance**: Regular updates and fixes
- 🚀 **In development**: New features on the roadmap

For complete change history, see the [CHANGELOG](public/docs/en/CHANGELOG.md).

---

## 📞 Support

- **Documentation**: Check the manuals in `public/docs/`
- **Issues**: Report bugs or request features at [GitHub Issues](https://github.com/pbenav/cth/issues)
- **Discussions**: Join [Discussions](https://github.com/pbenav/cth/discussions) for general questions

---

**⭐ If you find this project useful, consider giving it a star on GitHub**

---

## 📄 License

CTH is **free and open-source software** distributed under the [MIT License](LICENSE).

This means you can:
- ✅ **Use** the software for any purpose (personal or commercial)
- ✅ **Modify** the code according to your needs
- ✅ **Distribute** copies of the software
- ✅ **Sublicense** and sell copies of the modified software

The only condition is that you include the copyright notice and MIT license in all copies or substantial portions of the software.

**Author**: pbenav (2022-2026)  
**Full license**: See [LICENSE](LICENSE) file

---

## 🔒 Security

If you discover any security vulnerability, please **DO NOT** publish it in GitHub Issues. Instead, contact the development team directly so it can be addressed responsibly.

---

*© 2022-2026 pbenav - CTH is free software under MIT license*
