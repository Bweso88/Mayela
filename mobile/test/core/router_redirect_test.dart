import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mayela/core/router/router.dart';
import 'package:mayela/features/auth/data/models.dart';

import '../fakes.dart';

void main() {
  const loading = AsyncLoading<AppUser?>();
  const loggedOut = AsyncData<AppUser?>(null);

  test('affiche l\'écran de chargement pendant la restauration de session', () {
    expect(redirectFor(loading, '/parent'), '/splash');
    expect(redirectFor(loading, '/splash'), isNull);
  });

  test('renvoie un visiteur vers l\'accueil public', () {
    expect(redirectFor(loggedOut, '/parent'), '/');
    expect(redirectFor(loggedOut, '/splash'), '/');
    expect(redirectFor(loggedOut, '/login/student'), isNull);
  });

  test('envoie chaque rôle vers son espace et l\'y cantonne', () {
    expect(redirectFor(AsyncData(parentUser), '/'), '/parent');
    expect(redirectFor(AsyncData(parentUser), '/parent/children/new'), isNull);
    expect(redirectFor(AsyncData(parentUser), '/student'), '/parent');
    expect(redirectFor(AsyncData(studentUser), '/parent'), '/student');
  });
}
