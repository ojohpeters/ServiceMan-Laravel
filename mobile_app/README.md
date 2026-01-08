# ServiceMan Mobile App (Flutter)

This is the Flutter mobile app for the ServiceMan Laravel platform.

## Features
- ✅ Authentication (Login, Register)
- ✅ Service Requests Management
- ✅ Real-time Notifications
- ✅ Payment Integration (Paystack)
- ✅ Rating System
- ✅ Profile Management
- ✅ Category Browsing
- ✅ Serviceman Assignment

## Setup Instructions

### Prerequisites
1. Install Flutter: https://flutter.dev/docs/get-started/install
2. Install Android Studio (for Android development)
3. Install VS Code or Android Studio with Flutter extensions

### Installation

1. **Navigate to mobile app directory:**
   ```bash
   cd mobile_app
   ```

2. **Install dependencies:**
   ```bash
   flutter pub get
   ```

3. **Configure API Base URL:**
   - Open `lib/config/app_config.dart`
   - Update `baseUrl` to your Laravel API URL:
     ```dart
     static const String baseUrl = 'https://serviceman.sekimbi.com/api';
     ```

4. **Run the app:**
   ```bash
   flutter run
   ```

## Building APK

### Development Build (Debug APK)
```bash
flutter build apk --debug
```
Output: `build/app/outputs/flutter-apk/app-debug.apk`

### Release Build (Optimized APK)
```bash
flutter build apk --release
```
Output: `build/app/outputs/flutter-apk/app-release.apk`

### Split APKs (by architecture - smaller file size)
```bash
flutter build apk --split-per-abi
```
Outputs:
- `build/app/outputs/flutter-apk/app-armeabi-v7a-release.apk`
- `build/app/outputs/flutter-apk/app-arm64-v8a-release.apk`
- `build/app/outputs/flutter-apk/app-x86_64-release.apk`

### App Bundle (for Google Play Store)
```bash
flutter build appbundle --release
```
Output: `build/app/outputs/bundle/release/app-release.aab`

## Project Structure

```
lib/
├── config/          # App configuration (API URLs, constants)
├── models/          # Data models
├── services/        # API services
├── screens/         # UI screens
├── widgets/         # Reusable widgets
├── utils/           # Utilities (storage, helpers)
└── main.dart        # App entry point
```

## API Endpoints Used

The app connects to your Laravel API at `/api/*` endpoints:

- **Auth**: `/auth/login`, `/auth/register`, `/auth/me`
- **Categories**: `/categories`
- **Service Requests**: `/service-requests`
- **Payments**: `/payments/initialize`, `/payments/verify`
- **Notifications**: `/notifications`
- **Ratings**: `/ratings`

## Testing

Test on connected device or emulator:
```bash
flutter devices          # List available devices
flutter run -d <device>  # Run on specific device
```

## Troubleshooting

1. **API Connection Issues:**
   - Check internet permission in `android/app/src/main/AndroidManifest.xml`
   - Verify API base URL in `lib/config/app_config.dart`
   - Check CORS settings in Laravel `config/cors.php`

2. **Build Errors:**
   ```bash
   flutter clean
   flutter pub get
   flutter build apk --release
   ```

3. **Dependencies:**
   ```bash
   flutter pub upgrade
   ```

## Deployment

1. **Google Play Store:**
   - Build app bundle: `flutter build appbundle --release`
   - Upload `app-release.aab` to Google Play Console

2. **Direct APK Distribution:**
   - Build release APK: `flutter build apk --release`
   - Share `app-release.apk` file

## Support

For issues, check:
- Flutter documentation: https://flutter.dev/docs
- Laravel API documentation: Check `routes/api.php`

