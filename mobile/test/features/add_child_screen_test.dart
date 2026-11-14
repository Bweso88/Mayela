import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:material_ui/material_ui.dart';
import 'package:mayela/features/auth/data/auth_repository.dart';
import 'package:mayela/features/catalog/data/catalog_repository.dart';
import 'package:mayela/features/catalog/presentation/grade_picker.dart';

import '../fakes.dart';

/// Hôte minimal du sélecteur, avec l'état géré comme dans les écrans.
class _Host extends StatefulWidget {
  const _Host();

  @override
  State<_Host> createState() => _HostState();
}

class _HostState extends State<_Host> {
  final formKey = GlobalKey<FormState>();
  dynamic grade;
  dynamic track;

  @override
  Widget build(BuildContext context) => Scaffold(
    body: Form(
      key: formKey,
      child: Column(
        children: [
          GradePicker(
            required: true,
            grade: grade,
            track: track,
            onChanged: (g, t) => setState(() {
              grade = g;
              track = t;
            }),
          ),
          TextButton(onPressed: () => formKey.currentState!.validate(), child: const Text('Valider')),
        ],
      ),
    ),
  );
}

void main() {
  Future<void> pump(WidgetTester tester) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          catalogRepositoryProvider.overrideWithValue(FakeCatalogRepository()),
          authRepositoryProvider.overrideWithValue(FakeAuthRepository()),
        ],
        child: const MaterialApp(home: _Host()),
      ),
    );
    await tester.pumpAndSettle();
  }

  testWidgets('n\'affiche le choix de série que pour une classe qui en propose', (tester) async {
    await pump(tester);
    expect(find.byKey(const Key('track-picker')), findsNothing);

    await tester.tap(find.byKey(const Key('grade-picker')));
    await tester.pumpAndSettle();
    await tester.tap(find.text('CM2 · Primaire').last);
    await tester.pumpAndSettle();
    expect(find.byKey(const Key('track-picker')), findsNothing);

    await tester.tap(find.byKey(const Key('grade-picker')));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Première · Lycée').last);
    await tester.pumpAndSettle();
    expect(find.byKey(const Key('track-picker')), findsOneWidget);
  });

  testWidgets('exige classe puis série', (tester) async {
    await pump(tester);

    await tester.tap(find.text('Valider'));
    await tester.pump();
    expect(find.text('Choisis une classe.'), findsOneWidget);

    await tester.tap(find.byKey(const Key('grade-picker')));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Première · Lycée').last);
    await tester.pumpAndSettle();
    await tester.tap(find.text('Valider'));
    await tester.pump();
    expect(find.text('Choisis une série.'), findsOneWidget);
  });
}
