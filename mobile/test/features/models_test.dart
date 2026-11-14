import 'package:flutter_test/flutter_test.dart';
import 'package:mayela/core/theme/universe.dart';
import 'package:mayela/features/auth/data/models.dart';

void main() {
  final json = {
    'id': 's-1',
    'first_name': 'Sarah',
    'last_name': 'Okemba',
    'birth_date': '2010-05-04',
    'login_code': 'MY-ABCDEF',
    'level': 1,
    'xp': 0,
    'country_code': 'CG',
    'grade': {'id': 15, 'code': '1ERE', 'name': 'Première', 'universe': 'lycee'},
    'track': {'id': 3, 'code': 'C', 'name': 'Série C', 'kind': 'series'},
  };

  test('lit la classe et la série renvoyées par l\'API', () {
    final profile = StudentProfile.fromJson(json);

    expect(profile.grade?.code, '1ERE');
    expect(profile.schoolingLabel, 'Première C');
    expect(profile.universeAt(DateTime(2026, 11, 14)), Universe.lycee);
  });

  test('se replie sur l\'âge sans classe', () {
    final profile = StudentProfile.fromJson({...json, 'grade': null, 'track': null});

    expect(profile.schoolingLabel, isNull);
    expect(profile.universeAt(DateTime(2026, 11, 14)), Universe.lycee); // 16 ans
  });

  test('lit une classe du catalogue avec son cycle et ses séries', () {
    final grade = GradeRef.fromJson({
      'id': 14,
      'code': '2NDE',
      'name': 'Seconde',
      'universe': 'lycee',
      'stage': {'code': 'secondaire_general', 'name': 'Secondaire général'},
      'cycle': {'code': 'lycee', 'name': 'Lycée'},
      'tracks': [
        {'id': 1, 'code': 'A', 'name': 'Série A', 'kind': 'series'},
        {'id': 3, 'code': 'C', 'name': 'Série C', 'kind': 'series'},
      ],
    });

    expect(grade.label, 'Seconde · Lycée');
    expect(grade.requiresTrack, isTrue);
    expect(grade.tracks.map((t) => t.code), ['A', 'C']);
  });
}
