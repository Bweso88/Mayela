/// Configuration injectée à la compilation :
/// `flutter run --dart-define=API_BASE_URL=https://api.mayela.cg/api/v1`
abstract final class Env {
  /// Par défaut : backend local vu depuis l'émulateur Android (10.0.2.2 = machine hôte).
  static const apiBaseUrl = String.fromEnvironment('API_BASE_URL', defaultValue: 'http://10.0.2.2:8000/api/v1');

  static const appName = 'Mayela';
}
