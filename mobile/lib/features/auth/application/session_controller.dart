import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_exception.dart';
import '../data/auth_repository.dart';
import '../data/models.dart';

/// Session courante : `null` = non connecté.
final sessionProvider = AsyncNotifierProvider<SessionController, AppUser?>(SessionController.new);

class SessionController extends AsyncNotifier<AppUser?> {
  AuthRepository get _repo => ref.read(authRepositoryProvider);

  @override
  Future<AppUser?> build() async {
    try {
      return await _repo.restoreSession();
    } on ApiException catch (e) {
      // Jeton expiré ou révoqué : on repart d'une session vide.
      if (e.isUnauthenticated) {
        await _repo.logout().catchError((_) {});
        return null;
      }
      rethrow;
    }
  }

  Future<void> loginWithPassword(String identifier, String password) =>
      _run(() => _repo.loginWithPassword(identifier: identifier, password: password));

  Future<void> loginWithStudentCode(String code, String pin) =>
      _run(() => _repo.loginWithStudentCode(code: code, pin: pin));

  Future<void> registerParent({
    required String name,
    String? email,
    String? phone,
    required String password,
    required bool acceptTerms,
  }) => _run(
    () => _repo.registerParent(name: name, email: email, phone: phone, password: password, acceptTerms: acceptTerms),
  );

  Future<void> refresh() => _run(_repo.me);

  Future<void> logout() async {
    await _repo.logout();
    state = const AsyncData(null);
  }

  /// Les erreurs sont relancées pour être affichées par le formulaire appelant ;
  /// l'état précédent est conservé en cas d'échec.
  Future<void> _run(Future<AppUser> Function() action) async {
    final user = await action();
    state = AsyncData(user);
  }
}
