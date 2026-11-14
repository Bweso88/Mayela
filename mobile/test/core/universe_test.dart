import 'package:flutter_test/flutter_test.dart';
import 'package:mayela/core/theme/mayela_theme.dart';
import 'package:mayela/core/theme/universe.dart';

void main() {
  test('choisit l\'univers selon l\'âge en attendant la classe', () {
    expect(Universe.fromAge(7), Universe.junior);
    expect(Universe.fromAge(12), Universe.college);
    expect(Universe.fromAge(16), Universe.lycee);
  });

  test('agrandit le texte pour l\'univers primaire', () {
    final junior = MayelaTheme.of(Universe.junior).textTheme.bodyLarge!.fontSize!;
    final lycee = MayelaTheme.of(Universe.lycee).textTheme.bodyLarge!.fontSize!;
    expect(junior, greaterThan(lycee));
  });
}
