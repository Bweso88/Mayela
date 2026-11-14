import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mayela/core/api/api_exception.dart';
import 'package:mayela/features/auth/application/session_controller.dart';
import 'package:mayela/features/auth/data/auth_repository.dart';

import '../fakes.dart';

void main() {
  late FakeAuthRepository repo;
  late ProviderContainer container;

  setUp(() {
    repo = FakeAuthRepository();
    container = ProviderContainer(overrides: [authRepositoryProvider.overrideWithValue(repo)]);
    addTearDown(container.dispose);
  });

  test('démarre déconnecté sans jeton enregistré', () async {
    expect(await container.read(sessionProvider.future), isNull);
  });

  test('restaure la session existante', () async {
    repo.sessionUser = parentUser;
    expect((await container.read(sessionProvider.future))?.name, 'Grâce Nkounkou');
  });

  test('efface une session expirée au démarrage', () async {
    repo.restoreError = ApiException('expirée', statusCode: 401);
    expect(await container.read(sessionProvider.future), isNull);
    expect(repo.calls, contains('logout'));
  });

  test('connecte un enfant puis le déconnecte', () async {
    await container.read(sessionProvider.future);
    await container.read(sessionProvider.notifier).loginWithStudentCode('MY-7KQ4PX', '2468');
    expect(container.read(sessionProvider).value?.studentProfile?.firstName, 'Lucas');

    await container.read(sessionProvider.notifier).logout();
    expect(container.read(sessionProvider).value, isNull);
  });

  test('relance l\'erreur de connexion sans modifier la session', () async {
    await container.read(sessionProvider.future);
    repo.nextError = ApiException('Code ou PIN incorrect.', statusCode: 422);

    await expectLater(
      container.read(sessionProvider.notifier).loginWithStudentCode('MY-7KQ4PX', '0000'),
      throwsA(isA<ApiException>()),
    );
    expect(container.read(sessionProvider).value, isNull);
  });
}
