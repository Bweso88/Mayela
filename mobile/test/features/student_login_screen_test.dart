import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:material_ui/material_ui.dart';
import 'package:mayela/core/api/api_exception.dart';
import 'package:mayela/features/auth/data/auth_repository.dart';
import 'package:mayela/features/auth/presentation/student_login_screen.dart';

import '../fakes.dart';

void main() {
  late FakeAuthRepository repo;

  Future<void> pumpScreen(WidgetTester tester) async {
    repo = FakeAuthRepository();
    await tester.pumpWidget(
      ProviderScope(
        overrides: [authRepositoryProvider.overrideWithValue(repo)],
        child: const MaterialApp(home: StudentLoginScreen()),
      ),
    );
    await tester.pumpAndSettle();
  }

  testWidgets('valide le code et le PIN avant l\'envoi', (tester) async {
    await pumpScreen(tester);

    await tester.tap(find.text('C\'est parti !'));
    await tester.pump();

    expect(find.text('Ton code est obligatoire.'), findsOneWidget);
    expect(find.text('Le PIN contient 4 à 6 chiffres.'), findsOneWidget);
    expect(repo.calls.where((c) => c.startsWith('student-login')), isEmpty);
  });

  testWidgets('envoie le code et le PIN saisis', (tester) async {
    await pumpScreen(tester);

    await tester.enterText(find.byKey(const Key('student-code')), 'MY-7KQ4PX');
    await tester.enterText(find.byKey(const Key('student-pin')), '2468');
    await tester.tap(find.text('C\'est parti !'));
    await tester.pumpAndSettle();

    expect(repo.calls, contains('student-login:MY-7KQ4PX:2468'));
  });

  testWidgets('affiche l\'erreur renvoyée par le serveur', (tester) async {
    await pumpScreen(tester);
    repo.nextError = ApiException(
      'Code ou PIN incorrect.',
      statusCode: 422,
      fieldErrors: const {
        'login_code': ['Code ou PIN incorrect.'],
      },
    );

    await tester.enterText(find.byKey(const Key('student-code')), 'MY-AAAAAA');
    await tester.enterText(find.byKey(const Key('student-pin')), '0000');
    await tester.tap(find.text('C\'est parti !'));
    await tester.pumpAndSettle();

    expect(find.text('Code ou PIN incorrect.'), findsWidgets);
  });
}
