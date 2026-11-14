import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/storage/token_storage.dart';
import 'models.dart';

final authRepositoryProvider = Provider<AuthRepository>(
  (ref) => ApiAuthRepository(ref.watch(apiClientProvider), ref.watch(tokenStorageProvider)),
);

abstract interface class AuthRepository {
  Future<AppUser?> restoreSession();
  Future<AppUser> loginWithPassword({required String identifier, required String password});
  Future<AppUser> loginWithStudentCode({required String code, required String pin});
  Future<AppUser> registerParent({
    required String name,
    String? email,
    String? phone,
    required String password,
    required bool acceptTerms,
  });
  Future<StudentProfile> addChild({
    required String firstName,
    required String lastName,
    required DateTime birthDate,
    required String pin,
    required bool parentalConsent,
    String countryCode,
    int? gradeId,
    int? trackId,
  });
  Future<AppUser> me();
  Future<void> logout();
}

class ApiAuthRepository implements AuthRepository {
  ApiAuthRepository(this._api, this._tokens);

  final ApiClient _api;
  final TokenStorage _tokens;

  static const _deviceName = 'Application mobile Mayela';

  @override
  Future<AppUser?> restoreSession() async {
    if (await _tokens.read() == null) return null;
    return me();
  }

  @override
  Future<AppUser> me() async {
    final json = await _api.get('/me');
    return AppUser.fromJson(json['data'] as Map<String, dynamic>);
  }

  @override
  Future<AppUser> loginWithPassword({required String identifier, required String password}) =>
      _authenticate('/auth/login', {'identifier': identifier.trim(), 'password': password});

  @override
  Future<AppUser> loginWithStudentCode({required String code, required String pin}) =>
      _authenticate('/auth/student-login', {'login_code': code.trim(), 'pin': pin});

  @override
  Future<AppUser> registerParent({
    required String name,
    String? email,
    String? phone,
    required String password,
    required bool acceptTerms,
  }) => _authenticate('/auth/register-parent', {
    'name': name.trim(),
    if (email != null && email.isNotEmpty) 'email': email.trim(),
    if (phone != null && phone.isNotEmpty) 'phone': phone.trim(),
    'password': password,
    'password_confirmation': password,
    'accept_terms': acceptTerms,
  });

  @override
  Future<StudentProfile> addChild({
    required String firstName,
    required String lastName,
    required DateTime birthDate,
    required String pin,
    required bool parentalConsent,
    String countryCode = 'CG',
    int? gradeId,
    int? trackId,
  }) async {
    final json = await _api.post('/parent/children', {
      'first_name': firstName.trim(),
      'last_name': lastName.trim(),
      'birth_date': _isoDate(birthDate),
      'country_code': countryCode,
      'pin': pin,
      'pin_confirmation': pin,
      'parental_consent': parentalConsent,
      'grade_id': ?gradeId,
      'track_id': ?trackId,
    });
    return StudentProfile.fromJson(json['data'] as Map<String, dynamic>);
  }

  @override
  Future<void> logout() async {
    try {
      await _api.post('/auth/logout');
    } finally {
      await _tokens.clear(); // Toujours effacer localement, même hors ligne.
    }
  }

  Future<AppUser> _authenticate(String path, Map<String, dynamic> body) async {
    final json = await _api.post(path, {...body, 'device_name': _deviceName});
    await _tokens.write(json['token'] as String);
    return AppUser.fromJson(json['user'] as Map<String, dynamic>);
  }

  static String _isoDate(DateTime d) =>
      '${d.year.toString().padLeft(4, '0')}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';
}
